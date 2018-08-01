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

require_once("../../config.php");
require_once("lib.php");
require_once("locallib.php");

$id = optional_param('id', '', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$cmid = optional_param('cmid', '', PARAM_INT);
$ballots = optional_param('showballots', 0, PARAM_INT);
$group = optional_param('group', 0, PARAM_INT);

if ($cmid) {
    $id = $cmid;
}

if (! $cm = get_coursemodule_from_id('election', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);
$groupmode = groups_get_activity_groupmode($cm);
if ($groupmode) {
    $group = groups_get_activity_group($cm, true);
}

$election = new election($cm, $course, $group);

$strelection = get_string('modulename', 'election');

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/election/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($strelection));
$PAGE->set_heading(format_string($election->election->name));
$PAGE->set_context($context);
echo $OUTPUT->header();

if ($groupmode) {    
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/election/report.php?id='.$id);
    if (empty($group)) {
    	echo $OUTPUT->footer();
    	die();
    }
}

if ($election->finished) {
    echo $election->get_results();
} else if ($election->view_progress()) {
    echo html_writer::tag('div', get_string('electionlive', 'mod_election'), array('class' => 'alert alert-danger'));
	echo $election->get_results();
} else {
    echo html_writer::tag('div', get_string('electioninprocess', 'mod_election'), array('class' => 'alert alert-danger'));
}

if ($election->view_progress() && $ballots) {
    echo $election->show_ballots();
}

echo $OUTPUT->footer();