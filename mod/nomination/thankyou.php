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

$strnomination = get_string('modulename', 'nomination');

$context = context_module::instance($cm->id);

$nomination = new nomination($cm, $course, $group, false);

$PAGE->set_url('/mod/nomination/thankyou.php', array('id' => $cm->id));
$PAGE->set_title(format_string($strnomination));
$PAGE->set_heading(format_string($strnomination));
$PAGE->set_context($context);
echo $OUTPUT->header();

echo html_writer::tag('h3', get_string('thankyou', 'mod_nomination'));

echo $nomination->nomination_dates();

if ($nomination->finished) {
    echo $nomination->get_results();
}

$returnurl = new moodle_url('/course/view.php', array('id' => $course->id));
echo html_writer::link($returnurl, get_string('returntocourse', 'mod_nomination'), array('class' => 'btn btn-default'));

echo $OUTPUT->footer();
