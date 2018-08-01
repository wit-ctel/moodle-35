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

class backup_election_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $election = new backup_nested_element('election', array('id'), array(
            'course', 'name', 'intro', 'introformat',
            'timecreated', 'timemodified',
            'opendate', 'closedate', 'seats', 'electionlist', 'linkednomination'));

        $candidates = new backup_nested_element('candidates');

        $candidate = new backup_nested_element('candidate', array('id'), array(
            'election', 'candidate', 'timecreated', 'groupid'));

        $submitted = new backup_nested_element('submitted');

        $submit = new backup_nested_element('submit', array('id'), array(
            'election', 'userid', 'groupid', 'timecreated'));

        $votes = new backup_nested_element('votes');

        $vote = new backup_nested_element('vote', array('id'), array(
            'election', 'candidateid', 'preference', 'parentid', 'groupid'));

        // Build the tree.
        $election->add_child($candidates);
        $candidates->add_child($candidate);

        $election->add_child($submitted);
        $submitted->add_child($submit);

        $election->add_child($votes);
        $votes->add_child($vote);

        $election->set_source_table('election', array('id' => backup::VAR_ACTIVITYID));

        $candidate->set_source_sql('SELECT * FROM {election_candidates} WHERE election = ?', array(backup::VAR_PARENTID));

        if ($userinfo) {

            $submit->set_source_sql('SELECT * FROM {election_submitted} WHERE election = ?', array(backup::VAR_PARENTID));

            $vote->set_source_sql('SELECT * FROM {election_votes} WHERE election = ?', array(backup::VAR_PARENTID));
        }

        // Define id annotations.
        $submit->annotate_ids('user', 'userid');

        $submit->annotate_ids('group', 'groupid');

        $vote->annotate_ids('group', 'groupid');

        $candidate->annotate_ids('group', 'groupid');

        // Define file annotations.
        // Return the root element (election), wrapped into standard activity structure.
        return $this->prepare_activity_structure($election);

    }
}