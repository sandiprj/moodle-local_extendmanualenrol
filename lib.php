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
 * @copyright  2025 Sandip R <sandipr@meditab.com>
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
    global $USER;
    
    // Check if user is enrolled via manual enrolment
    $instances = enrol_get_instances($course->id, true);
    $manualenrolled = false;
    foreach ($instances as $instance) {
        if ($instance->enrol === 'manual') {
            $manualenrolled = is_enrolled($context, $USER->id, '', true);
            break;
        }
    }

    // Add request extension link for students
    if (has_capability('local/extendmanualenrol:requestextension', $context) && $manualenrolled) {
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