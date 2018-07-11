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
$manage = optional_param('manage', '', PARAM_INT);
$signup = optional_param('signup', '', PARAM_INT);
$vote = optional_param('vote', '', PARAM_INT);
$addingfields = optional_param('option_add_fields', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_ALPHA);
$runnerid = optional_param('runnerid', '', PARAM_INT);
$posid = optional_param('posid', '', PARAM_INT);
$group = optional_param('group', '', PARAM_INT);


if ($cmid) {
    $id = $cmid;
}

if (!$cm = get_coursemodule_from_id('nomination', $id)) {
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
echo $OUTPUT->header($nomination->nomination->name);

if ($nomination->nomination->name) {
    echo html_writer::tag('h2', $nomination->nomination->name);
}

switch ($action) {
    case 'edit':
        $nomination->preparerunnerform($runnerid);
        $nomination->runnerform->display();
        break;
    case 'delete':
        $nomination->deleterunner($runnerid, 'delete');
        break;
    case 'withdraw':
        $nomination->deleterunner($runnerid, 'withdraw');
        break;
    case 'showsignupform':
        $nomination->runnerform->display();
        break;
    case 'managerunners':
        echo $nomination->get_runners();
        break;
}


if (isset($nomination->runnerform) && 
        $nomination->runnerform->is_submitted() && 
        !$nomination->runnerform->is_validated()) {
    $nomination->runnerform->display();
    echo $OUTPUT->footer();
    die();
}

if (empty($action)) {

    $intro = '';
    if ($nomination->nomination->intro) {
        $intro =  $OUTPUT->box(format_module_intro('nomination', $nomination->nomination, $cm->id), 'generalbox', 'intro');
    }

    if (!$nomination->displayvoteform) {
        echo $intro;
    }

    echo $nomination->nomination_dates();

    if ($nomination->finished) {
        $url = new moodle_url('/mod/nomination/report.php', array('id' => $cm->id));
        $label = get_string('results', 'mod_nomination');
        echo $OUTPUT->single_button($url, $label);
        echo $OUTPUT->footer();
        die();
    } 

    if ($nomination->vote && !$nomination->manage) {

        $groupmode = groups_get_activity_groupmode($cm);
        
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/nomination/view.php?id='.$id);
        }

        if ($nomination->canwithdraw && $nomination->isrunner) {
            echo $nomination->get_runners();
        }

        $nomination->vote_form($nomination->position->id);
        
        if ($nomination->displayvoteform) {
            if (!$nomination->nomorevotes) {
                $nomination->voteform->display();
            } 
        } 
    }

    if ($nomination->manage) {
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/nomination/view.php?id='.$id);
        }
        echo $nomination->get_runners();

        $label = get_string('addnominee', 'mod_nomination');
        $url = new moodle_url('/mod/nomination/view.php', array('cmid' => $cm->id, 'action' => 'showsignupform'));
        echo $OUTPUT->single_button($url, $label);
    }


    if ($nomination->signup) {
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/nomination/view.php?id='.$id);
        }

        $url = new moodle_url('/mod/nomination/view.php', array('cmid' => $cm->id, 'action' => 'showsignupform'));

        echo $nomination->get_runners();
        $label = get_string('signup', 'mod_nomination');
        
        if ($nomination->canselfnominate) {
            echo $OUTPUT->single_button($url, $label);
        }
    }

    
}

echo $nomination->get_messages();

echo $OUTPUT->footer();