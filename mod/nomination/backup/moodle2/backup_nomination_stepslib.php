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

class backup_nomination_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $nomination = new backup_nested_element('nomination', array('id'), array(
            'course', 'name', 'intro', 'introformat',
            'timecreated', 'timemodified',
            'selfstart', 'selfstop', 'runstart', 'runstop', 'withdrawstop', 'anonymous', 'policy'));

        $runners = new backup_nested_element('runners');

        $runner = new backup_nested_element('runner', array('id'), array(
            'nomid', 'posid', 'userid', 'groupid', 'firstname', 'lastname', 'timecreated', 'state', 'policyagreed'));

        $positions = new backup_nested_element('positions');

        $position = new backup_nested_element('position', array('id'), array(
            'nomid', 'name', 'groupid', 'quotum', 'percentage', 'quotumtype', 'minrunners'));

        $crowds = new backup_nested_element('crowds');

        $crowd = new backup_nested_element('crowd', array('id'), array(
            'nomid', 'posid', 'userid', 'voteid', 'groupid'));

        $votes = new backup_nested_element('votes');

        $vote = new backup_nested_element('vote', array('id'), array(
            'nomid', 'posid', 'groupid', 'runnerid'));

        // Build the tree.
        $nomination->add_child($runners);
        $runners->add_child($runner);

        $nomination->add_child($positions);
        $positions->add_child($position);

        $nomination->add_child($crowds);
        $crowds->add_child($crowd);

        $nomination->add_child($votes);
        $votes->add_child($vote);

        $nomination->set_source_table('nomination', array('id' => backup::VAR_ACTIVITYID));

        $position->set_source_table('nomination_position', array('nomid' => backup::VAR_PARENTID), 'id ASC');

        if ($userinfo) {

            $crowd->set_source_sql('SELECT * FROM {nomination_position} WHERE nomid = ?', array(backup::VAR_PARENTID));

            $runner->set_source_sql('SELECT * FROM {nomination_runner} WHERE nomid = ?', array(backup::VAR_PARENTID));

            $crowd->set_source_sql('SELECT * FROM {nomination_crowd} WHERE nomid = ?', array(backup::VAR_PARENTID));

            $vote->set_source_sql('SELECT * FROM {nomination_vote} WHERE nomid = ?', array(backup::VAR_PARENTID));
        }

        // Define id annotations.
        $runner->annotate_ids('user', 'userid');
        $runner->annotate_ids('group', 'groupid');

        $crowd->annotate_ids('user', 'userid');
        $crowd->annotate_ids('group', 'groupid');
        
        $position->annotate_ids('group', 'groupid');
        
        $vote->annotate_ids('group', 'groupid');

        // Define file annotations.
        // Return the root element (nomination), wrapped into standard activity structure.
        return $this->prepare_activity_structure($nomination);

    }
}