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

class restore_referendum_activity_structure_step extends restore_activity_structure_step {


    protected $items = array();
    protected $responses = array();
    protected $newreferendumid;

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('referendum', '/activity/referendum');

        if ($userinfo) {
            $paths[] = new restore_path_element('referendum_submitted', '/activity/referendum/submitted/submit');
            $paths[] = new restore_path_element('referendum_votes', '/activity/referendum/votes/vote');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_referendum($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the referendum record.
        $newitemid = $DB->insert_record('referendum', $data);
        $this->newreferendumid = $newitemid;
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_referendum_submitted($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->referendum = $this->get_new_parentid('referendum');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $newitemid = $DB->insert_record('referendum_submitted', $data);
    }

    protected function process_referendum_votes($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->referendum = $this->get_new_parentid('referendum');
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $newitemid = $DB->insert_record('referendum_votes', $data);
    }

    protected function after_execute() {
        global $DB, $CFG;
        
        $userinfo = $this->get_setting_value('userinfo');
        
        if (!$userinfo) {
            if ($resref = $DB->get_record('referendum', array('id' => $this->newreferendumid))) {
                $dates = array('opendate', 'closedate');
                $olddate = $resref->timecreated;
                $now = time();
                $resref->timecreated = $now;
                $resref->timemodified = 0;
                foreach ($dates as $date) {
                    $diff = $resref->$date - $olddate;
                    $resref->$date = $now + $diff + ( 60 * 5);
                }
                $DB->update_record('referendum', $resref);
            }
        }
    }

}