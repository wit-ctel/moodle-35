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
 * @package    mod_nomination
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_nomination_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        if (empty($this->_instance)) {
            $locknumber = 0;
            $position = new Object();
        } else {
            $nomination = $DB->get_record('nomination', array('id' => $this->_instance));
            $position = $DB->get_record('nomination_position', array('nomid' => $nomination->id));
            $runstart = $nomination->runstart;
            $runstop = $nomination->runstop;
            $now = time();
            if (($runstart < $now)) {
                $locknumber = 9;
            } else {
                $locknumber = 0;
            }
        }

        $mform->addElement('hidden', 'disabletrick', $locknumber);
        $mform->setType('disabletrick', PARAM_INT);

        $mform->addElement('text', 'name', get_string('nominationname', 'mod_nomination'), array('size' => '64'));
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

        $mform->addElement('selectyesno', 'anonymous', get_string('anonymous', 'mod_nomination'));
        $mform->setDefault('anonymous', 1);
        $mform->disabledIf('anonymous', 'disabletrick', 'eq', '9');

        $mform->addElement('text', 'minrunners', get_string('minrunners', 'mod_nomination'));
        $mform->setType('minrunners', PARAM_INT);
        $mform->disabledIf('minrunners', 'disabletrick', 'eq', '9');

        if (isset($position->minrunners)) {
            $mform->setDefault('minrunners', $position->minrunners);
        }

        $mform->addElement('text', 'quotum', get_string('absquotum', 'mod_nomination'));
        $mform->setType('quotum', PARAM_INT);
        $mform->disabledIf('quotum', 'disabletrick', 'eq', '9');
        $mform->disabledIf('quotum', 'quotumtype', 'eq', '2');

        if (isset($position->quotum)) {
            $mform->setDefault('quotum', $position->quotum);
        }

        if ($this->current->groupmode) {

            $mform->addElement('text', 'percentage', get_string('percquotum', 'mod_nomination'));
            $mform->setType('percentage', PARAM_FLOAT);
            $mform->disabledIf('percentage', 'disabletrick', 'eq', '9');
            $mform->disabledIf('percentage', 'quotumtype', 'eq', '1');

            if (isset($position->percentage)) {
                $mform->setDefault('percentage', $position->percentage);
            }

            $radioarray[] =& $mform->createElement('radio', 'quotumtype', '', get_string('absolute', 'mod_nomination'), 1);
            $radioarray[] =& $mform->createElement('radio', 'quotumtype', '', get_string('percentage', 'mod_nomination'), 2);
            $mform->addGroup($radioarray, 'quotumtypes', get_string('selquotumtype', 'mod_nomination'), array(' '), false);
            $mform->setDefault('quotumtype', 1);


            if (isset($position->quotumtype)) {
                $mform->setDefault('quotumtype', $position->quotumtype);
            }

        }




        $dates = array('selfstart', 'selfstop', 'runstart', 'withdrawstop', 'runstop');
        foreach ($dates as $date) {
            $mform->addElement('date_time_selector', $date, get_string($date, 'mod_nomination'));
            $mform->setType($date, PARAM_INT);
            $this->date_default($date);
            $mform->addRule($date, null, 'required', null, 'client');
            $mform->disabledIf($date, 'disabletrick', 'eq', '9');
        }

        $fieldname = 'policy';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'mod_nomination'));
        $mform->setType($fieldname, PARAM_TEXT);
        $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function date_default($fieldname) {
        $mform = $this->_form;

        // The nomination starts in 5 minutes by default.
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
        foreach ($data as $field => $value) {

            // Check if the dates starts in the future.
            $dates = array('selfstart', 'selfstop', 'runstart', 'runstop');
            foreach ($dates as $date) {
                if ($field == $date) {
                    if ($value < time()) {
                        // $errors[$field] = get_string('datesinpasterror', 'mod_nomination');
                    }
                }
            }
        }

        return $errors;
    }
}
