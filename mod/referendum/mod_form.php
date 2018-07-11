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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_referendum_mod_form extends moodleform_mod {

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
            $referendum = $DB->get_record('referendum', array('id' => $this->_instance));
            $opendate = $referendum->opendate;
            $closedate = $referendum->closedate;
            $now = time();
            if (($opendate < $now)) {
                $locknumber = 9;
            } else {
                $locknumber = 0;
            }
        }
        $mform->addElement('hidden', 'disabletrick', $locknumber);
        $mform->setType('disabletrick', PARAM_INT);

        $mform->addElement('text', 'name', get_string('referendumname', 'mod_referendum'), array('size' => '64'));
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

        $fieldname = 'opendate';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_referendum'));
        $mform->setType($fieldname, PARAM_INT);
        $this->date_default($fieldname);
        $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');

        $fieldname = 'closedate';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_referendum'));
        $mform->setType($fieldname, PARAM_INT);
        $this->date_default($fieldname);
        $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->disabledIf($fieldname, 'disabletrick', 'eq', '9');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function date_default($fieldname) {
        $mform = $this->_form;

        // The referendum starts in 5 minutes by default.
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
        $referendumlisterror = false;
        $nolinkednomination = true;
        foreach ($data as $field => $value) {

            // Check if the open date starts in the future.
            if ($field == 'opendate') {
                if ($value < time()) {
                    $errors[$field] = get_string('opendatecannotbeinpast', 'mod_referendum');
                }
            }
            // Check if the open date starts in the future.
            if ($field == 'closedate') {
                if ($value < time()) {
                    $errors[$field] = get_string('closedatecannotbeinpast', 'mod_referendum');
                }
                if ($value < $data['opendate']) {
                    $errors[$field] = get_string('closedatecannotbebeforeopendate', 'mod_referendum');
                }
            }
        }

        return $errors;
    }
}
