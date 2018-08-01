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

class restore_nomination_activity_structure_step extends restore_activity_structure_step {


    protected $runners = array();
    protected $votes = array();
    protected $positions = array();
    protected $crowds = array();
    protected $newnominationid;

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('nomination', '/activity/nomination');
        $paths[] = new restore_path_element('nomination_positions', '/activity/nomination/positions/position');

        if ($userinfo) {
            $paths[] = new restore_path_element('nomination_runners', '/activity/nomination/runners/runner');
            $paths[] = new restore_path_element('nomination_crowds', '/activity/nomination/crowds/crowd');
            $paths[] = new restore_path_element('nomination_votes', '/activity/nomination/votes/vote');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_nomination($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the nomination record.
        $newitemid = $DB->insert_record('nomination', $data);
        $this->newnominationid = $newitemid;
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_nomination_positions($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->nomid = $this->get_new_parentid('nomination');
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $newitemid = $DB->insert_record('nomination_position', $data);
        $data->id = $newitemid;
        $this->positions[$oldid] = $data;
    }

    protected function process_nomination_runners($data) {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;

        $data->nomid = $this->get_new_parentid('nomination');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('nomination_runner', $data);
        $data->id = $newitemid;
        $this->runners[$oldid] = $data;
    }

    protected function process_nomination_votes($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->nomid = $this->get_new_parentid('nomination');
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $newitemid = $DB->insert_record('nomination_vote', $data);
        $data->id = $newitemid;
        $this->votes[$oldid] = $data;
    }

    protected function process_nomination_crowds($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->nomid = $this->get_new_parentid('nomination');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $newitemid = $DB->insert_record('nomination_crowd', $data);
        $data->id = $newitemid;
        $this->crowds[$oldid] = $data;
    }

    protected function after_execute() {
        global $DB, $CFG;

        foreach ($this->runners as $runner) {
            if (isset($this->positions[$runner->posid])) {
                $newposition = $this->positions[$runner->posid];
                $DB->set_field('nomination_runner', 'posid', $newposition->id, array('id' => $runner->id));
            }
        }

        foreach ($this->votes as $vote) {
            if (isset($this->positions[$vote->posid])) {
                $newposition = $this->positions[$vote->posid];
                $DB->set_field('nomination_vote', 'posid', $newposition->id, array('id' => $vote->id));
            }
            if (isset($this->runners[$vote->runnerid])) {
                $newrunner = $this->runners[$vote->runnerid];
                $DB->set_field('nomination_vote', 'runnerid', $newrunner->id, array('id' => $vote->id));
            }
        }

        foreach ($this->crowds as $crowd) {
            if (isset($this->positions[$crowd->posid])) {
                $newposition = $this->positions[$crowd->posid];
                $DB->set_field('nomination_crowd', 'posid', $newposition->id, array('id' => $crowd->id));
            }
            if (isset($this->votes[$crowd->voteid])) {
                $newvote = $this->votes[$crowd->voteid];
                $DB->set_field('nomination_crowd', 'voteid', $newvote->id, array('id' => $crowd->id));
            }
        }

        $userinfo = $this->get_setting_value('userinfo');
        
        if (!$userinfo) {
            if ($resnom = $DB->get_record('nomination', array('id' => $this->newnominationid))) {
                $dates = array('selfstart', 'selfstop', 'runstart', 'runstop', 'withdrawstop');
                $olddate = $resnom->timecreated;
                $now = time();
                $resnom->timecreated = $now;
                $resnom->timemodified = 0;
                foreach ($dates as $date) {
                    $diff = $resnom->$date - $olddate;
                    $resnom->$date = $now + $diff + ( 60 * 5);
                }
                $DB->update_record('nomination', $resnom);
            }
        }

        // Add compass related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_compass', 'intro', null);
    }

}