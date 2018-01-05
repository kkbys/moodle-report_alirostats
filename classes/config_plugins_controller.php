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
 * config_plugins controller
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/config_plugins.php');

/**
 * Class for control config_plugins
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_plugins_controller {
    /** @var config_plugins $config_plugins */
    private $config_plugins;

    /**
     * config_plugins_controller constructor.
     */
    public function __construct() {
        $this->config_plugins = new config_plugins();
    }

    /**
     * Execution of various processes.
     *
     * Receive post values as settings, and update config and table 'config_plugins'
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function run() {
        $config = $this->config_plugins->receive_settings();
        $this->config_plugins->update_config($config);
    }

    /**
     * @return config_plugins plugin's config
     */
    public function get_config_plugins() {
        return $this->config_plugins;
    }
}
