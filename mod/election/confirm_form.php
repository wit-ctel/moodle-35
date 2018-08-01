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

global $CFG;
require_once("$CFG->libdir/formslib.php");

class mod_election_confirm_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $strftimedatetime = get_string("strftimedatetime");

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('static', 'header', ' ', ' ');
        $data = $this->_customdata;
        foreach ($data as $field => $value) {
            $mform->addElement('hidden', $field, $value);
            $mform->setType($field, PARAM_INT);
        }
        $this->add_action_buttons(true, get_string('vote', 'mod_election'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $usedpositions = array();
        $hasnumberone = false;

        foreach ($data as $field => $value) {
            if ((substr($field, 0, 9) == 'candidate') && $value >= 1) {
                if (in_array($value, $usedpositions)) {
                    $errors['header'] = get_string('posistioncanonlybeusedonce', 'mod_election');
                }
                $usedpositions[] = $value;
                if ($value == 1) {
                    $hasnumberone = true;
                }
            }
        }

        if (!$hasnumberone) {
            $errors['header'] = get_string('musthavenumberone', 'mod_election');
        }

        sort($usedpositions, SORT_NUMERIC);

        if (empty($errors)) {
            for ($i = 0; $i < (count($usedpositions) - 1); $i++) {
                if ($usedpositions[$i] != ($usedpositions[$i + 1] - 1)) {

                    $field = array_search($usedpositions[$i + 1], $data);
                    $errors['header'] = get_string('nosequentialnumbering', 'mod_election');
                }
            }
        }

        return $errors;
    }
}