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
 * Term for stats.
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Term class for stats data.
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class term {
    /** @var DateTime stats start date */
    private $start;

    /** @var DateTime stats end date */
    private $end;

    /**
     * term constructor.
     *
     * @param string $start stats start date.
     * @param string $end stats end date.
     */
    public function __construct($start, $end) {
        if ($start !== '') {
            $this->start = new DateTime($start);
        } else {
            $this->start = false;
        }
        if ($end !== '') {
            $this->end = new DateTime($end);
            $this->end->modify('+1 day');
        } else {
            $this->end = false;
        }
    }

    /**
     * @return DateTime stats start date
     */
    public function get_start() {
        return $this->start;
    }

    /**
     * @return DateTime stats end date
     */
    public function get_end() {
        return $this->end;
    }
}
