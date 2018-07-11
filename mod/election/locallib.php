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

class election {
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
    public $election;
    public $displayvotingform;
    public $displayconfirmform;
    public $confirmtable;
    private $electionactive;

    // Elecation Calculation.
    private $stages;
    private $candidates;
    private $ballots;
    private $totalvotes;
    private $hopefuls;
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

        if (! $this->election = $DB->get_record('election', array('id' => $this->cm->instance) )) {
            print_error('election ID was incorrect');
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
        $this->election_submitted();

        $this->messages = array();

        // Activate the election when the time is right.
        $this->electionactive = false;
        $this->finished = false;
        $opendate = $this->election->opendate;
        $closedate = $this->election->closedate;
        $now = time();
        if ( ($opendate < $now) && ($closedate > $now) ) {
            $this->electionactive = true;
        }
        if ($now > $closedate) {
            $this->finished = true;
        }

        $this->num_valid_ballots = 0;
        if (!empty($this->election->linkednomination)) {
            $this->importnominationdata();
        }
        $this->candidates = $this->get_candidates();
        $this->displayform = false;
        $this->displaytable = false;
        $this->get_ballots();
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
        $formdata['candidates'] = $this->candidates;
        $formdata['type'] = 'vote';

        // Check if the user is eligible for voting.
        if (!$this->is_eligible()) {
            $formdata['type'] = 'static';
        }

        $votingform = new mod_election_voting_form(null, $formdata, 'post', '', array('class' => 'unresponsive'));
        $returnurl = new moodle_url('/course/view.php', array('id' => $this->course->id));
        if ($votingform->is_cancelled()) {
            redirect($returnurl);
        } else if ($data = $votingform->get_data()) {
            $data->type = 'confirm';
            $this->confirm_vote($data);
            $confirmform = new mod_election_voting_form(null, (array) $data);
            $this->votingform = $confirmform;
            $this->displayvotingform = true;
            if ($data = $confirmform->get_data()) {
                // This is empty on the submit form.
                if (empty($data->type)) {
                    $this->store_vote($data);
                    $returnurl = new moodle_url('/mod/election/thankyou.php', array('id' => $this->cm->id, 'group' => $this->groupid));
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
        // The election must be active.
        if (!$this->electionactive) {
            $this->set_message('electionnotactive');
            return false;
        }

        // User must has the capability to vote.
        if (!has_capability('mod/election:vote', $this->context)) {
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
     * Can user view the election progress?
     * @return bool
     */
    public function view_progress() {
        if (has_capability('mod/election:viewprogress', $this->context)) {
            return true;
        }
        return false;
    }

    /**
     * Get a list of candidates that can be elected
     * @return array - List of candidates
     */
    private function get_candidates($state = null) {
        require_once('candidate.php');
        global $DB;
        if (empty($this->candidates)) {
            if (empty($this->election->linkednomination)) {
                $group = 0;
            } else {
                $group = $this->groupid;
            }
            $electioncandidates = $DB->get_records('election_candidates', array('election' => $this->election->id, 'groupid' => $group));
            foreach ($electioncandidates as $ecandidate) {
                $this->candidates[$ecandidate->id] = new candidate($ecandidate->candidate, $ecandidate->id);
            }
            return $this->candidates;
        } else {
            if ($state === null) {
                return $this->candidates;
            }
            $candidates = array();
            foreach ($this->candidates as $cid => $candidate) {
                if ($candidate->get_state() === $state) {
                    $candidates[$cid] = $candidate;
                }
            }
            return $candidates;
        }
    }

    /**
     * Get a single candidate based on the candidate id
     * @return object - Candidate.
     */

    public function get_candidate($id) {
        if (!isset($this->candidates[$id])) {
            return false;
        }
        return $this->candidates[$id];
    }

    /**
     * After validation show the vote preferences to the user for confirmation
     * @param array $data - data from voting form
     * @return HTML confirmation table
     */
    public function confirm_vote($data = array()) {
        $table = new html_table();
        $formdata = array();
        $formdata['cmid'] = $this->cm->id;
        $table->attributes['class'] = 'table table-striped';

        $table->head = array(get_string('candidate', 'mod_election'), get_string('preference', 'mod_election'));

        $table->colclasses = array();
        $table->data = array();
        $votedata = array();
        foreach ($data as $field => $value) {
            if ((substr($field, 0, 9) == 'candidate') && $value >= 1) {
                $candidateid = str_replace('candidate_', '', $field);
                if ($candidate = $this->candidates[$candidateid]) {
                    $votedata[$value] = $candidate->get_name();
                }                
                $formdata[$field] = $value;
            }
        }
        ksort($votedata);
        foreach ($votedata as $preference => $candidatename) {
            $row = new html_table_row();

            $cell = new html_table_cell();
            $cell->text = $candidatename;
            $row->cells[] = $cell;

            $cell = new html_table_cell();
            $cell->text = $value;
            $row->cells[] = $preference;
            $table->data[] = $row;
        }
        $this->displaytable = true;
        $this->confirmtable = html_writer::table($table);
    }

    /**
     * Store the users vote
     * Return true if everything ok. If this fails a warning text will be set.
     */
    public function store_vote($vote) {
        global $DB;
        $parentid = 0;
        foreach ($vote as $field => $value) {
            if ((substr($field, 0, 9) == 'candidate') && $value >= 1) {
                $candidateid = str_replace('candidate_', '', $field);
                $vote = new stdClass();
                $vote->election = $this->election->id;
                $vote->candidateid = $candidateid;
                $vote->preference = $value;
                $vote->parentid = $parentid;
                $vote->groupid = $this->groupid;
                $vote->id = $DB->insert_record('election_votes', $vote);
                if ($parentid == 0) {
                    $vote->parentid = $vote->id;
                    $DB->update_record('election_votes', $vote);
                    $parentid = $vote->id;
                }
            }
        }
        $this->election_submitted(true);
    }

    /**
     * Check or Store when a user has voted.
     * @param bool store - store a record of this users submission for this election
     * @return bool - true if stored, redirect user if election already submitted.
     */
    private function election_submitted($store = false) {
        global $DB, $USER;
        if ($store) {
            $submitted = new stdClass();
            $submitted->election = $this->election->id;
            $submitted->userid = $USER->id;
            $submitted->timecreated = time();
            $submitted->groupid = $this->groupid;
            $DB->insert_record('election_submitted', $submitted);
            return true;
        } else {
            if ($submitted = $DB->get_record('election_submitted', array('election' => $this->election->id,
                'userid' => $USER->id, 'groupid' => $this->groupid))) {

                $redirecturl = new moodle_url('/mod/election/thankyou.php', array('id' => $this->cm->id, 'group' => $this->groupid));
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
     * Get a table of election results
     * @return html - A formatted table of the election winners
     */
    public function get_results() {
        global $DB;
        $this->find_winner();
        return $this->get_output();
    }

    /**
     * Get the Ballots form the database, count votes and pass them on
     * to be sorted and grouped.
     */
    private function get_ballots() {
        require_once('ballot.php');
        global $DB;

        $electionvotes = $DB->get_records('election_votes', array('election' => $this->election->id, 'groupid' => $this->groupid));
        $ballots = array();
        $this->ballots = array();
        // Group votes by parentid (from 1 user), then by preference.
        foreach ($electionvotes as $electionvote) {
            $ballots[$electionvote->parentid][$electionvote->preference] = $electionvote->candidateid;
        }
        $this->totalvotes = count($ballots);
        foreach ($ballots as $ballot) {
            ksort($ballot);
            $tempballot = new ballot($ballot, 1);
            $this->add_ballot($tempballot);
        }
    }

    /**
     * Add the Ballots to $this->ballots
     * The class Ballot takes care of grouping.
     */
    public function add_ballot(ballot $ballot, $key = null) {
        require_once('ballot.php');
        if ($key === null) {
            $key = $ballot->get_identifier();
        }
        $value = $ballot->get_value();
        if (isset($this->ballots[$key])) {
            $this->ballots[$key]->add_value($value);
        } else {
            $this->ballots[$key] = $ballot;
            // Ensure that ballots are sorted by their first preferences.
            ksort($this->ballots);
        }
        // Increase the count of valid ballots in the election.
        $this->num_valid_ballots += $value;
    }

    /**
     * Get the quota for this election
     * This is the number of votes required to be elected.
     */
    private function get_quota() {
        $quota = ($this->totalvotes / ($this->election->seats + 1) ) + 1;
        return $quota;
    }

    /**
     * Find the number of seats yet to be filled.
     * @return int
     */
    public function get_num_vacancies() {
        $filledseats = count($this->get_candidates(candidate::STATE_ELECTED));
        return $this->election->seats - $filledseats;
    }

    /**
     * Log this stage info.
     */
    public function log_stage($stage) {
        $totalvote = 0;

        foreach ($this->get_candidates() as $cid => $candidate) {
            $votes = $candidate->get_votes();
            $totalvote += $votes;
            $this->stages[$stage]['votes'][$cid] = round($votes, $this->precision);
            $this->stages[$stage]['changes'][$cid] = $candidate->get_log(true);
        }
        $this->stages[$stage]['total'] = $totalvote;
        $this->stages[$stage]['non_transferable'] = $this->nontransferablevote;
    }

    /**
     * Find the winners in a given round.
     * @param int $stage
     */
    private function find_winner($stage = 1) {
        require_once('candidate.php');
        global $DB;

        if ($stage == 1) {
            foreach ($this->ballots as $ballot) {
                $worth = $ballot->get_next_preference_worth();
                foreach ($ballot->get_next_preference() as $candidateid) {
                    if ($candidate = $this->get_candidate($candidateid)) {
                        $candidate->add_votes($worth);
                    }
                }
                $ballot->set_last_used_level(1);
            }
            $this->log_stage(0);

        }

        $hopefuls = $this->get_candidates(candidate::STATE_HOPEFUL);
        $quota = $this->get_quota();

        $anyoneelected = false;
        foreach ($hopefuls as $candidate) {
            if ($candidate->get_votes() >= $quota) {
                $candidate->set_state(candidate::STATE_ELECTED);
                $surplus = $candidate->get_votes() - $quota;
                if ($surplus) {
                    $candidate->set_surplus($surplus);
                    $candidate->log(sprintf('Elected at stage %d, with a surplus of %s votes.', $stage, number_format($surplus)), 'alert alert-info');
                    $this->transfer_votes($surplus, $candidate);
                } else {
                    $candidate->log(sprintf('Elected at stage %d.', $stage), 'alert alert-info');
                }
                $anyoneelected = true;
            }
        }

        $candidates = $this->find_defeatable_candidates();
        if (!$anyoneelected && $candidates) {
            $draw = false;
            foreach ($candidates as $candidate) {
                $candidate->set_state(candidate::STATE_DEFEATED);
            }
            foreach ($candidates as $candidate) {
                $candidatesleft = $this->get_candidates(candidate::STATE_HOPEFUL);
                $votes = $candidate->get_votes();
                if (count($candidatesleft) == 0) {
                    $candidate->log(sprintf('Draw at stage %d, with %s votes.', $stage, $votes ? number_format($votes) : 'no'));
                    $draw = true;
                } else {
                    $candidate->log(sprintf('Defeated at stage %d, with %s votes.', $stage, $votes ? number_format($votes) : 'no'));
                    if ($votes) {
                        $this->transfer_votes($votes, $candidate);
                    }
                }
            }
            if ($draw) {
                $this->log_stage($stage);
                return true;
            }
        }

        $hopefuls = $this->get_candidates(candidate::STATE_HOPEFUL);
        // If there are as many seats as remaining candidates, all the remaining candidates are elected.
        $numvacancies = $this->get_num_vacancies();
        if (count($hopefuls) == $numvacancies) {
            foreach ($hopefuls as $candidate) {
                $candidate->set_state(candidate::STATE_ELECTED);
                $candidate->log(sprintf('Elected at stage %d, by default.', $stage), 'alert alert-info');
            }
        } else {
            // If there are no remaining vacancies, all the remaining candidates are defeated.
            if ($numvacancies == 0) {
                foreach ($hopefuls as $candidate) {
                    $candidate->set_state(candidate::STATE_DEFEATED);
                    $candidate->log(sprintf('Defeated at stage %d, by default.', $stage));
                }
            }
        }

        $this->log_stage($stage);

        // Proceed to the next stage or stop if the election is complete.
        if ($this->get_num_vacancies() <= 0) {
            return true;
        } else if ($stage >= count($this->get_candidates())) {
            return false;
            throw new Exception(sprintf(
                'Maximum number of stages reached (%d) before completing the count.',
                count($this->get_candidates())
            ));
        }
        return $this->find_winner($stage + 1);
    }

    /**
     * Get the number of defeatable candidates.
     *
     * @return CandidateInterface
     */
    public function find_defeatable_candidates() {
        $hopefuls = $this->get_candidates(candidate::STATE_HOPEFUL);
        // Candidates can only be defeated if sufficient candidates remain to fill all the vacancies.
        if (count($hopefuls) <= $this->get_num_vacancies()) {
            return false;
        }
        $defeatables = false;
        $lowest = -1;
        
        foreach ($hopefuls as $candidate) {
            if ($lowest == -1) {
                $lowest = $candidate->get_votes();
            } else if ($candidate->get_votes() < $lowest) {
                $lowest = $candidate->get_votes();
            }
        }

        foreach ($hopefuls as $candidate) {
            if ($candidate->get_votes() == $lowest) {
                $defeatables[] = $candidate;
            }
        }
        return $defeatables;
    }

    /**
     * Get the hopeful candidate with the fewest votes.
     *
     * @return CandidateInterface
     */
    public function find_defeatable_candidate() {
        $hopefuls = $this->get_candidates(candidate::STATE_HOPEFUL);
        // Candidates can only be defeated if sufficient candidates remain to fill all the vacancies.
        if (count($hopefuls) <= $this->get_num_vacancies()) {
            return false;
        }
        $defeatable = false;
        $lowest = -1;
        $numlowest = 0;
        foreach ($hopefuls as $candidate) {
            if (!$defeatable instanceof candidate || $candidate->get_votes() < $defeatable->get_votes()) {
                $defeatable = $candidate;
                $lowest = $candidate->get_votes();
            }
        }

        return $defeatable;
    }

    /**
     * Get the submitted ballots grouped by number of votes.
     * @return HTML.
     */
    public function show_ballots() {
        $content = '';
        $content .= html_writer::tag('h1', get_string('ballotresults', 'mod_election'));
        $candidates = $this->get_candidates();
        $numcandidates = count($candidates);
        $table = new html_table();
        $table->attributes['class'] = 'table table-striped';

        $preference = get_string('preference', 'mod_election');
        for ($i = 1; $i <= $numcandidates; $i++) {
            $table->head[] = $preference . ' ' . $i;
        }
        $table->head[] = get_string('totalvotes', 'mod_election');

        foreach ($this->ballots as $ballot) {
            $data = $ballot->get_data();
            
            $row = new html_table_row();
            for ($i = 1; $i <= $numcandidates; $i++) {
                if (!empty($data->ranking[$i])) {
                    $candidateid = $data->ranking[$i];
                    $row->cells[] = $this->get_candidate($candidateid)->get_name();
                } else {
                    $row->cells[] = '-';
                }
            }
            $row->cells[] = $data->votecount;
            $table->data[] = $row;
            
        }
        $content .= html_writer::table($table);
        return $content;
    }

    /**
     * Transfer a candidate's votes or surplus to other hopefuls.
     *
     * @param float $num_to_transfer
     * @param candidate $from_candidate
     */
    public function transfer_votes($numtotransfer, candidate $fromcandidate) {
        require_once('candidate.php');
        $hopefuls = $this->get_candidates(candidate::STATE_HOPEFUL);
        // Go through the election's ballots. For each one, find the next preference
        // candidate(s), if $fromcandidate was the last preference candidate. Add
        // together the value (worth) of all of these ballots, and find what this is
        // as a proportion of $numtotransfer.
        $votes = [];
        foreach ($this->ballots as $ballot) {
            if (!in_array($fromcandidate->get_id(), $ballot->get_last_preference())) {
                // Not a relevant ballot.
                continue;
            }
            $worth = $ballot->get_next_preference_worth();
            foreach ($ballot->get_next_preference() as $candidateid) {
                if (!isset($hopefuls[$candidateid])) {
                    // The next preference candidate is not in the running.
                    continue;
                }
                if (!isset($votes[$candidateid])) {
                    $votes[$candidateid] = 0;
                }
                $votes[$candidateid] += $worth;
            }
            // Increment the last used preference level of the ballot.
            $ballot->set_last_used_level(1, true);
        }
        
        // To convert this into a ratio, find the total number of votes.
        $totalvotes = array_sum($votes);

        // Run the transfer.
        $transferred = 0;
        foreach ($votes as $tocid => $numvotes) {
            $amount = $numvotes;
            if (!$amount) {
                continue;
            }
            $tocandidate = $hopefuls[$tocid];
            $fromcandidate->transfer_votes($amount, $tocandidate);
            $transferred += $amount;
        }
        $this->nontransferablevote += $numtotransfer - $transferred;
    }

    private function get_output() {
        $content = '';

        $candidates = $this->get_candidates();
        $electednames = [];
        foreach ($candidates as $candidate) {
            if ($candidate->get_state() === candidate::STATE_ELECTED) {
                $electednames[] = trim($candidate->get_name());
            }
        }

        $table = new html_table();
        $table->attributes['class'] = 'table table-striped';
        $statistics = array(
            'elected' => implode(', ', $electednames),
            'numcandidates' => count($candidates),
            'vacancies' => $this->election->seats,
            'validballots' => $this->num_valid_ballots,
            'invalidballots' => 0, // The voting form does not allow empty ballots.
            'quota' => $this->get_quota(),
            'stages' => count($this->stages),
            'countmethod' => 'STV');

        // Add statistics to table.
        foreach ($statistics as $statname => $statvalue) {
            $row = new html_table_row();
            $row->cells[] = get_string($statname, 'mod_election');
            $row->cells[] = $statvalue;
            $table->data[] = $row;
        }
        $content .= html_writer::table($table);

        $table = new html_table();
        $table->head[] = get_string('candidates', 'mod_election');
        foreach (array_keys($this->stages) as $stageid) {
            if ($stageid == 0) {
                $table->head[] = get_string('initialcount', 'mod_election');
            } else {
                $table->head[] = get_string('stage', 'mod_election') . ' ' . $stageid;
            }
        }
        foreach ($candidates as $candidate) {
            $row = new html_table_row();
            $row->cells[] = $candidate->get_name();
            foreach ($this->stages as $stage) {
                $cell = number_format($stage['votes'][$candidate->get_id()], $this->precision);
                if (!empty($stage['changes'][$candidate->get_id()])) {
                    $changes = $stage['changes'][$candidate->get_id()];
                    foreach ($changes as $change) {
                        $cell .= html_writer::tag('div', $change[0], array('class' => $change[1]));
                    }
                }
                $row->cells[] = $cell;
            }
            $table->data[] = $row;
        }
        $content .= html_writer::table($table);

        $ballotlink = new moodle_url('/mod/election/report.php', array('id' => $this->cm->id, 'showballots' => 1, 'group' => $this->groupid));
        $content .= html_writer::link($ballotlink, get_string('showballots', 'mod_election'));

        return $content;
    }

    /**
     * Get messages for the user.
     * @return HTML - Bootstrap formatted HTML with each message in a div.
     */
    public function get_messages() {
        $content = '';
        foreach ($this->messages as $message) {
            $messagetext = get_string($message['text'], 'mod_election');
            $content .= html_writer::tag('div', $messagetext, array('class' => 'alert ' . $message['type']));
        }
        return $content;
    }

    /**
     * Import candidates from the nomination module if needed.
     *
     */
    private function importnominationdata() {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/nomination/classes/nomination.php");

        $candidates = $DB->get_records('election_candidates', array('election' => $this->election->id, 'groupid' => $this->groupid));

        if (count($candidates) == 0 ) {
            if ($cm = get_coursemodule_from_instance('nomination', $this->election->linkednomination)) {
                $nomination = new nomination($cm, $this->course, $this->groupid, false);
                if ($nominees = $nomination->export_winners()) {
                    foreach ($nominees as $nominee) {
                        $newcandidate = new stdClass();
                        $newcandidate->election = $this->election->id;
                        $newcandidate->candidate = $nominee->firstname . ' ' . $nominee->lastname;
                        $newcandidate->timecreated = time();
                        $newcandidate->groupid = $this->groupid;
                        $DB->insert_record('election_candidates', $newcandidate);
                    }

                }
            }
        }

    }
}