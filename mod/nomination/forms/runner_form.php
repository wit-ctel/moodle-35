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

class mod_nomination_runner_form extends moodleform {

    public function definition() {
        global $DB, $USER;
        $mform = $this->_form;

        $strftimedatetime = get_string("strftimedatetime");

        $nomid = $this->_customdata['nomid'];

        $groupid = $this->_customdata['groupid'];

        $canmanage = $this->_customdata['manage'];

        $nomination = $DB->get_record('nomination', array('id' => $nomid));

        $mform->addElement('hidden', 'nomid', $nomid);
        $mform->setType('nomid', PARAM_INT);

        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        if ($groupid) {
            $group = groups_get_group($groupid);
        }

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'runnerid', $this->_customdata['runnerid']);
        $mform->setType('runnerid', PARAM_INT);

        $mform->addElement('hidden', 'posid', $this->_customdata['posid']);
        $mform->setType('posid', PARAM_INT);

        $type = $this->_customdata['type'];

        // We are setting the initial form data.
        if ($type == 'active' || $type == 'static') {
            // $mform->addElement('static', 'positions', html_writer::tag('h3', get_string('positions', 'mod_nomination')));

            // $positions = (empty($this->_customdata['positions'])) ? array() : $this->_customdata['positions'];

            // Show the positions only. No further actions allowed.
            if ($type == 'static') {
                $mform->addElement('hidden', 'static', 1);
            } else {
                $mform->addElement('hidden', 'static', 0);
            }

            // $mform->addElement('select', 'posid', get_string('position', 'mod_nomination'), $positions);
            if ($groupid) {
                $mform->addElement('text', 'groupname', get_string('group'));
                $mform->setDefault('groupname', $group->name);
                $mform->disabledIf('groupname', 'groupid', 'neq', 0);
                $mform->setType('groupname', PARAM_RAW);
            }

            $mform->addElement('text', 'firstname', get_string('firstname'));
            $mform->setDefault('firstname', $USER->firstname);
            $mform->setType('firstname', PARAM_RAW);

            $mform->addElement('text', 'lastname', get_string('lastname'));
            $mform->setDefault('lastname', $USER->lastname);
            $mform->setType('lastname', PARAM_RAW);

            $mform->addElement('static', 'policytext', '', $nomination->policy);

            $mform->addElement('selectyesno', 'policyagreed', get_string('policyagree', 'mod_nomination'));

            $mform->setType('static', PARAM_INT);

            if ($type != 'static') {
                if ($canmanage) {
                    $this->add_action_buttons(true, get_string('addnominee', 'mod_nomination'));
                } else {
                    $this->add_action_buttons(true, get_string('signup', 'mod_nomination'));
                }
            }
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        foreach ($data as $field => $value) {
            if (($field == 'policyagreed') && $value == 0) {
                $errors[$field] = get_string('mustagree', 'mod_nomination');
            }
        }
        return $errors;
    }
}