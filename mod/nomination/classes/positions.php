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
define('RUNNER_NEW', 0);
define('RUNNER_ACCEPTED', 1);
define('RUNNER_NOMINATED', 2);

class nomination_positions {

    protected $name;
    protected $nomid;
    protected $minrunners = 1;
    protected $quotum = 0;
    protected $groupid = 0;

    public function __construct($nomid, $groupid = 0) {
        $this->nomid = $nomid;
        $this->groupid = $groupid;
    }

    public function get_position($posid) {
        global $DB;
        // The groupid defaults to 0. When creating unique postitions
        // For different groups use $this->groupid.
        if ($position = $DB->get_record('nomination_position', array('id' => $posid,
            'nomid' => $this->nomid, 'groupid' => 0))) {
            $position->quotum = $this->get_quotum($position);
            return $position;
        }
        return false;
    }

    public function get_positions() {
        global $DB;
                // The groupid defaults to 0. When creating unique postitions
        // For different groups use $this->groupid.
        if ($positions = $DB->get_records('nomination_position', array('nomid' => $this->nomid,
            'groupid' => 0))) {
            foreach ($positions as &$position) {
                $position->quotum = $this->get_quotum($position);
            }
            return $positions;
        }
        return array();
    }

    public function get_positions_array($userid = null) {
        $userrunningfor = array();
        if ($userid) {
            $userrunningfor = $this->user_running_for($userid);
        }

        $position_names = array();
        if ($positions = $this->get_positions()) {
            foreach ($positions as $position) {
                if (!array_key_exists($position->id, $userrunningfor)) {
                    $position_names[$position->id] = $position->name;
                }
            }
        }
        return $position_names;
    }

    private function get_quotum($position) {
        $DB;
        if ($position->quotumtype == 1) {
            return $position->quotum;
        }
        if ($position->quotumtype == 2) {
            $cm = get_coursemodule_from_instance('nomination', $position->nomid);
            $coursecontext = context_course::instance($cm->course);
            $groupmode = groups_get_activity_groupmode($cm);
            if ($groupmode) {
                $groupid = groups_get_activity_group($cm, true);
                if (is_numeric($groupid)) {
                    $membercount = count_enrolled_users($coursecontext, '', $groupid);
                    return ($membercount / 100) * $position->percentage;
                }
            } else {
                $membercount = count_enrolled_users($coursecontext);
                return ($membercount / 100) * $position->percentage;
            }
            return 0;

        }
    }

    public function user_running_for($userid) {
        global $DB;

        $positions = array();
        $runforpositions = $DB->get_records('nomination_runner', array('nomid' => $this->nomid, 
            'groupid' => $this->groupid));

        foreach ($runforpositions as $rfp) {
            $position = $DB->get_record('nomination_position', array('id' => $rfp->posid));
            $positions[$position->id] = $position;
        }
        return $positions;
    }

    public function store_position($name, $minrunners, $quotum, $id) {
        global $DB;
        
        if ($position = $this->get_position($id)) {
            $position->name = $name;
            $position->minrunners = $minrunners;
            $position->quotum = $quotum;
            if ($DB->update_record('nomination_position', $position)) {
                return true;
            }
        } else {
            if (empty($name)) {
                return false;
            }

            $position = new Object();
            $position->name = $name;
            $position->minrunners = $minrunners;
            $position->nomid = $this->nomid;
            $position->quotum = $quotum;
            $position->groupid = $this->groupid;
            if ($DB->insert_record('nomination_position', $position)) {
                return true;
            }
        }
        return false;
    }

    public function get_formdata() {
        if ($positions = $this->get_positions()) {
            $formdata = array();
            foreach ($positions as $position) {
                $formdata['position'][] = $position->name;
                $formdata['minrunners'][] = $position->minrunners;
                $formdata['quotum'][] = $position->quotum;
                $formdata['posid'][] = $position->id;
            }
            return $formdata;
        }
        return array();
    }

    public function registerrunner($data, $groupid) {
        global $USER, $DB;

        $data->state = RUNNER_NEW;
        if (!$data->runnerid) {
            $data->userid = $USER->id;
        }
        $data->timecreated = time();
        $data->groupid = $groupid;

        if ($data->runnerid) {
            if ($runner = $DB->get_record('nomination_runner', array('id' => $data->runnerid))) {
                $data->id = $data->runnerid;
                if ($DB->update_record('nomination_runner', $data)) {
                    return 'runnerupdated';
                }
            }
        } else if ($runner = $DB->insert_record('nomination_runner', $data)) {
            return 'runneradded';
        } else {
            return 'runneraddfailed';
        }
    }

    public function get_runners($position) {
        global $DB;
        if ($runners = $DB->get_records('nomination_runner', array('nomid' => $this->nomid,
            'posid' => $position->id, 'groupid' => $this->groupid), 'lastname')) {
            return $runners;
        } else {
            return array();
        }
    }

    public function is_runner($position) {
        global $DB, $USER;
        if ($runners = $DB->get_records('nomination_runner', array('nomid' => $this->nomid,
            'posid' => $position->id, 'groupid' => $this->groupid, 'userid' => $USER->id))) {
            return $runners;
        } else {
            return false;
        }
    }

    public function get_runner($position) {
        global $DB, $USER;

        if ($runner = $DB->get_record('nomination_runner', array('nomid' => $this->nomid,
            'posid' => $position->id, 'groupid' => $this->groupid, 'userid' => $USER->id))) {
            return $runner;
        } else {
            return false;
        }
    }

    public function get_votes($runner) {
        global $DB;
        if ($votes = $DB->get_records('nomination_vote', array('nomid' => $this->nomid, 
            'runnerid' => $runner->id))) {
            return $votes;
        } else {
            return array();
        }
    }

    public function active_runners($position) {
        $runners = $this->get_runners($position);
        $activerunners = array();
        foreach ($runners as $runner) {
            $votes = $this->get_votes($runner);
            if (count($votes) < $position->quotum) {
                $activerunners[$runner->id] = $runner;
            } 
        }
        return $activerunners;
    }

}
