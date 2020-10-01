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
 * Display information about all the mod_mindmaap modules in the requested course.
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_once(__DIR__ . '/lib.php');

use \mod_mindmaap\event\course_module_instance_list_viewed;

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_course_login($course);

$coursecontext = context_course::instance($course->id);

$event = course_module_instance_list_viewed::create(array(
        'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/mindmaap/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'mod_mindmaap');
echo $OUTPUT->heading($modulenameplural);

$mindmaaps = get_all_instances_in_course('mindmaap', $course);
$strname = get_string("name");
$usesections = course_format_uses_sections($course->format);

if (empty($mindmaaps)) {
    notice(get_string('nonewmodules', 'mod_mindmaap'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname);
    $table->align = array ("center", "left");
} else {
    $table->head  = array ($strname);
}

foreach ($mindmaaps as $mindmaap) {
    if (!$mindmaap->visible) {
        $link = html_writer::link(
                new moodle_url('/mod/mindmaap/view.php', array('id' => $mindmaap->coursemodule)),
                format_string($mindmaap->name, true),
                array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
                new moodle_url('/mod/mindmaap/view.php', array('id' => $mindmaap->coursemodule)),
                format_string($mindmaap->name, true));
    }

    if ($usesections) {
        $table->data[] = array (get_section_name($course, $mindmaap->section), $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();
