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

namespace theme_wit\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use paging_bar;
use context_course;
use pix_icon;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_wit
 * @copyright  2018 Waterford Institute of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE;

        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('card');
        $html .= html_writer::start_div('card-block');
        $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        $html .= html_writer::start_div('pull-xs-left');
        

        // Title and Breadcrumb
        $html .= html_writer::start_div('context_container');
        // Header Image
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_div();
        $html .= $this->context_header();

        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100', array('id' => 'page-navbar'));
            $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right customise-this-page');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right customise-this-page');
        }
        $html .= html_writer::end_div();

        $html .= html_writer::end_div();
        
    
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }
    

}