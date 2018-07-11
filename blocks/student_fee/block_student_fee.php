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
 * Display tuition fee balance to students.
 *
 * @package    block_student_fee
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class block_student_fee extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_student_fee');
    
    }
  
    /**
     * Return contents of student_fees block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER;
        
        if($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real user
        }
        
        $feebalancefield = get_config('block_student_fee', 'feebalance_field_title');
        
        // check we have a custom profile field for 'feebalance'
        if (isset($USER->profile) && isset($USER->profile[$feebalancefield])) {
            $renderer = $this->page->get_renderer('block_student_fee');
            $overduenotice = get_config('block_student_fee', 'overdue_notice');
            
            $feebalance = str_replace(array(",", "€"), '', $USER->profile[$feebalancefield]);
            
            if (!is_numeric($feebalance)) { // only work with numeric balances
                return $this->content;
            }
            
            $this->content->text .= $renderer->display_balance($feebalance, $overduenotice);
        }    
        
        return $this->content;
    }
    
    public function instance_allow_multiple() {
        return false;
    }
    
    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
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



