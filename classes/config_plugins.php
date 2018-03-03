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
 * config_plugins model
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * config_plugins model class
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_plugins {
    /** @var stdClass plugin's config */
    private $config;

    /**
     * config_plugins constructor.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct() {
        $this->config = get_config('report_alirostats');

        if (!isset($this->config->termstart)) {
            $this->create_config();
        }

        if (is_string($this->config->displaycourses) || is_string($this->config->coursecolours)) {
            global $DB;
            $courses = $DB->get_records('course', array(), 'id', 'id');

            foreach (array('displaycourses', 'coursecolours') as $item) {
                if (is_string($this->config->$item)) {
                    $temp = explode(',', $this->config->$item);
                    unset($this->config->$item);
                    $i = 0;
                    foreach ($courses as $course) {
                        $this->config->$item[$course->id] = $temp[$i++];
                    }
                }
            }
        }
    }

    /**
     * Initialize config object
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_config() {
        global $DB;
        $courses = $DB->get_records('course', array(), 'id', 'id');

        $config = new stdClass();
        $date = new DateTime('-6 days');
        $config->termstart = $date->format('Y/m/d');
        $config->termend = $date->modify('+6 days')->format('Y/m/d');
        $config->rangemax = '';
        $config->rangemin = '';
        $config->bydatetitle = get_string('accessstatsbydate', 'report_alirostats');
        $config->bydaytitle = get_string('accessstatsbyday', 'report_alirostats');
        $config->byhourtitle = get_string('accessstatsbyhour', 'report_alirostats');
        $config->displaycourses = $this->create_initial_display_settings($courses);
        $config->coursecolours = $this->create_initial_colour_set($courses);
        $config->displaytotal = '0';
        $config->totalcolour = '#000000';

        $keys = array_keys(get_object_vars($config));
        foreach ($keys as $key) {
            if (!is_array($config->$key)) {
                set_config($key, $config->$key, 'report_alirostats');
            } else {
                set_config($key, implode(',', $config->$key), 'report_alirostats');
            }
        }

        $this->config = $config;
    }

    /**
     * Up to 10 courses should be displayed
     *
     * @param array $courses moodle courses
     * @return array display settings
     */
    private function create_initial_display_settings($courses) {
        $displaycourses = array();
        $count = 0;
        foreach ($courses as $course) {
            $displaycourses[$course->id] = ($count++ < 10);
        }
        return $displaycourses;
    }

    /**
     * Create initial colour set.
     *
     * Divide 255 by number of courses, and substitute values obtained by multiplying the quotient by ($i + 1) into the array.
     * Then, shuffle the array and format each element to hexadecimal.
     * Incidentally, keys of colour set are each course's id.
     *
     * @param array $courses moodle courses
     * @return array colour set
     */
    private function create_initial_colour_set($courses) {
        $courses_number = count($courses);
        $interval = 255 / ($courses_number + 1);
        $array = array();
        $ids = array();
        $colours = array();
        for ($i = 0; $i < $courses_number; $i++) {
            foreach (array('red', 'green', 'blue') as $rgb) {
                $array[$rgb][$i] = $interval * ($i + 1);
            }
            $array[$i] = '';
        }
        foreach (array('red', 'green', 'blue') as $rgb) {
            shuffle($array[$rgb]);
            for ($i = 0; $i < $courses_number; $i++) {
                $array[$i] .= sprintf('%02X', $array[$rgb][$i]);
            }
        }
        foreach ($courses as $course) {
            $ids[] = $course->id;
        }
        for ($i = 0; $i < $courses_number; $i++) {
            $colours[$ids[$i]] = '#' . $array[$i];
        }
        return $colours;
    }

    /**
     * Create random colour code (hexadecimal triplet)
     *
     * @return string Colour code (hexadecimal triplet)
     */
    public function create_random_colour_code() {
        $colour = array();
        for ($i = 0; $i < 3; $i++) {
            $colour[] = sprintf("%02X", rand(0, 255));
        }
        return '#' . implode('', $colour);
    }

    /**
     * Update config (also update config_plugins table)
     *
     * During update, if the type of setting does not match, continue without updating.
     *
     * @param stdClass $config
     */
    public function update_config($config) {
        $keys = array_keys(get_object_vars($config));
        foreach ($keys as $key) {
            if (gettype($config->$key) !== gettype($this->config->$key)) {
                return;
            }
            if ($config->$key !== $this->config->$key) {
                $this->config->$key = $config->$key;
                if (!is_array($config->$key)) {
                    set_config($key, $config->$key, 'report_alirostats');
                } else {
                    set_config($key, implode(',', $config->$key), 'report_alirostats');
                }
            }
        }
    }

    /**
     * Receive post values.
     *
     * Use optional_param() to post values.  Then format values and return them.
     *
     * @return stdClass plugin's config
     * @throws coding_exception
     * @throws dml_exception
     */
    public function receive_settings() {
        global $DB;
        $courses = $DB->get_records('course', array(), 'id', 'id');

        $config = new stdClass();
        $config->termstart      = optional_param('termstart', $this->config->termstart, PARAM_TEXT);
        $config->termend        = optional_param('termend', $this->config->termend, PARAM_TEXT);
        $config->rangemax       = optional_param('rangemax', $this->config->rangemax, PARAM_TEXT);
        $config->rangemin       = optional_param('rangemin', $this->config->rangemin, PARAM_TEXT);
        $config->bydatetitle    = optional_param('bydatetitle', $this->config->bydatetitle, PARAM_TEXT);
        $config->bydaytitle     = optional_param('bydaytitle', $this->config->bydaytitle, PARAM_TEXT);
        $config->byhourtitle    = optional_param('byhourtitle', $this->config->byhourtitle, PARAM_TEXT);
        $config->displaycourses = $this->format_displaycourses($courses);
        $config->coursecolours  = $this->format_coursecolours($courses);
        $config->displaytotal   = optional_param('displaytotal', '', PARAM_TEXT) === '1' ? '1' : '0';
        $config->totalcolour    = optional_param('totalcolour', $this->config->totalcolour, PARAM_TEXT);
        if (empty($config->totalcolour)) {
            $config->totalcolour = $this->create_random_colour_code();
        }

        return $config;
    }

    /**
     * Format optional param 'displaycourses'
     *
     * Change the key to the course's id.
     *
     * @param $courses moodle's courses
     * @return array Formatted optional param 'displaycourses'
     * @throws coding_exception
     */
    private function format_displaycourses($courses) {
        $keys = optional_param_array('displaycourses', array(), PARAM_TEXT);

        if (empty($keys)) {
            return $this->config->displaycourses;
        }

        $displaycourses = array();
        foreach ($courses as $course) {
            $displaycourses[$course->id] = '0';
        }

        foreach ($keys as $key) {
            $displaycourses[$key] = '1';
        }

        return $displaycourses;
    }

    /**
     * Format optional param 'coursecolours'
     *
     * If one course's colour is empty string, create random colour code and set it.
     *
     * @param array $courses moodle courses
     * @return array formatted optional param 'coursecolours'
     * @throws coding_exception
     */
    private function format_coursecolours($courses) {
        $coursecolours = array();

        foreach ($courses as $course) {
            $coursecolours[$course->id] = optional_param('coursecolour'.$course->id, $this->config->coursecolours[$course->id], PARAM_TEXT);
            if ($coursecolours[$course->id] === '') {
                $coursecolours[$course->id] = $this->create_random_colour_code();
            }
        }

        return $coursecolours;
    }

    /**
     * @return stdClass plugin's config
     */
    public function get_config() {
        return $this->config;
    }
}
