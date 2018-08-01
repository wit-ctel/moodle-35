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

class mod_election_voting_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $strftimedatetime = get_string("strftimedatetime");

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $type = $this->_customdata['type'];

        // We are setting the initial form data.
        if ($type == 'vote' || $type == 'static') {
            $mform->addElement('static', 'votinglist', html_writer::tag('h3', get_string('votinglist', 'mod_election')));
            $mform->addElement('static', 'header', html_writer::tag('h4', get_string('candidate', 'mod_election')),
                html_writer::tag('h4', get_string('preference', 'mod_election')));

            $candidates = (empty($this->_customdata['candidates'])) ? array() : $this->_customdata['candidates'];

            // Show the candidates only. No further actions allowed.
            if ($type == 'static') {
                $mform->addElement('hidden', 'static', 1);
            } else {
                $mform->addElement('hidden', 'static', 0);
            }
            $mform->setType('static', PARAM_INT);

            $options = array(0 => '-');
            for ($i = 1; $i <= count($candidates); $i++) {
                $options[$i] = $i;
            }

            foreach ($candidates as $candidate) {
                $fieldname = 'candidate_' . $candidate->get_id();
                $mform->addElement('select', $fieldname, $candidate->get_name(), $options);
                $mform->setType($fieldname, PARAM_INT);
                $mform->setDefault($fieldname, 0);
                $mform->disabledIf($fieldname, 'static', 'eq', 1);
            }
            if ($type != 'static') {
                $this->add_action_buttons(true, get_string('continue', 'mod_election'));
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
            $this->add_action_buttons(true, get_string('vote', 'mod_election'));
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $usedpositions = array();
        $hasnumberone = false;

        foreach ($data as $field => $value) {
            if ((substr($field, 0, 9) == 'candidate') && $value >= 1) {
                if (in_array($value, $usedpositions)) {
                    $errors[$field] = get_string('posistioncanonlybeusedonce', 'mod_election');
                }
                $usedpositions[] = $value;
                if ($value == 1) {
                    $hasnumberone = true;
                }
            }
        }

        // Do we allow empty votes?
        if (!$hasnumberone) {
            $errors['header'] = get_string('musthavenumberone', 'mod_election');
        }

        sort($usedpositions, SORT_NUMERIC);

        if (empty($errors)) {
            for ($i = 0; $i < (count($usedpositions) - 1); $i++) {
                if ($usedpositions[$i] != ($usedpositions[$i + 1] - 1)) {

                    $field = array_search($usedpositions[$i + 1], $data);
                    $errors[$field] = get_string('nosequentialnumbering', 'mod_election');
                }
            }
        }
        return $errors;
    }
}