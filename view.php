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
 * AliroStats view
 *
 * @package    report_alirostats
 * @copyright  2017 Kota Kobayashi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

?>

<div class="container">
    <!-- Header -->
    <div class="alirostats-header">
        <h3><?php echo get_string('pluginname', 'report_alirostats');?></h3>
        <hr>
    </div>
    <!-- Statistics -->
    <div class="alirostats-statistics">
        <!-- by Date -->
        <p>
            <h4><?php echo get_string('accessstatsbydate', 'report_alirostats'); ?></h4>
            <img src="./graph.php?mode=date"><br>
            <a href="downloadcsv.php?mode=date"><?php echo get_string('downloadcsv', 'report_alirostats'); ?></a>
            <hr>
        </p>
        <!-- by Day -->
        <p>
            <h4><?php echo get_string('accessstatsbyday', 'report_alirostats'); ?></h4>
            <img src="./graph.php?mode=day"><br>
            <a href="downloadcsv.php?mode=day"><?php echo get_string('downloadcsv', 'report_alirostats'); ?></a>
            <hr>
        </p>
        <!-- by Hour -->
        <p>
            <h4><?php echo get_string('accessstatsbyhour', 'report_alirostats'); ?></h4>
            <img src="./graph.php?mode=hour"><br>
            <a href="downloadcsv.php?mode=hour"><?php echo get_string('downloadcsv', 'report_alirostats'); ?></a>
            <hr>
        </p>
    </div>
    <!-- Settings -->
    <div class="alirostats-settings">
        <form method="post" action="index.php?id=<?php echo optional_param('id', 0, PARAM_TEXT); ?>">
            <h3><?php echo get_string('settings', 'report_alirostats'); ?></h3>
            <!-- Term -->
            <div class="term-settings">
                <h4><?php echo get_string('term', 'report_alirostats'); ?></h4>
                <!-- Start Date-->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="termstart"><?php echo get_string('termstart', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="termstart" name="termstart" value="<?php echo $config->termstart; ?>">
                            </div>
                        </div>
                    </div>
                </p>
                <!-- End Date-->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="termsend"><?php echo get_string('termend', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="termend" name="termend" value="<?php echo $config->termend; ?>">
                            </div>
                        </div>
                    </div>
                </p>
            </div>
            <!-- Display Courses -->
            <div class="display-settings">
                <h4><?php echo get_string('displaycourses', 'report_alirostats'); ?></h4>
                <?php
                foreach ($courses as $course) {
                    // label
                    $label = html_writer::label($course->shortname, $course->shortname) .
                             html_writer::span($course->fullname, 'form-shortname d-block small text-muted');
                    $label = html_writer::div($label, 'form-label col-sm-3 text-sm-right');
                    // item
                    $input = html_writer::checkbox('displaycourses[]', $course->id, $config->displaycourses[$course->id],
                            get_string('display', 'report_alirostats'), array('id' => $course->shortname));
                    $input .= html_writer::empty_tag('input', array('type' => 'text', 'class' => 'form-control',
                            'name' => 'coursecolour'.$course->id, 'value' => $config->coursecolours[$course->id]));
                    $input = html_writer::div($input, 'form-text');
                    $input = html_writer::div($input, 'form-setting col-sm-9');
                    // link (label & input(checkbox&text))
                    echo '<p>' . html_writer::div($label . $input, 'form-item row') . '</p>';
                }
                $label = html_writer::label(get_string('total', 'report_alirostats'), get_string('total', 'report_alirostats'));
                $label = html_writer::div($label, 'form-label col-sm-3 text-sm-right');
                $input = html_writer::checkbox('displaytotal', '1', $config->displaytotal,
                        get_string('display', 'report_alirostats'), array('id' => 'displaytotal'));
                $input .= html_writer::empty_tag('input', array('type' => 'text', 'class' => 'form-control',
                        'name' => 'totalcolour', 'value' => $config->totalcolour));
                $input = html_writer::div($input, 'form-text');
                $input = html_writer::div($input, 'form-setting col-sm-9');
                echo '<p>' . html_writer::div($label . $input, 'form-item row') . '</p>';
                ?>
            </div>
            <!-- Graph Title -->
            <div class="title-settings">
                <h4><?php echo get_string('graphtitle', 'report_alirostats'); ?></h4>
                <!-- by Date Title-->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="bydatetitle"><?php echo get_string('accessstatsbydate', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="bydatetitle" name="bydatetitle" value="<?php echo $config->bydatetitle; ?>">
                            </div>
                        </div>
                    </div>
                </p>
                <!-- by Day Title-->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="bydaytitle"><?php echo get_string('accessstatsbyday', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="bydaytitle" name="bydaytitle" value="<?php echo $config->bydaytitle; ?>">
                            </div>
                        </div>
                    </div>
                </p>
                <!-- by Hour Title-->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="byhourtitle"><?php echo get_string('accessstatsbyhour', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="byhourtitle" name="byhourtitle" value="<?php echo $config->byhourtitle; ?>">
                            </div>
                        </div>
                    </div>
                </p>
            </div>
            <!-- Range -->
            <div class="range-settings">
                <h4><?php echo get_string('range', 'report_alirostats'); ?></h4>
                <!-- Range Max. -->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="rangemax"><?php echo get_string('rangemax', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="rangemax" name="rangemax" value="<?php echo $config->rangemax; ?>">
                            </div>
                        </div>
                    </div>
                </p>
                <!-- Range Min. -->
                <p>
                    <div class="form-item row">
                        <div class="form-label col-sm-3 text-sm-right">
                            <label for="rangemin"><?php echo get_string('rangemin', 'report_alirostats'); ?></label>
                        </div>
                        <div class="form-setting col-sm-9">
                            <div class="form-text">
                                <input type="text" class="form-control" id="rangemin" name="rangemin" value="<?php echo $config->rangemin; ?>">
                            </div>
                        </div>
                    </div>
                </p>
            </div>
            <!-- other -->
            <div class="row">
                <div class="offset-sm-3 col-sm-3">
                    <button type="submit" class="btn btn-primary"><?php echo get_string('ok', 'report_alirostats'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
