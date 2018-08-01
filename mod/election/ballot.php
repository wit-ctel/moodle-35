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
 * @package    mod_election
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class Ballot {

    protected $identifier;
    protected $lastusedlevel = 0;
    protected $ranking = [];
    protected $value = 1;


    public function __construct(array $ranking, $value = 1) {
        $this->ranking = $ranking;
        $this->value = $value;
    }

    public function get_identifier() {
        if (!isset($this->identifier)) {
            $this->identifier = '';
            foreach ($this->ranking as $preference) {
                if (is_array($preference)) {
                    $preference = implode('=', $preference);
                }
                $this->identifier .= $preference . ' ';
            }
            $this->identifier = rtrim($this->identifier);
        }
        return $this->identifier;
    }

    public function get_preference($level) {
        if (empty($this->ranking[$level])) {
            return [];
        }
        return (array) $this->ranking[$level];
    }

    public function get_last_preference() {
        return $this->get_preference($this->lastusedlevel);
    }

    public function get_next_preference() {
        return $this->get_preference($this->lastusedlevel + 1);
    }


    public function get_next_preference_worth() {
        $level = $this->lastusedlevel ? : 0;
        if (empty($this->ranking[$level + 1])) {
            return 0;
        }
        $vote = $this->ranking[$level + 1];
        return (1 / count($vote)) * $this->value;
    }

    public function get_value() {
        return $this->value;
    }

    public function add_value($amount) {
        $this->value += $amount;
    }

    public function set_last_used_level($level, $increment = false) {
        if ($increment) {
            $level += $this->lastusedlevel;
        }
        $this->lastusedlevel = $level;
    }

    public function is_exhausted() {
        return $this->lastusedlevel >= count($this->ranking);
    }

    public function get_data() {
        $data = new stdClass();
        $data->votecount = $this->value;
        $data->ranking = $this->ranking;
        return $data;
    }

}
