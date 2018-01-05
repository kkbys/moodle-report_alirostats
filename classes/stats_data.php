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
 * Stats data model
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Stats data model class
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class stats_data {
    /** @var array access numbers*/
    protected $access_numbers;

    /** @var term analyze term */
    protected $term;

    /** @var stdClass config */
    private $config;

    /** @var array courses */
    private $courses;

    /** @var array records */
    private $records;

    /**
     * stats_data constructor.
     *
     * @param $config
     * @param $records
     * @throws dml_exception
     */
    public function __construct($config, $records) {
        global $DB;

        $this->term = new term($config->termstart, $config->termend);
        $this->records = $records;
        $this->courses = $DB->get_records('course', array(), 'id', 'id');
        $this->config = $config;
    }

    /**
     * Adjust to the value in the specified range
     *
     * Needs to call it for each course.
     *
     * @param $access_numbers
     * @param $rangemax
     * @param $rangemin
     */
    public function align_access_numbers_by_range() {
        if ($this->config->rangemax !== '' || $this->config->rangemin !== '') {
            foreach ($this->access_numbers as &$access_number) {
                if ($this->config->rangemax !== '' && $access_number > $this->config->rangemax) {
                    $access_number = $this->config->rangemax;
                }
                if ($this->config->rangemin !== '' && $access_number < $this->config->rangemin) {
                    $access_number = $this->config->rangemin;
                }
            }
        }
    }

    /**
     * Format and tore access numbers in config_plugins
     *
     * @param string $mode 'date', 'day', 'hour'
     */
    public function store_access_numbers($mode) {
        $access_numbers_string = '';
        foreach ($this->courses as $course) {
            $access_numbers_string .= $course->id . ':' . implode(',', $this->access_numbers[$course->id]) . '|';
        }
        set_config('accessnumbersby' . $mode, $access_numbers_string, 'report_alirostats');
    }

    /**
     * Analyze Access Records.
     *
     * If the record's courseid is the course's id doesn't currently exist, this method will skip to analyze records.
     */
    abstract public function analyze_access_records();

    /**
     * Initialize access numbers.
     */
    abstract public function init_access_numbers();

    /**
     * @return array access numbers
     */
    public function get_access_numbers() {
        return $this->access_numbers;
    }

    /**
     * @return array courses
     */
    public function get_courses() {
        return $this->courses;
    }

    /**
     * @return array records
     */
    public function get_records() {
        return $this->records;
    }
}
