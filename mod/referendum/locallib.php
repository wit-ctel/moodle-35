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

class referendum {
    // Moodle module variables.
    private $cm;
    private $course;
    private $context;
    private $userid;
    private $config;

    // Voting variables.
    public $finished;
    public $votingform;
    public $confirmform;
    public $referendum;
    public $displayvotingform;
    public $displayconfirmform;
    public $confirminfo;
    private $referendumactive;

    private $totalvotes;
    protected $precision = 0;
    protected $nontransferablevote = 0;

    public $messages;

    /**
     * @param int|string $cmid optional
     * @param object $course optional
     */
    public function __construct($cm, $course, $group) {
        global $COURSE, $DB, $CFG, $USER;

        $this->userid = $USER->id;

        $this->cm = $cm;

        if ($CFG->version < 2011120100) {
            $this->context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        } else {
            $this->context = context_module::instance($this->cm->id);
        }

        if ($course) {
            $this->course = $course;
        } else if ($this->cm->course == $COURSE->id) {
            $this->course = $COURSE;
        } else if (! $this->course = $DB->get_record('course', array('id' => $this->cm->course) )) {
            print_error('Course is misconfigured');
        }

        if (! $this->referendum = $DB->get_record('referendum', array('id' => $this->cm->instance) )) {
            print_error('referendum ID was incorrect');
        }

        $usergroups = groups_get_user_groups($course->id);

        $this->multigroup = false;
        if (count($usergroups) > 1) {
            $this->multigroup = true;
        }

        if (!empty($group)) {
            if (groups_is_member($group, $USER->id)) {
                $this->groupid = $group;
            }

            if (has_capability('moodle/site:accessallgroups', $this->context)) {
                $this->groupid = $group;
            }
        } else {
            if (groups_get_activity_groupmode($this->cm) > 0) {
                $this->groupid = groups_get_activity_group($this->cm);
            } else {
                $this->groupid = 0;
            }
        }

        // Redirects users to the thankyou page if the vote has been submitted.
        $this->referendum_submitted();

        $this->messages = array();

        // Sets the active stages referendumactive, finished
        $this->active_stage();

        $this->displayform = false;
        $this->displaytable = false;
    }

    public function referendum_dates() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_referendum');
        return $renderer->referendum_dates($this->dates);
    }

    /**
     * Sets the active stages based on the configured settings.
     * @return void
     */ 
    private function active_stage() {
        // Activate the referendum when the time is right.
        $this->referendumactive = false;
        $this->finished = false;
        
        $dates = new Object();
        $dates->opendate = $this->referendum->opendate;
        $dates->closedate = $this->referendum->closedate;
        
        $dates->now = usertime(time(), usertimezone());
        $this->dates =  $dates;

        foreach ($dates as $key => $value) {
            $dates->$key = usertime($value, usertimezone());
        }
        
        if ( ($dates->opendate < $dates->now) && ($dates->closedate > $dates->now) ) {
            $this->referendumactive = true;
        }

        if ($dates->now > $dates->closedate) {
            $this->finished = true;
        }
        
    }


    /**
     * This initiates the form class and displays, verifies the voting form.
     * If everything is okay the vote is passed on to the store_vote method.
     * @return string || void.
     */
    public function voting_form() {
        require_once("voting_form.php");

        $formdata = array();
        $formdata['cmid'] = $this->cm->id;
        $formdata['type'] = 'vote';
        $formdata['group'] = $this->groupid;
        $formdata['multigroup'] = $this->multigroup;

        // Check if the user is eligible for voting.
        if (!$this->is_eligible()) {
            $formdata['type'] = 'static';
        }

        $votingform = new mod_referendum_voting_form(null, $formdata, 'post', '', array('class' => 'unresponsive'));
        $returnurl = new moodle_url('/course/view.php', array('id' => $this->course->id));
        if ($votingform->is_cancelled()) {
            redirect($returnurl);
        } else if ($data = $votingform->get_data()) {
            $data->type = 'confirm';
            $this->confirm_vote($data);
            $confirmform = new mod_referendum_voting_form(null, (array) $data);
            $this->votingform = $confirmform;
            $this->displayvotingform = true;
            if ($data = $confirmform->get_data()) {
                // This is empty on the submit form.
                if (empty($data->type)) {
                    $this->store_vote($data);
                    $returnurl = new moodle_url('/mod/referendum/thankyou.php', array('id' => $this->cm->id, 'group' => $this->groupid));
                    redirect($returnurl);
                }
            }
        } else {
            $this->displayvotingform = true;
            $this->votingform = $votingform;
        }
    }

    /**
     * Check if the user is eligible for casting a vote.
     * Return true if eligible, Return false if not. Set the warning 
     * message if the user is not eligible.
     * @return bool
     */
    private function is_eligible() {
        $warningtext = $warningtype = '';
        // The referendum must be active.
        if (!$this->referendumactive) {
            $this->set_message('referendumnotactive');
            return false;
        }

        // User must has the capability to vote.
        if (!has_capability('mod/referendum:vote', $this->context)) {
            $this->set_message('notallowedtovote', 'alert-warning');
            return false;
        }

        if (is_siteadmin()) {
            $this->set_message('notallowedtovote', 'alert-warning');
            return false;
        }
        return true;
    }

    /**
     * Can user view the referendum progress?
     * @return bool
     */
    public function view_progress() {
        if (has_capability('mod/referendum:viewprogress', $this->context)) {
            return true;
        }
        return false;
    }


    /**
     * After validation show the vote preferences to the user for confirmation
     * @param array $data - data from voting form
     * @return HTML confirmation table
     */
    public function confirm_vote($data = array()) {
        $this->displaytable = true;
        $this->confirminfo = get_string('yourvote', 'mod_referendum');
        // Yes
        if ($data->preference == 1) {
            $this->confirminfo .= html_writer::tag('span', get_string('votedyes', 'mod_referendum'), array('class' => 'strong'));
        }
        // No
        if ($data->preference == 2) {
            $this->confirminfo .= html_writer::tag('span', get_string('votedno', 'mod_referendum'), array('class' => 'strong'));
        }
        //$this->confirminfo = '<pre>' . print_r($data, true) . '</pre>';
    }

    /**
     * Store the users vote
     * Return true if everything ok. If this fails a warning text will be set.
     */
    public function store_vote($vote) {
        global $DB;
        $newvote = new stdClass();
        $newvote->referendum = $this->referendum->id;
        $newvote->preference = $vote->preference;
        $newvote->groupid = $vote->group;

        if ($DB->insert_record('referendum_votes', $newvote)) {
            $this->referendum_submitted(true);
        }
    }

    /**
     * Check or Store when a user has voted.
     * @param bool store - store a record of this users submission for this referendum
     * @return bool - true if stored, redirect user if referendum already submitted.
     */
    private function referendum_submitted($store = false) {
        global $DB, $USER;

        if ($store) {
            $submitted = new stdClass();
            $submitted->referendum = $this->referendum->id;
            $submitted->userid = $USER->id;
            $submitted->timecreated = time();
            $submitted->groupid = $this->groupid;
            $DB->insert_record('referendum_submitted', $submitted);
            return true;
        } else {
            if ($submitted = $DB->get_record('referendum_submitted', array('referendum' => $this->referendum->id,
                'userid' => $USER->id, 'groupid' => $this->groupid))) {

                $redirecturl = new moodle_url('/mod/referendum/thankyou.php', array('id' => $this->cm->id, 'group' => $this->groupid));
                redirect($redirecturl);
            }
        }
    }

    /**
     * Set a message for the user.
     * @param string $text - The message text
     * @param string $type - The alert type, works nicely with bootstrap based themes.
     */
    private function set_message($text, $type = 'alert-info') {
        if (!empty($text)) {
            $this->messages[] = array('text' => $text, 'type' => $type);
        }
    }

    /**
     * Get a table of referendum results
     * @return html - A formatted table of the referendum winners
     */
    public function get_results() {
        global $DB, $PAGE;

        $results = new Object();

        $novotes = $DB->get_records('referendum_votes', array('referendum' => $this->referendum->id, 'groupid' => $this->groupid, 'preference' => 2));

        $results->nocount = count($novotes);

        $yesvotes = $DB->get_records('referendum_votes', array('referendum' => $this->referendum->id, 'groupid' => $this->groupid, 'preference' => 1));

        $results->yescount = count($yesvotes);

        $renderer = $PAGE->get_renderer('mod_referendum');
        return $renderer->referendum_results($results);
    }


    /**
     * Get messages for the user.
     * @return HTML - Bootstrap formatted HTML with each message in a div.
     */
    public function get_messages() {
        $content = '';
        foreach ($this->messages as $message) {
            $messagetext = get_string($message['text'], 'mod_referendum');
            $content .= html_writer::tag('div', $messagetext, array('class' => 'alert ' . $message['type']));
        }
        return $content;
    }
}