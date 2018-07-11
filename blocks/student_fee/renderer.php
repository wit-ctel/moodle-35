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
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Student_fee block rendrer
 *
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_student_fee_renderer extends plugin_renderer_base {
    
    public function display_balance($balance, $overduenotice) {
        
        if ($balance <= 0)
            return "";
        
        $template = new stdClass();
        $template->balance = $this->format_balance_for_output($balance);
        $template->overduenotice = $overduenotice;
        $template->isoverdue = ($balance > 0 ? true : false);
        
        return $this->render_from_template('block_student_fee/balance', $template);
    }
    
    private function format_balance_for_output($in) {
        $balance = number_format(abs($in), 2);
        
        if ($in >= 0) {
            $balance .= " DR";
        } else {
            $balance .= " CR";
        }
    
        return $balance;
    }
    
}
    