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
 * Access records model
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Access records model class
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_records {
    /** @var array records */
    private $records;

    /**
     * Find records for the specified term.
     *
     * @param term $term term of analyze
     * @param string $fields fields to extract
     * @throws dml_exception
     */
    public function find_access_records($term, $fields) {
        global $DB;

        $table = 'logstore_standard_log';
        $conditions = '';

        if ($term->get_start() && $term->get_end()) {
            $conditions .= "timecreated >= '" . $term->get_start()->getTimestamp() .
                    "' AND timecreated < '" . $term->get_end()->getTimestamp() . "' AND ";
        } elseif ($term->get_start()) {
            $conditions .= "timecreated >= '" . $term->get_start()->getTimestamp() . "' AND ";
        } elseif ($term->get_end()) {
            $conditions .= "timecreated < '" . $term->get_end()->getTimestamp() . "' AND ";
        }
        $conditions .= "action = 'viewed";

        $this->records = $DB->get_records_select($table, $conditions, [], '', $fields);
    }

    /**
     * @return array records
     */
    public function get_records() {
        return $this->records;
    }
}
