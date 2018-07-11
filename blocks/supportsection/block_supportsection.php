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
 * Display Frequent Support Issues within Moodle and provide option 
 * to search the knowledge base from Moodle
 *
 * @package    block_supportsection
 * @copyright  2017 Pete Windle, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_supportsection extends block_base {
    public function init() {
        $this->title = get_string('supportsection', 'block_supportsection');
    }
        
	/**
	 * Render the content and return it
	 * @return string
	 */
    public function get_content() {
	    if ($this->content !== null) {
	      return $this->content;
	    }
	 	$this->content         =  new stdClass;
	    $this->content->text   = '';
	    $this->content->footer = '';
	    $supportsearchurl = get_config('block_supportsection', 'support_section_search_url');
	    $supportrssurl = get_config('block_supportsection', 'support_section_rss_url');
	    if ($supportrssurl == ""){
	    	echo "not set";
	    	echo $supportrssurl;
	    	$supportrssurl = "http://staging.elearning.wit.ie/support/moodle-support-section";
	    }

	  	$renderer = $this->page->get_renderer('block_supportsection');
	  	$this->content->text .= $renderer->display_search($supportrssurl,$supportsearchurl);
	    return $this->content;
	}	
	/**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}