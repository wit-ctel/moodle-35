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


function nomination_supports($feature) {
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
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the nomination into the database
 *
 * @param object $nomination An object from the form in mod_form.php
 * @param mod_nomination_mod_form $mform
 * @return int The id of the newly inserted nomination record
 */
function nomination_add_instance(stdClass $nomination, mod_nomination_mod_form $mform = null) {
    global $DB;

    $nomination->timecreated = time();
    $nomination->id = $DB->insert_record('nomination', $nomination);

    if (!isset($nomination->quotum)) {
        $nomination->quotum = 0;
    }

    if (!isset($nomination->percentage)) {
        $nomination->percentage = 0;
    }

    if (!isset($nomination->quotumtype)) {
        $nomination->quotumtype = 1;
    }

    // The position is store in a separate table. This allows for multiple positions in future
    // releases.
    $position = new Object();
    $position->name = $nomination->name;
    $position->nomid = $nomination->id;
    $position->groupid = 0;
    $position->quotum = $nomination->quotum;
    $position->percentage = $nomination->percentage;
    $position->quotumtype = $nomination->quotumtype;
    $position->minrunners = $nomination->minrunners;

    $positionid = $DB->insert_record('nomination_position', $position);

    return $nomination->id;
}

/**
 * Updates an instance of the nomination in the database
 *
 *
 * @param object $nomination An object from the form in mod_form.php
 * @param mod_nomination_mod_form $mform
 * @return boolean Success/Fail
 */
function nomination_update_instance(stdClass $nomination, mod_nomination_mod_form $mform = null) {
    global $DB;

    $nomination->timemodified = time();
    $nomination->id = $nomination->instance;
    $DB->update_record('nomination', $nomination);

    if (!isset($nomination->quotum)) {
        $nomination->quotum = 0;
    }

    if (!isset($nomination->percentage)) {
        $nomination->percentage = 0;
    }


    $position = $DB->get_record('nomination_position', array('nomid' => $nomination->id));
    $position->name = $nomination->name;
    $position->quotum = $nomination->quotum;
    $position->percentage = $nomination->percentage;
    if (!isset($nomination->quotumtype)) {
        $nomination->quotumtype = $position->quotumtype;
    }
    $position->quotumtype = $nomination->quotumtype;
    $position->minrunners = $nomination->minrunners;

    return $DB->update_record('nomination_position', $position);
}

/**
 * Removes an instance of the nomination from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function nomination_delete_instance($id) {
    global $DB;
    $DB->delete_records('nomination_position', array('nomid' => $id));
    $DB->delete_records('nomination_runner', array('nomid' => $id));
    $DB->delete_records('nomination_vote', array('nomid' => $id));
    $DB->delete_records('nomination_crowd', array('nomid' => $id));
    $DB->delete_records('nomination', array('id' => $id));
    return true;
}


/**
 * Election Cron.
 *
 * @return boolean
 **/
function nomination_cron () {
}

function nomination_extend_settings_navigation(settings_navigation $settings,
    navigation_node $nominationnode) {
    global $PAGE;

    $context = $PAGE->cm->context;
    if (has_capability('mod/nomination:viewresults', $context)) {
        $url = '/mod/nomination/report.php';
        $node = navigation_node::create(get_string('results', 'mod_nomination'),
                new moodle_url($url, array('id' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'report');
        $nominationnode->add_node($node, null);
    }
}