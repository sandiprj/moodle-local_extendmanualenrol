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
 * Language strings for local_extendmanualenrol
 *
 * This file contains all the language strings used by the manual enrolment
 * extension plugin, including UI elements, capability descriptions,
 * and notification messages.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extendmanualenrol\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist; 
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use context;
use context_course;

/**
 * Privacy provider for local_extendmanualenrol.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements 
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_extendmanualenrol',
            [
                'userid' => 'privacy:metadata:local_extendmanualenrol:userid',
                'courseid' => 'privacy:metadata:local_extendmanualenrol:courseid',
                'daysrequested' => 'privacy:metadata:local_extendmanualenrol:daysrequested',
                'reason' => 'privacy:metadata:local_extendmanualenrol:reason',
                'status' => 'privacy:metadata:local_extendmanualenrol:status',
                'approverid' => 'privacy:metadata:local_extendmanualenrol:approverid',
                'timecreated' => 'privacy:metadata:local_extendmanualenrol:timecreated',
                'timemodified' => 'privacy:metadata:local_extendmanualenrol:timemodified',
                'timeapproved' => 'privacy:metadata:local_extendmanualenrol:timeapproved'
            ],
            'privacy:metadata:local_extendmanualenrol'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course} co ON co.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {local_extendmanualenrol} eme ON eme.courseid = co.id
                 WHERE eme.userid = :userid OR eme.approverid = :approverid";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
            'approverid'   => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT userid
                  FROM {local_extendmanualenrol}
                 WHERE courseid = :courseid";

        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT approverid as userid
                  FROM {local_extendmanualenrol}
                 WHERE courseid = :courseid
                   AND approverid IS NOT NULL";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Export enrolment extension requests.
        $sql = "SELECT eme.*, c.id as contextid
                  FROM {local_extendmanualenrol} eme
                  JOIN {course} co ON co.id = eme.courseid
                  JOIN {context} c ON c.instanceid = co.id AND c.contextlevel = :contextlevel
                 WHERE c.id {$contextsql}
                   AND (eme.userid = :userid OR eme.approverid = :approverid)";

        $params = array_merge($contextparams, [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
            'approverid'   => $userid,
        ]);

        $requests = $DB->get_records_sql($sql, $params);

        foreach ($requests as $request) {
            $context = \context::instance_by_id($request->contextid);
            
            $data = (object) [
                'courseid' => $request->courseid,
                'daysrequested' => $request->daysrequested,
                'reason' => $request->reason,
                'status' => $request->status,
                'timecreated' => transform::datetime($request->timecreated),
                'timemodified' => transform::datetime($request->timemodified),
                'timeapproved' => transform::datetime($request->timeapproved),
            ];

            if ($request->userid === $userid) {
                writer::with_context($context)->export_data(['extendmanualenrol_requests'], $data);
            }
            if ($request->approverid === $userid) {
                writer::with_context($context)->export_data(['extendmanualenrol_approvals'], $data);
            }
        }
    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param \context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('local_extendmanualenrol', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_course) {
                continue;
            }

            $DB->delete_records('local_extendmanualenrol', [
                'courseid' => $context->instanceid,
                'userid' => $userid
            ]);

            // We only delete approver data if there is no user data associated with it.
            $DB->execute(
                "UPDATE {local_extendmanualenrol}
                    SET approverid = NULL
                  WHERE courseid = :courseid
                    AND approverid = :userid",
                [
                    'courseid' => $context->instanceid,
                    'userid' => $userid,
                ]
            );
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $params = array_merge($userinparams, ['courseid' => $context->instanceid]);

        // Delete user's extension requests.
        $DB->delete_records_select(
            'local_extendmanualenrol',
            "courseid = :courseid AND userid {$userinsql}",
            $params
        );

        // Remove approver information.
        $DB->execute(
            "UPDATE {local_extendmanualenrol}
                SET approverid = NULL
              WHERE courseid = :courseid
                AND approverid {$userinsql}",
            $params
        );
    }
}
