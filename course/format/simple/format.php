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
 * Simple course format.  Display a single section at a time.
 *
 * @package format_simple
 * @copyright Waterford Institute of Technology, 2014
 * @author Cathal O'Riordan, CTEL.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure all sections are created
$course = course_get_format($course)->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_simple');

if (empty($displaysection)) {
  $displaysection = 0; // we always default to section 0
}

if ($PAGE->user_is_editing()) {
  $PAGE->requires->yui_module('moodle-format_simple-section',
          'M.format_simple.section.init_section',
          array(array(
              'sectionid' => $displaysection
          ))
  );
  
  $renderer->print_multiple_section_page($course, null, null, null, null);
} else {
  $renderer->print_single_section_page($course, null, null, null, null, $displaysection);  
}




// Include course format js module
$PAGE->requires->js('/course/format/simple/format.js');
