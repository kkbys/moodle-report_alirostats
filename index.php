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
 * AliroStats index file
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('./classes/access_records_controller.php');
require_once('./classes/config_plugins_controller.php');
require_once('./classes/stats_data_bydate_controller.php');
require_once('./classes/stats_data_byday_controller.php');
require_once('./classes/stats_data_byhour_controller.php');
require_once($CFG->libdir.'/adminlib.php');

if (empty($id = optional_param('id', 0, PARAM_INT))) {
    $site = get_site();
    $id = $site->id;
}

$PAGE->set_url(new moodle_url($CFG->wwwroot . '/report/alirostats/index.php'), array('id' => $id));
$PAGE->set_pagelayout('report');

// Get course details.
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

if (empty($course) || ($course->id == $SITE->id)) {
    admin_externalpage_setup('alirostats', '', null, '', array('pagelayout' => 'report'));
    $PAGE->set_title($SITE->shortname .': '. get_string('pluginname', 'report_alirostats'));
} else {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_alirostats'));
    $PAGE->set_heading($course->fullname);
}
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/alirostats/style.css'));

echo $OUTPUT->header();

$cpc = new config_plugins_controller();
$cpc->run();
$config = $cpc->get_config_plugins()->get_config();
$arc = new access_records_controller($config);
$arc->run();
$records = $arc->get_access_records()->get_records();
$sdatec = new stats_data_bydate_controller($config, $records);
$sdatec->run();
$sdayc  = new stats_data_byday_controller($config, $records);
$sdayc->run();
$shourc = new stats_data_byhour_controller($config, $records);
$shourc->run();
$courses = $DB->get_records('course', array(), 'id', 'id, shortname, fullname');
require('./view.php');

echo $OUTPUT->footer();
