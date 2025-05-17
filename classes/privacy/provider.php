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

class provider implements \core_privacy\local\metadata\provider {

    /**
     * Returns meta data about this plugin.
     *
     * @param   \core_privacy\local\metadata\collection $collection The initialised collection to add items to.
     * @return  \core_privacy\local\metadata\collection     A listing of user data stored through this system.
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
}

