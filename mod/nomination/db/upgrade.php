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

function xmldb_nomination_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015093000) {

        // Define field quotumtype to be added to nomination_position.
        $table = new xmldb_table('nomination_position');
        $field = new xmldb_field('quotumtype', XMLDB_TYPE_INTEGER, '2', null, null, null, '1', 'quotum');

        // Conditionally launch add field quotumtype.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Nomination savepoint reached.
        upgrade_mod_savepoint(true, 2015093000, 'nomination');
    }

    if ($oldversion < 2015093001) {

        // Define field percentage to be added to nomination_position.
        $table = new xmldb_table('nomination_position');
        $field = new xmldb_field('percentage', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'quotum');

        // Conditionally launch add field percentage.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Nomination savepoint reached.
        upgrade_mod_savepoint(true, 2015093001, 'nomination');
    }

    if ($oldversion < 2015093002) {

        // Changing type of field percentage on table nomination_position to number.
        $table = new xmldb_table('nomination_position');
        $field = new xmldb_field('percentage', XMLDB_TYPE_NUMBER, '10, 2', null, null, null, null, 'quotum');

        // Launch change of type for field percentage.
        $dbman->change_field_type($table, $field);

        // Nomination savepoint reached.
        upgrade_mod_savepoint(true, 2015093002, 'nomination');
    }

    if ($oldversion < 2015101400) {

        // Define field groupid to be added to nomination_crowd.
        $table = new xmldb_table('nomination_crowd');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'voteid');

        // Conditionally launch add field groupid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Nomination savepoint reached.
        upgrade_mod_savepoint(true, 2015101400, 'nomination');
    }

    if ($oldversion < 2015101401) {

        $DB->delete_records('nomination_vote');
        $DB->delete_records('nomination_crowd');

        upgrade_mod_savepoint(true, 2015101401, 'nomination');
    }

    if ($oldversion < 2015110600) {

        // Define field withdrawstop to be added to nomination.
        $table = new xmldb_table('nomination');
        $field = new xmldb_field('withdrawstop', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'runstop');

        // Conditionally launch add field withdrawstop.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Nomination savepoint reached.
        upgrade_mod_savepoint(true, 2015110600, 'nomination');
    }

    return true;
}


