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
 * @package    block_supportsection
 * @copyright  2017 Pete Windle, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext(
            'block_supportsection/support_section_rss_url',
            get_string('labelsupportsectiontitle', 'block_supportsection'),
            get_string('dessupportsectiontitle', 'block_supportsection'),
            get_string('defaultsupportsectiontitle', 'block_supportsection')
        ));

$settings->add(new admin_setting_configtext(
            'block_supportsection/support_section_search_url',
            get_string('labelsupportsectionsearchtitle', 'block_supportsection'),
            get_string('dessupportsectionsearchtitle', 'block_supportsection'),
            get_string('defaultsupportsectionsearchtitle', 'block_supportsection')
        ));
