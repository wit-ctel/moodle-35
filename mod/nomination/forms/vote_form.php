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

global $CFG;
require_once("$CFG->libdir/formslib.php");

class mod_nomination_vote_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata['cmid'];

        $nomid = $this->_customdata['nomid'];

        $groupid = $this->_customdata['groupid'];

        $posid = $this->_customdata['posid'];

        $mform->addElement('hidden', 'nomid', $nomid);
        $mform->setType('nomid', PARAM_INT);

        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'posid', $posid);
        $mform->setType('posid', PARAM_INT);

        $mform->addElement('hidden', 'vote', 1);
        $mform->setType('vote', PARAM_INT);

        $type = $this->_customdata['type'];
        // We are setting the initial form data.
        if ($type == 'active' || $type == 'static') {
            $mform->addElement('static', 'header', '', html_writer::tag('h4', get_string('nominate', 'mod_nomination')));

            $runners = (empty($this->_customdata['runners'])) ? array() : $this->_customdata['runners'];

            if (count($runners) == 0 ) {
                $mform->addElement('html', get_string('norunnersyet', 'mod_nomination'));
            } else {

                // Show the runners only. No further actions allowed.
                if ($type == 'static') {
                    $mform->addElement('hidden', 'static', 1);
                } else {
                    $mform->addElement('hidden', 'static', 0);
                }
                $mform->setType('static', PARAM_INT);

                $options = array(0 => '-');
                for ($i = 1; $i <= count($runners); $i++) {
                    $options[$i] = $i;
                }

                $radioarray = array();
                foreach ($runners as $runner) {
                    $radioarray[] =& $mform->createElement('radio', 'runner', '', $runner->firstname . ' ' . $runner->lastname, $runner->id);
                }
                if (!empty($radioarray)) {
                    $mform->addGroup($radioarray, 'runners', '', array(' '), false);
                }
                if ($type != 'static') {
                    $this->add_action_buttons(true, get_string('nominate', 'mod_nomination'));
                }
            }
        }

    }

    public function validation($data, $files) {
        global $DB, $USER;
        $errors = parent::validation($data, $files);

        $hasvote = false;

        foreach ($data as $field => $value) {
            if ($field == 'runner') {
                $hasvote = true;
                $runner = $DB->get_record('nomination_runner', array('id' => $value));
                if ($USER->id == $runner->userid) {
                    $errors['header'] = get_string('novoteself', 'mod_nomination');
                }
            }
        }

        // Do we allow empty votes?
        if (!$hasvote) {
            $errors['header'] = get_string('musthavevote', 'mod_nomination');
        }

        return $errors;
    }
}