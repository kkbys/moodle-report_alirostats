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
 * Access records  controller
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/term.php');
require_once(dirname(__FILE__) . '/access_records.php');

/**
 * access_records controller class
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_records_controller {
    /** @var access_records moodle access records */
    private $access_records;

    /** @var stdClass plugin's config */
    private $config;

    /**
     * access_records_controller constructor.
     *
     * @param stdClass $config plugin's config
     */
    public function __construct($config) {
        $this->access_records = new access_records();
        $this->config = $config;
    }

    /**
     * Execution of various processes.
     *
     * @throws dml_exception
     */
    public function run() {
        $term = new term($this->config->termstart, $this->config->termend);
        $this->access_records->find_access_records($term, 'id, courseid, timecreated');
    }

    /**
     * @return access_records moodle access records
     */
    public function get_access_records() {
        return $this->access_records;
    }
}