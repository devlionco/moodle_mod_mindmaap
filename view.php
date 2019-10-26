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
 * Prints an instance of mod_mindmaap.
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/moodle.php');

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// Module instance id.
$m = optional_param('m', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('mindmaap', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('mindmaap', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($m) {
    $moduleinstance = $DB->get_record('mindmaap', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('mindmaap', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', mod_mindmaap));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_mindmaap\event\course_module_viewed::create(array(
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('mindmaap', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/mindmaap/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$token = get_config('mod_mindmaap', 'token');
$token = get_config('mod_mindmaap', 'token');
$url = get_config('mod_mindmaap', 'url');
$mindmaap = new mindmaap($token, $url);
$data = [
        'email' => $USER->email,
        'first_name' => $USER->firstname,
        'last_name' => $USER->lastname,
        'additional_data' => [$moduleinstance->activityid],
];

// Create mindmaap.
$user = $mindmaap->registeruser($data['email'], $data['first_name'], $data['last_name'], $data['additional_data']);

$url = $user['url'] . "&lang=" . current_language();
$sessionurl = $mindmaap->getsessionurl($user['url_param'], $user['mindmap']['token']);

$o = '';
$session = '<iframe id="mindmapsessioniframe" src="' . $sessionurl . '" style="width:0px;border:0px;height: 0px;"></iframe>';
switch ($moduleinstance->type) {
    case 'iframe':
        $o .= '<iframe id="mindmapiframe" src="" ></iframe>>
               <script> window.onload = function(){document.getElementById(\'mindmapiframe\').src="' . $url . '"}; </script>';
        break;
    case 'popup':
        $PAGE->requires->js_call_amd('mod_mindmaap/popup', 'init', [$url, format_string($moduleinstance->name)]);
        $o .= \html_writer::link("#", get_string('openpopup', 'mod_mindmaap'),
                ["id" => "mindmaapopen", "class" => "btn btn-primary mindmaapbutton", "rel" => "noopener noreferrer"]);
        break;
    case 'window':
        $o .= \html_writer::link($url, get_string('newwindow', 'mod_mindmaap'),
                ["id" => "mindmaapopen", "class" => "btn btn-primary mindmaapbutton", "target" => "_blank",
                        "rel" => "noopener noreferrer"]);
        break;
    case 'link':
        $o .= '<script> window.setInterval(function(){ window.location.href="' . $url . '"},2000); </script>';
        break;
    default:
        break;
}
$PAGE->requires->css('/mod/mindmaap/styles.css');
echo $OUTPUT->header();
echo \html_writer::start_div('col-12');
if (trim(strip_tags($moduleinstance->intro))) {
    echo $OUTPUT->box_start('mod_introbox container', 'mindmapintro');
    echo format_module_intro('mindmaap', $moduleinstance, $cm->id);
    echo $OUTPUT->box_end();
}
echo \html_writer::start_div('row');
echo $session . $o;
echo \html_writer::end_div();
echo \html_writer::end_div();
echo $OUTPUT->footer();
