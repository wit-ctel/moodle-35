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
 * student fee block admin settings
 *
 * @package    block_student_fee
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext(
            'block_student_fee/feebalance_field_title',
            get_string('labelfeebalancefieldtitle', 'block_student_fee'),
            get_string('desfeebalancefieldtitle', 'block_student_fee'),
            get_string('defaultfeebalancefieldtitle', 'block_student_fee')
        ));

$settings->add(new admin_setting_confightmleditor(
            'block_student_fee/overdue_notice',
            get_string('labeloverduenotice', 'block_student_fee'),
            get_string('descoverduenotice', 'block_student_fee'),
            ""
        ));