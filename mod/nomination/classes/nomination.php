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

require_once($CFG->dirroot . "/mod/nomination/forms/runner_form.php");
require_once($CFG->dirroot . "/mod/nomination/forms/vote_form.php");
require_once($CFG->dirroot . "/mod/nomination/classes/positions.php");

class nomination {
    // Moodle module variables.
    private $cm;
    private $course;
    private $context;
    private $userid;
    private $config;

    // Voting variables.
    public $finished;
    public $runnerform;
    public $voteform;
    public $confirmform;
    public $nomination;
    public $position;
    public $viewprogress;

    // Capabilities
    public $manage;
    public $signup;
    public $canselfnominate;
    public $isnominee;
    public $canwithdraw;
    public $nomorevotes;
    public $vote;

    private $votingactive;
    private $selfnominationactive;

    public $messages;

    /**
     * @param int|string $cmid optional
     * @param object $course optional
     */
    public function __construct($cm, $course, $group, $autoredir = true) {
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

        if (! $this->nomination = $DB->get_record('nomination', array('id' => $this->cm->instance) )) {
            print_error('nomination ID was incorrect');
        }

        if (! $this->position = $DB->get_record('nomination_position', array('nomid' => $this->nomination->id) )) {
            print_error('No position found');
        }

        // Initiate an empty messages array.
        $this->messages = array();

        // Sets the active stages votingactive, selfnominationactive, finished
        $this->active_stage();

        // Set the group id for the current user.
        if ($group) {
            $this->groupid = $group;
        } else {
            if (groups_get_activity_groupmode($this->cm) > 0) {
                $this->groupid = groups_get_activity_group($this->cm);
            } else {
                $this->groupid = 0;
            }
        }

        // Gets the user capabilities for this nomination based on role and nomination stage
        $this->get_capabilities();

        // Redirects users to the thankyou page if the vote has been submitted.
        if ($autoredir) {
            $this->nomorevotes = false;
            $this->nomination_submitted();
        }

        $this->runner_form();

        $this->displayrunnerform = false;
        $this->displayvoteform = false;
    }

    /**
     * Sets the active stages based on the configured settings.
     * @return void
     */ 
    private function active_stage() {
        // Activate the nomination when the time is right.
        $this->selfnominationactive = false;
        $this->votingactive = false;
        $this->finished = false;
        $this->canwithdraw = false;
        
        $dates = new Object();
        $dates->selfstart = $this->nomination->selfstart;
        $dates->selfstop = $this->nomination->selfstop;
        $dates->runstart = $this->nomination->runstart;
        $dates->runstop = $this->nomination->runstop;
        $dates->withdrawstop = $this->nomination->withdrawstop;

        $dates->now = usertime(time(), usertimezone());
        $this->dates =  $dates;

        foreach ($dates as $key => $value) {
            $dates->$key = usertime($value, usertimezone());
        }
        
        if (($dates->selfstart < $dates->now) && ($dates->selfstop > $dates->now) ) {
            $this->selfnominationactive = true;

        }

        if (($dates->runstart < $dates->now) && ($dates->runstop > $dates->now) ) {
            $this->votingactive = true;
        }
        if ($dates->withdrawstop > $dates->now) {
            $this->canwithdraw = true;
        }
        if ($dates->now > $dates->runstop) {
            $this->finished = true;
        }
        // Transform dates to human readable.
        
    }

    public function nomination_dates() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_nomination');
        return $renderer->nomination_dates($this->dates);
    }

    /**
     * Get the user capabilities for this nomination based on role and nomination stage
     * @return void
     */
    public function get_capabilities() {
        global $USER;
        $this->signup = $this->vote = $this->manage = $this->canselfnominate = false;
        // Check self declaration capabilities.
        if (has_capability('mod/nomination:selfdeclare', $this->context)) {
            if ($this->selfnominationactive) {
                $this->signup = true;
            }
        }

        // Check voting capabilities.
        if (has_capability('mod/nomination:vote', $this->context)) {
            if ($this->votingactive) {
                $this->vote = true;
            }
        }

        // Check management capabilities.
        if (has_capability('mod/nomination:manage', $this->context)) {
            $this->signup = false;
            $this->manage = true;
            $this->viewprogress = true;
        }

        $this->isrunner = false;
        
        if ($this->manage) {
            $this->canselfnominate = true;
        } else {
            $nompos = new nomination_positions($this->nomination->id, $this->groupid);
            $positions = $nompos->get_positions();

            foreach($positions as &$position) {
                if ($runner = $nompos->get_runner($position)) {
                    $this->canselfnominate = false;
                    if ($runner->userid == $USER->id) {
                        $this->isrunner = true;
                    }
                } else {
                    $this->canselfnominate = true;
                }
            }
        }

        return true;
    }



    /**
     * Helper function for Management form.
     * reorders returned form data and prepare for storing / updating
     * return void
     */
    private function manage_positions($formdata) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/mod/nomination/classes/positions.php");

        $names = $formdata->position;
        $minrunners = $formdata->minrunners;
        $quotum = $formdata->quotum;
        $posid = $formdata->posid;
        $groupid = $formdata->groupid;

        $nompos = new nomination_positions($this->nomination->id, $groupid);

        foreach ($names as $key => $name) {
            if (empty($name)) {
                continue;
            }
            $q = $quotum[$key];
            $r = $minrunners[$key];
            $p = $posid[$key];
            if ($nompos->store_position($name, $r, $q, $p)) {
                $this->set_message('positionsaved', 'alert-success', $name);
            } else {
                $this->set_message('positionfailed', 'alert-warning', $name);
            }
        }
    }

    /**
     * Signup form nomination.
     */
    public function runner_form() {
        if ($this->signup || $this->manage || $this->isrunner) {

            $nompos = new nomination_positions($this->nomination->id, $this->groupid);

            $formdata = array();
            $formdata['cmid'] = $this->cm->id;
            $formdata['type'] = 'active';
            $formdata['nomid'] = $this->nomination->id;
            $formdata['posid'] = $this->position->id;
            $formdata['groupid'] = $this->groupid;
            $formdata['manage'] = $this->manage;
            $formdata['runnerid'] = 0;

            $runnerform = new mod_nomination_runner_form(null, $formdata, 'post', '', array('class' => 'unresponsive'));
            $returnurl = new moodle_url('/mod/nomination/view.php', array('id' => $this->cm->id));
            $this->runnerform = $runnerform;
            if ($runnerform->is_cancelled()) {
                $returnurl = new moodle_url('/mod/nomination/view.php', array('id' => $this->cm->id, 'group' => $this->groupid));
                redirect($returnurl);
            } else if ($data = $runnerform->get_data()) {
                $this->canselfnominate = false;
                $this->set_message($nompos->registerrunner($data, $this->groupid));
            } 
        }
    }
    /**
     * Set the Runner form data when editing a runner.
     */
    public function preparerunnerform($runnerid) {
        global $DB;
        if ($runner = $DB->get_record('nomination_runner', array('id' => $runnerid))) {
            $runner->runnerid = $runner->id;
            $this->runnerform->set_data($runner);
        }   
    }

    /**
     * Delete a runner.
     */
    public function deleterunner($runnerid, $action) {
        global $DB, $USER, $OUTPUT;

        $confirmed = optional_param('confirmed', '', PARAM_INT);
        $returnurl = new moodle_url('/mod/nomination/view.php', array('id' => $this->cm->id));

        if (!$confirmed) {
            echo html_writer::tag('div', get_string('areyousure'. $action, 'mod_nomination'), array('class' => 'alert alert-warning'));
            $url = new moodle_url('/mod/nomination/view.php', array('id' => $this->cm->id,
                'action' => $action, 'confirmed' => 1, 'runnerid' => $runnerid));
            echo $OUTPUT->single_button($url, get_string('confirm' . $action, 'mod_nomination'));
            echo $OUTPUT->single_button($returnurl, get_string('cancel'));
        } else {
            if ($runner = $DB->get_record('nomination_runner', array('id' => $runnerid))) {
                if ($this->manage) {
                    $DB->delete_records('nomination_runner', array('id' => $runnerid));
                    $this->set_message('nominationremoved', 'alert-info', $returnurl->out());
                } else if ($runner->userid == $USER->id) {
                    $DB->delete_records('nomination_runner',  array('id' => $runnerid));
                    $this->set_message('nominationremoved', 'alert-info', $returnurl->out());
                }
            }
        }
    }

    /**
     * Print the list of runners for this nomination
     */
    public function get_runners() {
        global $PAGE;
        $nompos = new nomination_positions($this->nomination->id, $this->groupid);
        $positions = $nompos->get_positions();

        foreach($positions as &$position) {
            $position->runners = $nompos->get_runners($position);
        }

        $renderer = $PAGE->get_renderer('mod_nomination');
        return $renderer->print_runners($positions, $this->manage, $this->cm->id);
    }

    public function vote_links() {
        global $OUTPUT, $USER, $DB;
        if ($this->vote) {
            $links = array();
            $nompos = new nomination_positions($this->nomination->id, $this->groupid);
            $positions = $nompos->get_positions();
            foreach ($positions as $position) {
                $runners = $nompos->get_runners($position);

                if (count($runners) < $position->minrunners) {
                    continue;
                }

                if ($submitted = $DB->get_record('nomination_crowd',
                        array('nomid' => $this->nomination->id,
                        'userid' => $USER->id, 'posid' => $position->id, 'groupid' => $this->groupid))) {
                    $links[] = html_writer::tag('span',
                        get_string('voteposition', 'mod_nomination', $position->name),
                        array('class' => 'btn btn-default disabled nominatebtn'));

                } else {
                    $url = new moodle_url('/mod/nomination/view.php', array('cmid' => $this->cm->id, 'vote' => 1, 'posid' => $position->id));
                    $label = get_string('voteposition', 'mod_nomination', $position->name);
                    $links[] = html_writer::link($url, $label, array('class' => 'btn btn-success nominatebtn'));
                }
            }
            return implode('<br>', $links);
        }
    }

    /**
     * Vote for a candidate
     */
    public function vote_form($positionid) {
        global $DB;
        if ($this->vote) {
            $nompos = new nomination_positions($this->nomination->id, $this->groupid);
            $position = $nompos->get_position($positionid);
            $formdata = array();
            $formdata['cmid'] = $this->cm->id;
            $formdata['type'] = 'active';
            $formdata['nomid'] = $this->nomination->id;
            $formdata['posid'] = $position->id;
            $formdata['groupid'] = $this->groupid;
            $formdata['runners'] = $nompos->active_runners($position);
            $formdata['position'] = $position->name;


            $voteform = new mod_nomination_vote_form(null, $formdata, 'post', '', array('class' => 'unresponsive'));
            $returnurl = new moodle_url('/mod/nomination/view.php', array('id' => $this->cm->id));
            if ($voteform->is_cancelled()) {
            } else if ($data = $voteform->get_data()) {
                $this->set_message($this->votefor($data));
            } else {
                $this->displayvoteform = true;
                $this->voteform = $voteform;
            }
        }
    }

    public function votefor($data) {
        global $USER, $DB;

        if ($voted = $DB->get_record('nomination_crowd',
            array('userid' => $USER->id, 'nomid' => $this->nomination->id, 'posid' => $data->posid,
                'groupid' => $this->groupid))) {
            return false;
        }

        $vote = new Object();
        $vote->nomid = $this->nomination->id;
        $vote->posid = $data->posid;
        $vote->groupid = $this->groupid;
        $vote->runnerid = $data->runner;

        if ($vote->id = $DB->insert_record('nomination_vote', $vote)) {
            $crowd = new Object();
            $crowd->nomid = $this->nomination->id;
            $crowd->userid = $USER->id;
            $crowd->voteid = $vote->id;
            $crowd->posid = $data->posid;
            $crowd->groupid = $this->groupid;
            if ($this->nomination->anonymous) {
                $crowd->voteid = 0;
            } else {
                $crowd->voteid = $vote->id;
            }

            $DB->insert_record('nomination_crowd', $crowd);
            $this->set_message('thankyou');
        }
    }

    /**
     * Store the users vote
     * Return true if everything ok. If this fails a warning text will be set.
     */
    public function nomination_submitted() {
        global $DB, $USER;

        $nompos = new nomination_positions($this->nomination->id, $this->groupid);
        $positions = $nompos->get_positions();
        $nomorevotes = true;
        foreach ($positions as $position) {
            if ($submitted = $DB->get_record('nomination_crowd',
                    array('nomid' => $this->nomination->id,
                    'userid' => $USER->id, 'posid' => $position->id, 'groupid' => $this->groupid))) {
            } else {
                $nomorevotes = false;
            }
        }

        if ($nomorevotes) {
            $this->set_message('thankyou');
            $this->nomorevotes = true;
            // $redirecturl = new moodle_url('/mod/nomination/thankyou.php', array('id' => $this->cm->id));
            // redirect($redirecturl);
        }
    }


    /**
     * Helper function - Set a message for the user.
     * @param string $text - The message text
     * @param string $type - The alert type, works nicely with bootstrap based themes.
     */
    private function set_message($text, $type = 'alert-info', $custom = '') {
        if (!empty($text)) {
            $this->messages[] = array('text' => $text, 'type' => $type, 'custom' => $custom);
        }
    }

    /**
     * Helper function - Get messages for the user.
     * @return HTML - Bootstrap formatted HTML with each message in a div.
     */
    public function get_messages() {
        $content = '';
        foreach ($this->messages as $message) {
            $messagetext = get_string($message['text'], 'mod_nomination', $message['custom']);
            if ($message['type']) {
                $classes = 'alert ' . $message['type'];
            } else {
                $classes = '';
            }
            $content .= html_writer::tag('div', $messagetext, array('class' => $classes));
        }
        return $content;
    }

    private function allposition_results() {
        $nompos = new nomination_positions($this->nomination->id, $this->groupid);
        $positions = $nompos->get_positions();

        foreach($positions as &$position) {
            $position->runners = $nompos->get_runners($position);
            $position->groupsize = $this->groupsize();
            foreach ($position->runners as &$runner) {
                $runner->votes = $nompos->get_votes($runner);
                if (count($runner->votes) >= $position->quotum) {
                    $runner->nominated = 1;
                } else {
                    $runner->nominated = 0;
                }
            }
        }
        return $positions;
    }

    public function export_winners() {
        if ($this->finished) {
            $nominees = array();
            $positions = $this->allposition_results();
            foreach ($positions as $position) {
                foreach ($position->runners as $runner) {
                    if ($runner->nominated) {
                        $nominees[] = $runner;
                    }
                }
            }
            return $nominees;
        } else {
            return false;
        }
    }

    public function get_results() {
        global $PAGE;
        $positions = $this->allposition_results();
        $renderer = $PAGE->get_renderer('mod_nomination');
        return $renderer->print_results($positions);
    }

    public function groupsize() {
        $membercount = 0;
        if ($this->groupid != 0) {
            $mygroup = groups_get_activity_group($this->cm, true);
            $members = groups_get_members($mygroup);
            $membercount = count($members);
            return $membercount;
        }
    }
}