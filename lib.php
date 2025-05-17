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
 * Library functions for local_extendmanualenrol
 * 
 * This file contains the main functions for the manual enrolment extension plugin.
 * It handles course navigation hooks and provides functionality for extending
 * manual enrolments.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to course navigation
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param context $context The course context
 */
function local_extendmanualenrol_extend_navigation_course($navigation, $course, $context) {
    global $USER, $DB;
    
    // Check if user is enrolled via manual enrolment and has an end date
    $instances = enrol_get_instances($course->id, true);
    $manualenrolled = false;
    $hasendate = false;
    
    foreach ($instances as $instance) {
        if ($instance->enrol === 'manual') {
            $ue = $DB->get_record('user_enrolments', [
                'enrolid' => $instance->id,
                'userid' => $USER->id,
                'status' => ENROL_USER_ACTIVE
            ]);
            
            if ($ue && is_enrolled($context, $USER->id, '', true)) {
                $manualenrolled = true;
                if (!empty($ue->timeend)) {
                    $hasendate = true;
                }
                break;
            }
        }
    }

    // Add request extension link for students who have an end date
    if (has_capability('local/extendmanualenrol:requestextension', $context) 
        && $manualenrolled 
        && $hasendate) {
        $url = new moodle_url('/local/extendmanualenrol/request.php', ['courseid' => $course->id]);
        $navigation->add(
            get_string('requestextension', 'local_extendmanualenrol'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/calendar', '')
        );
    }

    // Add manage extensions link for teachers/managers
    if (has_capability('local/extendmanualenrol:manageextensions', $context)) {
        $url = new moodle_url('/local/extendmanualenrol/manage.php', ['courseid' => $course->id]);
        $navigation->add(
            get_string('manageextensions', 'local_extendmanualenrol'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/settings', '')
        );
    }
}
