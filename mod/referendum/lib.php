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


function referendum_supports($feature) {
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
 * Saves a new instance of the referendum into the database
 *
 * @param object $referendum An object from the form in mod_form.php
 * @param mod_referendum_mod_form $mform
 * @return int The id of the newly inserted referendum record
 */
function referendum_add_instance(stdClass $referendum, mod_referendum_mod_form $mform = null) {
    global $DB;

    $referendum->timecreated = time();
    $referendumid = $DB->insert_record('referendum', $referendum);
    if (isset($referendum->referendumlist) && !emtpy($referendum->referendumlist)) {
        referendum_save_candidates($referendum->referendumlist, $referendumid);
    }
    return $referendumid;
}

/**
 * Updates an instance of the referendum in the database
 *
 *
 * @param object $referendum An object from the form in mod_form.php
 * @param mod_referendum_mod_form $mform
 * @return boolean Success/Fail
 */
function referendum_update_instance(stdClass $referendum, mod_referendum_mod_form $mform = null) {
    global $DB;

    $referendum->timemodified = time();
    $referendum->id = $referendum->instance;

    return $DB->update_record('referendum', $referendum);
}


/**
 * Removes an instance of the referendum from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function referendum_delete_instance($id) {
    global $DB;
    $DB->delete_records('referendum_votes', array('referendum' => $id));
    $DB->delete_records('referendum_submitted', array('referendum' => $id));
    $DB->delete_records('referendum', array('id' => $id));
    return true;
}


/**
 * Referendum Cron.
 *
 * @return boolean
 **/
function referendum_cron () {
}

function referendum_extend_settings_navigation(settings_navigation $settings,
    navigation_node $referendumnode) {
    global $PAGE;

    $context = $PAGE->cm->context;
    if (has_capability('mod/referendum:viewresults', $context)) {
        $url = '/mod/referendum/report.php';
        $node = navigation_node::create(get_string('results', 'mod_referendum'),
                new moodle_url($url, array('id' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING, null, 'report');
        $referendumnode->add_node($node, null);
    }
}