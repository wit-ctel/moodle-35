<?php
// This file is part of the Election plugin for Moodle
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
 * @package    mod_referendum
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");

class mod_referendum_voting_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $strftimedatetime = get_string("strftimedatetime");

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->setType('static', PARAM_INT);
        $type = $this->_customdata['type'];

        $mform->addElement('hidden', 'group', $this->_customdata['group']);
        $mform->setType('group', PARAM_INT);

        $groupid = $this->_customdata['group'];

        if ($type == 'vote' || $type == 'static') {
            if ($type == 'static' ) {
                $mform->addElement('hidden', 'static', 1);
            } else {
                $mform->addElement('hidden', 'static', 0);
            }

            $options[0] = '-';
            $options[1] = get_string('optionyes', 'mod_referendum');
            $options[2] = get_string('optionno', 'mod_referendum');

            $fieldname = 'preference';
            $mform->addElement('select', $fieldname, get_string('options', 'mod_referendum'), $options);
            $mform->setType($fieldname, PARAM_INT);
            $mform->setDefault($fieldname, 0);
            $mform->disabledIf($fieldname, 'static', 'eq', 1);

            // We are setting the initial form data.
            if ($type == 'vote') {
                $this->add_action_buttons(true, get_string('continue', 'mod_referendum'));
            }
        }

        // This will contain form data from the previous submit (see type=active above).
        // It's only purpose is to confirm the vote.
        if ($type == 'confirm') {
            $data = $this->_customdata;
            foreach ($data as $field => $value) {
                $mform->addElement('hidden', $field, $value);
                $mform->setType($field, PARAM_INT);
            }
            $this->add_action_buttons(true, get_string('vote', 'mod_referendum'));
        }

        
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        foreach ($data as $field => $value) {
            if ($field == 'preference' && $value == 0) {
                $errors[$field] = get_string('pleasselectoption', 'mod_referendum');
            }
        }
        return $errors;
    }
}