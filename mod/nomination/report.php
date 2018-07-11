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

require_once("../../config.php");
require_once("lib.php");
require_once("classes/nomination.php");

$id = optional_param('id', '', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$cmid = optional_param('cmid', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);


if ($cmid) {
    $id = $cmid;
}

if (! $cm = get_coursemodule_from_id('nomination', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

$nomination = new nomination($cm, $course, $group);

$strnomination = get_string('modulename', 'nomination');

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/nomination/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($strnomination));
$PAGE->set_heading(format_string($nomination->nomination->name));
$PAGE->set_context($context);
echo $OUTPUT->header();

if ($nomination->manage) {
    $groupmode = groups_get_activity_groupmode($cm);
    if ($groupmode) {
        groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/nomination/report.php?id='.$id);
    }
}

if ($nomination->finished) {
    echo $nomination->get_results();
} else if ($nomination->viewprogress) {
	echo $nomination->get_results();
} else {
    echo html_writer::tag('div', get_string('nominationinprocess', 'mod_nomination'), array('class' => 'alert alert-info'));
}

echo $OUTPUT->footer();