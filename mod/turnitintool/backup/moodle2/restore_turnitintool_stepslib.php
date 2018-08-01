<?php

// This file is part of Moodle - http://moodle.org/
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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one turnitintool activity
 */

require_once($CFG->dirroot."/mod/turnitintool/lib.php");

class restore_turnitintool_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('turnitintool_courses', '/activity/turnitintool/course');
        $paths[] = new restore_path_element('turnitintool', '/activity/turnitintool');
        $paths[] = new restore_path_element('turnitintool_parts', '/activity/turnitintool/parts/part');

        if ($userinfo) {
            $paths[] = new restore_path_element('turnitintool_submissions', '/activity/turnitintool/submissions/submission');
            $paths[] = new restore_path_element('turnitintool_comments', '/activity/turnitintool/submissions/submission/comments/comment');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_turnitintool($data) {
        global $CFG, $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        if ($data->grade < 0) {
            // scale found, get mapping
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        if ($CFG->turnitin_account_id!=$data->tiiaccount) {
            $a = new stdClass();
            $a->backupid=$data->tiiaccount;
            $a->current=$CFG->turnitin_account_id;
            turnitintool_print_error('wrongaccountid','turnitintool',NULL,$a);
            return false;
        } else {
            // insert the activity record
            $newitemid = $DB->insert_record('turnitintool', $data);
            // immediately after inserting "activity" record, call this
            $this->apply_activity_instance($newitemid);
        }
    }

    protected function process_turnitintool_courses($data) {
        global $DB, $USER;

        $data = (object)$data;
        $oldid = $data->id;
        $data->courseid = $this->get_courseid();

        $owneremail = (empty($data->owneremail)) ? join(array_splice(explode(".",$data->ownerun),0,-1)) : $data->owneremail;
        $owner = $DB->get_record('user', array('email'=>$owneremail));

        if ($owner) {
            $data->ownerid = $owner->id;
        } else {
            // Turnitin class owner not found so use restoring user as owner
            $data->ownerid = $USER->id;
        }

        $tiiowner = new stdClass();
        $tiiowner->userid = $data->ownerid;
        $tiiowner->turnitin_uid = $data->ownertiiuid;
        if (!$tiiuser = $DB->get_record('turnitintool_users', array('userid'=>$data->ownerid))) {
            $DB->insert_record('turnitintool_users',$tiiowner);
        }
        if ( !$DB->get_records_select('turnitintool_courses', 'courseid='.$data->courseid )) {
            $newitemid = $DB->insert_record('turnitintool_courses', $data);
            $this->set_mapping('turnitintool_courses', $oldid, $newitemid);
        }
    }

    protected function process_turnitintool_parts($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->turnitintoolid = $this->get_new_parentid('turnitintool');

        $newitemid = $DB->insert_record('turnitintool_parts', $data);
        $this->set_mapping('turnitintool_parts', $oldid, $newitemid);
    }

    protected function process_turnitintool_submissions($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->turnitintoolid = $this->get_new_parentid('turnitintool');
        $data->submission_part = $this->get_mappingid('turnitintool_parts', $data->submission_part);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->submission_hash = $data->userid.'_'.$data->turnitintoolid.'_'.$data->submission_part;

        // Create TII User Account Details
        if (!$tiiuser = $DB->get_record('turnitintool_users', array('turnitin_uid'=>$data->tiiuserid))) {
            $tiiuser = new stdClass();
            $tiiuser->userid=$data->userid;
            $tiiuser->turnitin_uid=$data->tiiuserid;
            $DB->insert_record('turnitintool_users',$tiiuser);
        }

        // Check if this hash already exists.
        if ($check_hash = $DB->get_record('turnitintool_submissions', array('submission_hash' => $data->submission_hash))) {
            $data->submission_hash = turnitintool_genUuid();
        }

        // Insert the submission as we have a unique hash.
        $newitemid = $DB->insert_record('turnitintool_submissions', $data);
        $this->set_mapping('turnitintool_submissions', $oldid, $newitemid);
    }

    protected function process_turnitintool_comments($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        if (!isset($data->commenttext)) {
            $data->commenttext=$data->comment;
            $data->dateupdated=$data->date;
        }

        $data->submissionid = $this->get_mappingid('turnitintool_submissions', $data->submissionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('turnitintool_comments', $data);
    }

}

//?>