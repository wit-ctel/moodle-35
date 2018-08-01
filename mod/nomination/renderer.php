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

defined('MOODLE_INTERNAL') || die();

class mod_nomination_renderer extends plugin_renderer_base {

	public function print_runners($positions, $manage, $cmid) {
		$this->manager = $manage;
		$this->cmid = $cmid;

		if (count($positions) < 1) {
			return html_writer::tag('div', get_string('nopositions', 'mod_nomination'),
				array('class' => 'alert alert-warning'));
		}

		$content = '';
		$content .= html_writer::start_tag('dl');
		foreach ($positions as $position) {
			$content .= html_writer::tag('dt', html_writer::tag('h3', get_string('nominees', 'mod_nomination')));
			if (count($position->runners) == 0) {
				$content .= html_writer::tag('div', get_string('norunnersyet', 'mod_nomination'), array('class' => 'alert alert-warning'));
			}

			foreach ($position->runners as $runner) {
				$runnerinfo = $runner->firstname . ' ' . $runner->lastname . $this->actionmenu($runner);
				$content .= html_writer::tag('dd', $runnerinfo . '<br>');
			}
		}
		$content .= html_writer::end_tag('dl');
		return $content;
	}



    public function nomination_dates($dates) {
    	$humandates = new Object(); 
    	foreach ($dates as $key => $value) {
            $humandates->$key = userdate($value, get_string('strftimedatetimeshort'));
        }
        $content = '';
        $content .= html_writer::start_tag('div', array('class' => 'well'));
        $content .= html_writer::tag('strong', get_string('nominationinfo', 'mod_nomination'));
        $now = userdate(usertime(time(), usertimezone()), get_string('strftimedatetimeshort'));
        $content .= html_writer::start_tag('div', array('class' => 'row-fluid'));
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('timenow', 'mod_nomination'));
        $content .= html_writer::tag('dd', $now);
       	$content .= html_writer::end_tag('dl');
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('withdrawstop', 'mod_nomination'));
        $content .= html_writer::tag('dd', $humandates->withdrawstop);
        $content .= html_writer::end_tag('dl');
        $content .= html_writer::end_tag('div');
        
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('selfnominationactive', 'mod_nomination'));
        $content .= html_writer::tag('dd', get_string('start', 'mod_nomination') . $humandates->selfstart);
        $content .= html_writer::tag('dd', get_string('stop', 'mod_nomination') . $humandates->selfstop);
        $content .= html_writer::end_tag('dl');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'span4 col-md-4'));
        $content .= html_writer::start_tag('dl');
        $content .= html_writer::tag('dt', get_string('votingactive', 'mod_nomination'));
        $content .= html_writer::tag('dd', get_string('start', 'mod_nomination') . $humandates->runstart);
        $content .= html_writer::tag('dd', get_string('stop', 'mod_nomination') . $humandates->runstop);
        $content .= html_writer::end_tag('dl');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

	private function actionmenu($runner) {
		global $USER;

		if (($runner->userid != $USER->id) && !$this->manager) {
			return '';
		}

		$menu = new \action_menu();
		$trigger = html_writer::tag('span', get_string('editsettings', 'mod_nomination'), array('class' => 'add-menu'));
		$menu->set_menu_trigger($trigger);

		$options = array('runnerid' => $runner->id, 'action' => 'edit', 'cmid' => $this->cmid);
		$edit = new moodle_url('/mod/nomination/view.php', $options);
		$icon = new pix_icon('t/edit', get_string('editsettings', 'mod_nomination'));
		$link = new action_menu_link_secondary($edit, $icon, get_string('editsettings', 'mod_nomination'));
		$menu->add($link);
		
		if ($this->manager) {
			$options['action'] = 'delete';
			$edit = new moodle_url('/mod/nomination/view.php', $options);
			$icon = new pix_icon('t/delete', get_string('withdraw', 'mod_nomination'));
			$link = new action_menu_link_secondary($edit, $icon, get_string('deleterunner', 'mod_nomination'));
			$menu->add($link);
		} else if ($runner->userid == $USER->id) {
			$options['action'] = 'withdraw';
			$edit = new moodle_url('/mod/nomination/view.php', $options);
			$icon = new pix_icon('t/delete', get_string('withdraw', 'mod_nomination'));
			$link = new action_menu_link_secondary($edit, $icon, get_string('withdraw', 'mod_nomination'));
			$menu->add($link);
		}

		$menu->attributes['class'] .= ' manage-runner-menu ';
		return $this->render($menu);
	}

	public function print_results($positions) {
		global $OUTPUT;
		$content = '';



		foreach ($positions as $position) {
			$nominated = array();
			$runners = array();

			foreach ($position->runners as $runner) {
				$runner->icon = '';
				if ($runner->nominated) {
					$nominated[] = $runner;
					$runner->icon = $OUTPUT->pix_icon('t/check', get_string('winner', 'mod_nomination'));
				} else {
					$runners[] = $runner;
				}
			}

			$content .= ' ' . get_string('groupsize', 'mod_nomination') . $position->groupsize;
			$content .= ' ' . get_string('quotum', 'mod_nomination') . ' ' . $position->quotum;
			if ($position->quotumtype == 2) {
				$content .= ' (' . $position->percentage . ' %) ';
			}

			// Setup the table.
			$table = new html_table();
			$table->attributes['class'] = 'table table-striped';

			$table->head = array(get_string('nominee', 'mod_nomination'),
			get_string('numnominations', 'mod_nomination'),
			get_string('nominated', 'mod_nomination'));

			$table->colclasses = array();
			$table->data = array();


			foreach ($nominated as $nominee) {
				$row = new html_table_row();
				$cell = new html_table_cell();
	            $cell->text = '<strong>' . $nominee->firstname . ' ' . $nominee->lastname .'</strong>';
	            $row->cells[] = $cell;

	            $cell = new html_table_cell();
	            $cell->text = count($nominee->votes);
	            $row->cells[] = $cell;

	            $cell = new html_table_cell();
	            $cell->text = $nominee->icon;
	            $row->cells[] = $cell;
	            $table->data[] = $row;
			}

			foreach ($runners as $runner) {
				$row = new html_table_row();
				$cell = new html_table_cell();
	            $cell->text = $runner->firstname . ' ' . $runner->lastname;
	            $row->cells[] = $cell;

	            $cell = new html_table_cell();
	            $cell->text = count($runner->votes);
	            $row->cells[] = $cell;

	            $cell = new html_table_cell();
	            $cell->text = $runner->icon;
	            $row->cells[] = $cell;
	            $table->data[] = $row;
			}
			$content .= html_writer::table($table);
		}

		
		// $content .= html_writer::start_tag('dl');
		// $content .= html_writer::tag('dt', $position->name;
		// $content .= html_writer::tag('dd', $runner->firstname . ' ' . $runner->lastname . ': ' . $votes . ' ' . $icon);
		return $content;
	}
}