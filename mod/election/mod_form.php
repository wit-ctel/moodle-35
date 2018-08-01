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
 * @package    mod_election
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_election_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB, $COURSE;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        if (empty($this->_instance)) {
            $locknumber = 0;
        } else {
            $election = $DB->get_record('election', array('id' => $this->_instance));
            $opendate = $election->opendate;
            $closedate = $election->closedate;
            $now = time();
            if (($opendate < $now)) {
                $locknumber = 9;
            } else {
                $locknumber = 0;
            }
        }
        $mform->addElement('hidden', 'disabletrick', $locknumber);
        $mform->setType('disabletrick', PARAM_INT);

        $mform->addElement('text', 'name', get_string('electionname', 'mod_election'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->disabledIf('name', 'disabletrick', 'eq', '9');

        $this->standard_intro_elements();

        $mform->disabledIf('introeditor', 'disabletrick', 'eq', '9');

        $mform->addElement('text', 'seats', get_string('seats', 'mod_election'), array('size' => '5'));
        $mform->setType('seats', PARAM_INT);
        $mform->setDefault('seats', 1);
        $mform->disabledIf('seats', 'disabletrick', 'eq', '9');

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->disabledIf('name', 'disabletrick', 'eq', '9');

        $fieldname = 'opendate';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_election'));
        $mform->setType($fieldname, PARAM_INT);
        $this->date_default($fieldname);
        $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');

        $fieldname = 'closedate';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_election'));
        $mform->setType($fieldname, PARAM_INT);
        $this->date_default($fieldname);
        $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');

        // Linked activities
        if ($nominations = $DB->get_records('nomination', array('course' => $COURSE->id))) {
            $options = array('0' => 'none');
            foreach ($nominations as $nomination) {
                $options[$nomination->id] = $nomination->name;
            }
            $mform->addElement('select', 'linkednomination', get_string('linkednomination', 'mod_election'),  $options);
        } else {
            $mform->addElement('hidden', 'linkednomination', 0);
        }

        $fieldname = 'electionlist';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'mod_election'));
        $mform->setType($fieldname, PARAM_TEXT);
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');
        $mform->disabledIf($fieldname, 'linkednomination', 'neq', '0');
        $mform->addHelpButton($fieldname, $fieldname, 'mod_election');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function date_default($fieldname) {
        $mform = $this->_form;

        // The election starts in 5 minutes by default.
        $now = time() + (60 * 5);
        static $count = 0;

        // Increment with 1 week.
        $phasetime = $now + (60 * 60 * 24 * 7 * $count++);

        if (isset($this->_customdata[$fieldname])) {
            $mform->setDefault($fieldname, $this->_customdata[$fieldname]);
        } else {
            $mform->setDefault($fieldname, $phasetime);
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $seats = 0;
        $numcandidates = 0;
        $electionlisterror = false;
        $nolinkednomination = true;
        foreach ($data as $field => $value) {
            // Check if we have valid records in the Election list.
            if ($field == 'electionlist') {
                $candidates = explode("\n", $value);
                if (count($candidates) <= 1) {
                    $electionlisterror = get_string('cannotstoreelectionlist', 'mod_election');
                }
                $numcandidates = count($candidates);
            }
            // Check if we have a linked nomination so we can ignore electionlist errors.
            if ($field == 'linkednomination') {
                if (!empty($value)) {
                    $nolinkednomination = false;
                }
            }
            // Check if the open date starts in the future.
            if ($field == 'opendate') {
                if ($value < time()) {
                    $errors[$field] = get_string('opendatecannotbeinpast', 'mod_election');
                }
            }
            // Check if the open date starts in the future.
            if ($field == 'closedate') {
                if ($value < time()) {
                    $errors[$field] = get_string('closedatecannotbeinpast', 'mod_election');
                }
                if ($value < $data['opendate']) {
                    $errors[$field] = get_string('closedatecannotbebeforeopendate', 'mod_election');
                }
            }
            // Store the number of seats
            if ($field == 'seats') {
                $seats = $value;
            }
        }
        if ($electionlisterror) {
            if ($nolinkednomination) {
                $errors['electionlist'] = $electionlisterror;
            }
        }
        if ($seats == 0) {
            $errors['seats'] = get_string('incorrectnumberofseats', 'mod_election');
        }

        return $errors;
    }
}
