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
 * @copyright  2025 Sandip R <sandipr@meditab.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Manual Enrolment Extension';
$string['pluginname_desc'] = 'Allows students to request extensions for their manual course enrolments';
$string['requestextension'] = 'Request Extension';
$string['extendaccess'] = 'Extend Access';
$string['extensionrequested'] = 'Extension has been requested';
$string['extensionapproved'] = 'Extension has been approved';
$string['extensiondenied'] = 'Extension request has been denied';
$string['manageextensions'] = 'Manage enrolment extensions';
$string['extensionrequests'] = 'Extension Requests';
$string['requestextension_desc'] = 'Request an extension for course access';
$string['manageextensions_desc'] = 'Manage student enrolment extension requests';
$string['daystoextend'] = 'Number of days to extend';
$string['extensionreason'] = 'Reason for extension';
$string['norequests'] = 'No extension requests found';
$string['approve'] = 'Approve';
$string['deny'] = 'Deny';

// Capability strings
$string['extendmanualenrol:requestextension'] = 'Request an extension to manual course enrolment';
$string['extendmanualenrol:manageextensions'] = 'Manage manual enrolment extension requests';