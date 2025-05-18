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
 * Extension request form definition for local_extendmanualenrol.
 *
 * This form allows students to submit requests for extending their manual course
 * enrolment. It includes fields for specifying the number of days needed and
 * providing a reason for the extension request. The form includes validation
 * to ensure reasonable request lengths.
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extendmanualenrol\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Extension request form
 *
 * @package    local_extendmanualenrol
 * @copyright  2025 Sandip R <radadiyasandip89@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_form extends \moodleform {

    /**
     * Form definition
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'daysrequested', get_string('daystoextend', 'local_extendmanualenrol'));
        $mform->setType('daysrequested', PARAM_INT);
        $mform->addRule('daysrequested', null, 'required', null, 'client');
        $mform->addRule('daysrequested', null, 'numeric', null, 'client');
        $mform->addRule('daysrequested', null, 'nonzero', null, 'client');

        $mform->addElement('textarea', 'reason', get_string('extensionreason', 'local_extendmanualenrol'),
                    ['rows' => 5, 'cols' => 50]);
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('requestextension', 'local_extendmanualenrol'));
    }

    /**
     * Validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (!empty($data['daysrequested'])) {
            if ($data['daysrequested'] <= 0) {
                $errors['daysrequested'] = get_string('invalidnum', 'error');
            }
            if ($data['daysrequested'] > 365) {
                $errors['daysrequested'] = get_string('error');
            }
        }
        return $errors;
    }
}
