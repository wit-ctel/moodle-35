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

class candidate {
    const STATE_ELECTED = 2;
    const STATE_HOPEFUL = 1;
    const STATE_WITHDRAWN = 0;
    const STATE_DEFEATED = -1;

    public $name;

    protected $id;
    protected $log = [];
    protected $state;
    protected $surplus = 0;
    protected $votes = 0;

    public $messages;

    /**
     * @param int|string $cmid optional
     * @param int $userid optional
     * @param object $election optional
     * @param object $cm optional
     * @param object $course optional
     */
    public function __construct($name, $id = null) {
        $this->name = $name;
        $this->id = $id;
        $this->state = self::STATE_HOPEFUL;
    }

    public function get_votes() {
        return $this->votes;
    }


    public function add_votes($votes) {
        $this->votes += $votes;
    }


    public function transfer_votes($amount, candidate $to, $precision = 5) {
        if (round($this->votes, $precision) < round($amount, $precision)) {
            throw new Exception('Not enough votes to transfer');
        }
        $this->votes -= $amount;
        $to->add_votes($amount);
        $displayprecision = $precision >= 2 ? 2 : $precision;
        $this->log(sprintf('Transferred %s votes to %s', number_format($amount, $displayprecision), $to->get_name()));
        $to->log(sprintf('Received %s votes from %s', number_format($amount, $displayprecision), $this->name));
    }

    public function log($message, $type = 'alert alert-warning') {
        $this->log[] = array($message, $type);
    }

    public function get_log($reset = false) {
        $log = $this->log;
        if ($reset) {
            $this->log = [];
        }
        return $log;
    }

    public function get_surplus() {
        return $this->surplus;
    }

    public function set_surplus($amount, $increment = false) {
        $this->surplus = $increment ? $this->surplus + $amount : $amount;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_state($formatted = false) {
        return $formatted ? $this->get_formatted_state() : $this->state;
    }

    public function set_state($state) {
        $this->state = $state;
    }

    protected function get_formatted_state() {
        switch ($this->state) {
            case self::STATE_DEFEATED:
            return 'Defeated';
            case self::STATE_WITHDRAWN:
            return 'Withdrawn';
            case self::STATE_ELECTED:
            return 'Elected';
            case self::STATE_HOPEFUL:
            return 'Hopeful';
        }
        return 'Unknown';
    }
}