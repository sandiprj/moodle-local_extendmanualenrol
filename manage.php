<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Extension management page for local_extendmanualenrol
 * 
 * This page provides an interface for teachers and managers to review, approve,
 * or deny extension requests from students. Approved requests automatically
 * extend the student's manual course enrolment by the requested number of days.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <sandipr@meditab.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/extendmanualenrol/classes/manager.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/lib/classes/context/course.php');

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$requestid = optional_param('requestid', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);

// Get proper course context
$coursecontext = context_course::instance($course->id);
require_capability('local/extendmanualenrol:manageextensions', $coursecontext);

$PAGE->set_url('/local/extendmanualenrol/manage.php', ['courseid' => $courseid]);
$PAGE->set_context($coursecontext);
$PAGE->set_title($course->shortname . ': ' . get_string('manageextensions', 'local_extendmanualenrol'));
$PAGE->set_heading($course->fullname);

// Handle actions
if ($action && $requestid) {
    require_sesskey();
    
    if ($action === 'approve') {
        if (\local_extendmanualenrol\manager::approve_request($requestid, $USER->id)) {
            redirect(
                $PAGE->url,
                get_string('extensionapproved', 'local_extendmanualenrol'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    } else if ($action === 'deny') {
        if (\local_extendmanualenrol\manager::deny_request($requestid, $USER->id)) {
            redirect(
                $PAGE->url,
                get_string('extensiondenied', 'local_extendmanualenrol'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    }
}

// Get all requests for this course
$requests = \local_extendmanualenrol\manager::get_course_requests($courseid);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('extensionrequests', 'local_extendmanualenrol'));

if (empty($requests)) {
    echo $OUTPUT->notification(get_string('norequests', 'local_extendmanualenrol'), \core\output\notification::NOTIFY_INFO);
} else {
    $table = new html_table();
    $table->head = [
        get_string('fullname'),
        get_string('daystoextend', 'local_extendmanualenrol'),
        get_string('extensionreason', 'local_extendmanualenrol'),
        get_string('status'),
        get_string('timecreated', 'core'),
        get_string('actions')
    ];
    $table->attributes['class'] = 'generaltable';

    foreach ($requests as $request) {
        $actionbuttons = '';
        if ($request->status === 'pending') {
            $approveurl = new moodle_url($PAGE->url, [
                'action' => 'approve',
                'requestid' => $request->id,
                'sesskey' => sesskey()
            ]);
            $denyurl = new moodle_url($PAGE->url, [
                'action' => 'deny',
                'requestid' => $request->id,
                'sesskey' => sesskey()
            ]);
            $actionbuttons = html_writer::link($approveurl, get_string('approve', 'local_extendmanualenrol'), 
                                             ['class' => 'btn btn-success mr-2']) .
                           html_writer::link($denyurl, get_string('deny', 'local_extendmanualenrol'), 
                                             ['class' => 'btn btn-danger']);
        }

        $row = [
            fullname($request),
            $request->daysrequested,
            format_text($request->reason, FORMAT_MOODLE),
            $request->status,
            userdate($request->timecreated),
            $actionbuttons
        ];
        $table->data[] = $row;
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();