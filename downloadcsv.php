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
 * Align and output csv for download
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(dirname(__FILE__) . '/classes/term.php');

if (empty($id = optional_param('id', 0, PARAM_INT))) {
    $site = get_site();
    $id = $site->id;
}

$course = null;
if ($id) {
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
} else {
    require_login();
    $context = context_system::instance();
    $PAGE->set_context($context);
}
require_capability('report/alirostats:view', $context);

$mode = optional_param('mode', '', PARAM_TEXT);
if (!in_array($mode, array('date', 'day', 'hour'))) {
    exit(get_string('invalidmode', 'report_alirostats'));
}

if (empty($courses)) {
    $courses = $DB->get_records('course', array(), 'id', 'id, shortname');
}

$config = get_config('report_alirostats');

// Format access numbers data
$access_numbers = format_access_numbers_data($config, $mode);

$filename = 'access_numbers_by_' . $mode . '.csv';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $filename);

$csv = pack('C*', 0xEF, 0xBB, 0xBF);
$heading = [get_string('xlabel' . $mode, 'report_alirostats')];
foreach ($courses as $course) {
    $heading[] = $course->shortname;
}
$csv .= implode(',', $heading) . "\r\n";

$indexnumber = count(current($access_numbers));
$times = array_keys($access_numbers[current($courses)->id]);

switch ($mode) {
    case 'date':
        $term = new term($config->termstart, $config->termend);
        $date = $term->get_start();
        for ($i = 0; $i < $indexnumber; $i++) {
            write_line_csv($csv, $date->format('Y/m/d'), $access_numbers, $courses, $times[$i]);
            $date->modify('+1 day');
        }
        break;
    case 'day':
        $daynames = explode(',', get_string('daynames', 'report_alirostats'));
        for ($i = 0; $i < $indexnumber; $i++) {
            write_line_csv($csv, $daynames[$i], $access_numbers, $courses, $times[$i]);
        }
        break;
    case 'hour':
        for ($i = 0; $i < $indexnumber; $i++) {
            write_line_csv($csv, $times[$i], $access_numbers, $courses, $times[$i]);
        }
        break;
}

echo $csv;

/**
 * Get access numbers before format data
 *
 * @param stdClass $config plugin's config
 * @param string $mode 'date', 'day', 'hour'
 * @return array
 */
function format_access_numbers_data($config, $mode) {
    $access_numbers = 'accessnumbersby' . $mode;
    $access_numbers = explode('|', $config->$access_numbers);

    $keys = array();
    foreach ($access_numbers as &$access_number) {
        $temp = explode(':', $access_number);
        $keys[] = $temp[0];
        $access_number = $temp[1];
        unset($temp);
    }
    $i = 0;
    foreach ($keys as $key) {
        $temp[$key] = explode(',', $access_numbers[$i++]);
    }

    return $temp;
}

/**
 * @param $csv csv format data
 * @param $index left column index
 * @param $access_numbers moodle access numbers
 * @param $courses moodle courses
 * @param $time current column number
 */
function write_line_csv(&$csv, $index, $access_numbers, $courses, $time) {
    $line = [$index];
    foreach ($courses as $course) {
        $line[] = $access_numbers[$course->id][$time];
    }
    $csv .= implode(',', $line) . "\r\n";
}
