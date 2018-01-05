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
 * Stats data by hour model
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/stats_data.php');

/**
 * Stats data by hour model class
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stats_data_byhour extends stats_data {
    /**
     * Analyze Access Records.
     *
     * If the record's courseid is the course's id doesn't currently exist, this method will skip to analyze records.
     */
    public function analyze_access_records() {
        foreach (parent::get_records() as $record) {
            if (!in_array($record->courseid, array_keys(parent::get_courses()))) {
                continue;
            }
            $this->access_numbers[$record->courseid][date('G', $record->timecreated)]++;
        }
        $this->store_access_numbers('hour');
    }

    /**
     * Initialize access numbers.
     */
    public function init_access_numbers() {
        foreach (parent::get_courses() as $course) {
            $this->access_numbers[$course->id] = array_fill(0, 24, 0);
        }
    }
}
