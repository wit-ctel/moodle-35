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

function xmldb_election_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

     if ($oldversion < 2015100100) {

        // Define field linkednomination to be added to election.
        $table = new xmldb_table('election');
        $field = new xmldb_field('linkednomination', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'electionlist');

        // Conditionally launch add field linkednomination.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Election savepoint reached.
        upgrade_mod_savepoint(true, 2015100100, 'election');
    }

    if ($oldversion < 2015100101) {

        // Define field groupid to be added to election_candidates.
        $table = new xmldb_table('election_candidates');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Election savepoint reached.
        upgrade_mod_savepoint(true, 2015100101, 'election');
    }

    if ($oldversion < 2015100200) {

        // Define field groupid to be added to election_votes.
        $table = new xmldb_table('election_votes');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'parentid');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('election_submitted');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'userid');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Election savepoint reached.
        upgrade_mod_savepoint(true, 2015100200, 'election');
    }


    return true;
}


