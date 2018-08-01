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


function election_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the election into the database
 *
 * @param object $election An object from the form in mod_form.php
 * @param mod_election_mod_form $mform
 * @return int The id of the newly inserted election record
 */
function election_add_instance(stdClass $election, mod_election_mod_form $mform = null) {
    global $DB;

    $election->timecreated = time();
    $electionid = $DB->insert_record('election', $election);
    if (isset($election->electionlist) && !empty($election->electionlist)) {
        election_save_candidates($election->electionlist, $electionid);
    }
    return $electionid;
}

/**
 * Updates an instance of the election in the database
 *
 *
 * @param object $election An object from the form in mod_form.php
 * @param mod_election_mod_form $mform
 * @return boolean Success/Fail
 */
function election_update_instance(stdClass $election, mod_election_mod_form $mform = null) {
    global $DB;

    $election->timemodified = time();
    $election->id = $election->instance;

    if (isset($election->electionlist) && !empty($election->electionlist)) {
        $DB->delete_records('election_candidates', array('election' => $election->id));
        election_save_candidates($election->electionlist, $election->id);
    };

    return $DB->update_record('election', $election);
}

/**
 * Inserts a list of candidates in the database
 *
 * @param string $candidatelist
 * @param int $electionid
 * @return boolean Success/Fail
 */
function election_save_candidates($candidatelist, $electionid) {
    global $DB;

    $candidates = explode("\n", $candidatelist);

    if (count($candidates) <= 1) {
        print_error(get_string('cannotstoreelectionlist', 'mod_election'));
        return false;
    }

    foreach ($candidates as $candidate) {
        $newcandidate = new stdClass();
        $newcandidate->election = $electionid;
        $newcandidate->candidate = $candidate;
        $newcandidate->timecreated = time();
        $newcandidate->groupid = 0;

        $DB->insert_record('election_candidates', $newcandidate);
    }
    return true;
}

/**
 * Removes an instance of the election from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function election_delete_instance($id) {
    global $DB;
    $DB->delete_records('election_candidates', array('election' => $id));
    $DB->delete_records('election_votes', array('election' => $id));
    $DB->delete_records('election_submitted', array('election' => $id));
    $DB->delete_records('election', array('id' => $id));
    return true;
}


/**
 * Election Cron.
 *
 * @return boolean
 **/
function election_cron () {
}

function election_extend_settings_navigation(settings_navigation $settings,
    navigation_node $electionnode) {
    global $PAGE;

    $context = $PAGE->cm->context;
    if (has_capability('mod/election:viewresults', $context)) {
        $url = '/mod/election/report.php';
        $node = navigation_node::create(get_string('results', 'mod_election'),
                new moodle_url($url, array('id' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'report');
        $electionnode->add_node($node, null);
    }
}