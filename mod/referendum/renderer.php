<?php
// This file is part of the Referendum plugin for Moodle
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

defined('MOODLE_INTERNAL') || die();

class mod_referendum_renderer extends plugin_renderer_base {    
    
    public function referendum_dates($dates) {
    	$humandates = new Object(); 
    	foreach ($dates as $key => $value) {
            $humandates->$key = userdate($value, get_string('strftimedatetimeshort'));
        }
        $content = '';
        $content .= html_writer::start_tag('div', array('class' => 'well'));
        $content .= html_writer::tag('strong', get_string('referenduminfo', 'mod_referendum'));
        $now = userdate(usertime(time(), usertimezone()), get_string('strftimedatetimeshort'));
        $content .= html_writer::start_tag('div', array('class' => 'row-fluid'));
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('timenow', 'mod_referendum'));
        $content .= html_writer::tag('dd', $now);
       	$content .= html_writer::end_tag('dl');
        $content .= html_writer::end_tag('div');
        
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('referendumdates', 'mod_referendum'));
        $content .= html_writer::tag('dd', get_string('start', 'mod_referendum') . $humandates->opendate);
        $content .= html_writer::tag('dd', get_string('stop', 'mod_referendum') . $humandates->closedate);
        $content .= html_writer::end_tag('dl');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    public function referendum_results($results) {
        $content = '';

        $content .= html_writer::start_tag('div', array('class' => 'well'));
        $content .= html_writer::tag('strong', get_string('referenduminfo', 'mod_referendum'));
        $content .= html_writer::start_tag('div', array('class' => 'row-fluid'));
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4 text-center'));
        $content .= html_writer::tag('span', get_string('votedyescount', 'mod_referendum', $results->yescount));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4 text-center'));
        $content .= html_writer::tag('span', get_string('votednocount', 'mod_referendum', $results->nocount));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }
}