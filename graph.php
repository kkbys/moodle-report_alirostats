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
 * output access numbers as graph
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/graphlib.php');
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

foreach (array('displaycourses', 'coursecolours') as $item) {
    if (is_string($config->$item)) {
        $temp = explode(',', $config->$item);
        unset($config->$item);
        $i = 0;
        foreach ($courses as $course) {
            $config->$item[$course->id] = $temp[$i++];
        }
    }
}

// Format access numbers data
$access_numbers = format_access_numbers_data($config, $mode);

$graph = new graph(1600, 1000);

if ($config->displaytotal === '1') {
    calc_total_access_numbers($access_numbers);
    $graph->y_order[] = PHP_INT_MAX - 1;
    $graph->y_format[PHP_INT_MAX - 1] = array('colour' => $config->totalcolour,
            'line' => 'brush', 'legend' => get_string('total', 'report_alirostats'));
}

align_access_numbers_by_range($access_numbers, $config->rangemax, $config->rangemin);

switch ($mode) {
    case 'date':
        $graph->parameter['title'] = $config->bydatetitle;
        $term = new term($config->termstart, $config->termend);
        $date = $term->get_start();
        $days = $term->get_start()->diff($term->get_end())->days;
        if ($days <= 31) {
            for ($i = 0; $i < $days; $i++) {
                $graph->x_data[] = $date->format('Y/m/d');
                $date->modify('+1 day');
            }
        } else {
            $graph->x_data = array_fill(0, $days, '');
            // 最初と最後
            $graph->x_data[0] = $term->get_start()->format('Y/m/d');
            $graph->x_data[$days - 1] = $term->get_end()->modify('-1 day')->format('Y/m/d');
            // 途中
            if ($days % 2 === 0) {
                $day = round(($days - 2) / 9);
                for ($i = 1; $i < 9; $i++) {
                    $date->modify('+' . $day . ' days');
                    $graph->x_data[$day * $i] = $date->format('Y/m/d');
                }
            } else {
                $day = round(($days - 2) / 10);
                for ($i = 1; $i < 10; $i++) {
                    $date->modify('+' . $day . ' days');
                    $graph->x_data[$day * $i] = $date->format('Y/m/d');
                }
            }
        }
        break;
    case 'day':
        $graph->parameter['title'] = $config->bydaytitle;
        $graph->x_data = explode(',', get_string('daynames', 'report_alirostats'));
        break;
    case 'hour':
        $graph->parameter['title'] = $config->byhourtitle;
        $graph->x_data = range(0, 23);
        break;
}

$graph->y_data = $access_numbers;

foreach ($courses as $course) {
    if ($config->displaycourses[$course->id] === '1') {
        $graph->y_order[] = $course->id;
        $graph->y_format[$course->id] = array('colour' => $config->coursecolours[$course->id],
                'line' => 'brush', 'legend' => $course->shortname);
    }
}


$graph->parameter['axis_size'] = 20;
$graph->parameter['x_axis_angle'] = $mode == 'date' ? 30 : 0;
$graph->parameter['x_label'] = get_string('xlabel' . $mode, 'report_alirostats');
$graph->parameter['x_label_angle'] = 0;
$graph->parameter['title_size'] = 36;
$graph->parameter['label_size'] = 20;
$graph->parameter['legend']     = 'top-left';
$graph->parameter['legend_size'] = 20;
$graph->parameter['inner_background'] = 'none';
$graph->parameter['outer_background'] = 'none';
$graph->parameter['inner_border'] = 'black';
$graph->parameter['inner_border_type'] = 'axis';
$graph->parameter['outer_padding'] = 60;
$graph->parameter['shadow'] = 'none';
$graph->parameter['y_label_left'] = get_string('ylabel', 'report_alirostats');
$graph->parameter['x_grid'] = 'none';
$graph->parameter['y_grid'] = 'none';

if ($config->rangemin !== '') {
    $graph->parameter['y_min_left'] = $config->rangemin;
}

// Register colour from colour code
foreach ($config->coursecolours as $coursecolour) {
    $colour = sscanf($coursecolour, '#%2X%2X%2X');
    $graph->colour[$coursecolour] = imagecolorallocate($graph->image, $colour[0], $colour[1], $colour[2]);
}
$colour = sscanf($config->totalcolour, '#%2X%2X%2X');
$graph->colour[$config->totalcolour] = imagecolorallocate($graph->image, $colour[0], $colour[1], $colour[2]);

$graph->draw_stack();

/**
 * Get access numbers before format data
 *
 * @param stdClass $config plugin's config
 * @param string $mode 'date', 'day', 'hour'
 * @return array access numbers
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
 * Align access numbers
 *
 * @param array $access_numbers moodle access numbers
 * @param int $rangemax graph y range max value
 * @param int $rangemin graph y range min value
 */
function align_access_numbers_by_range(&$access_numbers, $rangemax, $rangemin) {
    if ($rangemax === '' && $rangemin === '') {
        return;
    }
    foreach ($access_numbers as &$access_number) {
        foreach ($access_number as &$number) {
            if ($rangemax !== '' && $number > $rangemax) {
                $number = $rangemax;
            }
            if ($rangemin !== '' && $number < $rangemin) {
                $number = $rangemin;
            }
        }
    }
}

/**
 * Calc total access numbers
 *
 * @param array $access_numbers moodle access numbers
 */
function calc_total_access_numbers(&$access_numbers) {
    $temp = array_fill(0, count(current($access_numbers)), 0);
    foreach ($access_numbers as $access_number) {
        for ($i = 0; $i < count($access_number); $i++) {
            $temp[$i] += $access_number[$i];
        }
    }
    $access_numbers[PHP_INT_MAX - 1] = $temp;
}
