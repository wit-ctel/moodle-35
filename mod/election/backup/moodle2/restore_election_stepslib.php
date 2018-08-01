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

class restore_election_activity_structure_step extends restore_activity_structure_step {


    protected $votes = array();
    protected $submitted = array();
    protected $candidates = array();
    protected $parentmap = array();

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('election', '/activity/election');
        $paths[] = new restore_path_element('election_candidates', '/activity/election/candidates/candidate');

        if ($userinfo) {
            $paths[] = new restore_path_element('election_submitted', '/activity/election/submitted/submit');
            $paths[] = new restore_path_element('election_votes', '/activity/election/votes/vote');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_election($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the election record.
        $newitemid = $DB->insert_record('election', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_election_candidates($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->election = $this->get_new_parentid('election');
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('election_candidates', $data);

        $data->id = $newitemid;
        $this->candidates[$oldid] = $data;
    }

    protected function process_election_submitted($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->election = $this->get_new_parentid('election');

        $data->userid = $this->get_mappingid('user', $data->userid);

        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('election_submitted', $data);

        $data->id = $newitemid;
        $this->submitted[$oldid] = $data;
    }

    protected function process_election_votes($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->election = $this->get_new_parentid('election');
        
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('election_votes', $data);

        $data->id = $newitemid;

        $this->votes[$oldid] = $data;
    }

    protected function after_execute() {
        global $DB, $CFG;

        $parentmapping = array();
        foreach ($this->votes as $oldid => $vote) {
            if (isset($this->candidates[$vote->candidateid])) {
                $new = $this->candidates[$vote->candidateid];
                $DB->set_field('election_votes', 'candidateid', $new->id, array('id' => $vote->id));
            }
            if (array_key_exists($vote->parentid, $this->votes)) {
                $parentvote = $this->votes[$vote->parentid];
                $DB->set_field('election_votes', 'parentid', $parentvote->id, array('id' => $vote->id));
            }
        }
    }

}