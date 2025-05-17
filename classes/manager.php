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
 * Extension request manager class for local_extendmanualenrol
 *
 * This class provides the core functionality for managing extension requests,
 * including creating new requests, approving or denying requests, and querying
 * request status. It handles all database operations and enrolment updates.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extendmanualenrol;

defined('MOODLE_INTERNAL') || die();

/**
 * Extension manager class
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Create a new extension request
     *
     * @param int $courseid The course ID
     * @param int $userid The user ID
     * @param int $daysrequested Number of days requested
     * @param string $reason The reason for the extension
     * @return bool|int The ID of the new request or false if failed
     */
    public static function create_request($courseid, $userid, $daysrequested, $reason) {
        global $DB;

        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->daysrequested = $daysrequested;
        $record->reason = $reason;
        $record->status = 'pending';
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record('local_extendmanualenrol', $record);
    }

    /**
     * Approve an extension request
     *
     * @param int $requestid The request ID
     * @param int $approverid The approver's user ID
     * @return bool True if successful, false otherwise
     */
    public static function approve_request($requestid, $approverid) {
        global $DB;

        $request = $DB->get_record('local_extendmanualenrol', ['id' => $requestid], '*', MUST_EXIST);

        // Get manual enrolment instance
        $instances = enrol_get_instances($request->courseid, true);
        $manualinstance = null;
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                $manualinstance = $instance;
                break;
            }
        }

        if (!$manualinstance) {
            return false;
        }

        // Get user enrolment
        $ue = $DB->get_record('user_enrolments', [
            'enrolid' => $manualinstance->id,
            'userid' => $request->userid
        ]);

        if (!$ue) {
            return false;
        }

        // Calculate new end time
        $newendtime = $ue->timeend + ($request->daysrequested * DAYSECS);

        // Update enrolment
        $manual = enrol_get_plugin('manual');
        $manual->update_user_enrol($manualinstance, $request->userid, ENROL_USER_ACTIVE, null, $newendtime);

        // Update request status
        $request->status = 'approved';
        $request->approverid = $approverid;
        $request->timeapproved = time();
        $request->timemodified = time();

        return $DB->update_record('local_extendmanualenrol', $request);
    }

    /**
     * Deny an extension request
     *
     * @param int $requestid The request ID
     * @param int $approverid The approver's user ID
     * @return bool True if successful, false otherwise
     */
    public static function deny_request($requestid, $approverid) {
        global $DB;

        $request = $DB->get_record('local_extendmanualenrol', ['id' => $requestid], '*', MUST_EXIST);
        $request->status = 'denied';
        $request->approverid = $approverid;
        $request->timemodified = time();

        return $DB->update_record('local_extendmanualenrol', $request);
    }

    /**
     * Get all extension requests for a course
     *
     * @param int $courseid The course ID
     * @return array Array of request records
     */
    public static function get_course_requests($courseid) {
        global $DB;

        $sql = "SELECT e.*, u.firstname, u.lastname, u.email,
                            u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                FROM {local_extendmanualenrol} e
                JOIN {user} u ON u.id = e.userid
                WHERE e.courseid = :courseid
                ORDER BY e.timecreated DESC";

        return $DB->get_records_sql($sql, ['courseid' => $courseid]);
    }

    /**
     * Get pending requests for a user in a course
     *
     * @param int $courseid The course ID
     * @param int $userid The user ID
     * @return bool True if there are pending requests, false otherwise
     */
    public static function has_pending_requests($courseid, $userid) {
        global $DB;

        return $DB->record_exists('local_extendmanualenrol', [
            'courseid' => $courseid,
            'userid' => $userid,
            'status' => 'pending'
        ]);
    }
}
