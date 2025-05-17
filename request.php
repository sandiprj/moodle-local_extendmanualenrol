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
 * Extension request page for local_extendmanualenrol
 * 
 * This page handles the student's request for an extension to their manual
 * course enrolment. It displays a form where students can specify the
 * number of days needed and provide a reason for the extension.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/extendmanualenrol/classes/form/request_form.php');
require_once($CFG->dirroot . '/local/extendmanualenrol/classes/manager.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/lib/classes/context/course.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$coursecontext = context_course::instance($course->id);
if (!$coursecontext) {
    throw new moodle_exception('nocontext', 'error');
}
require_capability('local/extendmanualenrol:requestextension', $coursecontext);
if (\local_extendmanualenrol\manager::has_pending_requests($courseid, $USER->id)) {
    redirect(
        new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('extensionrequested', 'local_extendmanualenrol'),
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

$PAGE->set_url('/local/extendmanualenrol/request.php', ['courseid' => $courseid]);
$PAGE->set_context($coursecontext);
$PAGE->set_title($course->shortname . ': ' . get_string('requestextension', 'local_extendmanualenrol'));
$PAGE->set_heading($course->fullname);

$form = new \local_extendmanualenrol\form\request_form();
$form->set_data(['courseid' => $courseid]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $form->get_data()) {
    if (\local_extendmanualenrol\manager::create_request($courseid, $USER->id, $data->daysrequested, $data->reason)) {
        redirect(
            new moodle_url('/course/view.php', ['id' => $courseid]),
            get_string('extensionrequested', 'local_extendmanualenrol'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/course/view.php', ['id' => $courseid]),
            get_string('error'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('requestextension', 'local_extendmanualenrol'));
$form->display();
echo $OUTPUT->footer();
