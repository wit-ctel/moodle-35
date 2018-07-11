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
 * student fee block renderer
 *
 * @package    block_student_fee
 * @copyright  2017 Pete Windle, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Support Section Block Rendrer
 *
 * @copyright  2017 Pete Windle, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_supportsection_renderer extends plugin_renderer_base {
    
    /**
     * Call the Support Section RSS feed from elearning website
     *  
     * @return [type] [description]
     */
    public function display_search($supportrssurl,$supportsearchurl){
        
        $template = new stdClass();
        $supportxml = simplexml_load_file($supportrssurl);
        $supportarray = array();

        // create simple array from the complex XML object
        // include only the item title and URL
        for($i = 0; $i < 5; $i++){
            $title = (string)$supportxml->channel->item[$i]->title;
            $url = (string)$supportxml->channel->item[$i]->link;
            $supportarray[$i] = array(
                'key' => $i,
                'title' => $title,
                'url' => $url,);
            }

        $template->supportarray = $supportarray;
        $template->supportsearch = $supportsearchurl;
        return $this->render_from_template('block_supportsection/search', $template);
    }
    
}
    