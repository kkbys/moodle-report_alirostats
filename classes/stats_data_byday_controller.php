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
 * For control stats data by day
 *
 * @package    report_alirostats
 * @category   report
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/stats_data_controller.php');
require_once(dirname(__FILE__) . '/stats_data_byday.php');

/**
 * Class for control stats_data_byday
 *
 * @package    report_alirostats
 * @category   report
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stats_data_byday_controller extends stats_data_controller {
    /**
     * stats_data_byday_controller constructor.
     *
     * @param stdClass $config plugin's config
     * @param array $records moodle access records
     */
    public function __construct($config, $records) {
        $this->stats_data = new stats_data_byday($config, $records);
    }
}
