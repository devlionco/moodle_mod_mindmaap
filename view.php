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
$session = '<iframe src="' . $sessionurl . '" style="display: none;"></iframe>';
switch ($moduleinstance->type) {
    case 'iframe':
        $o .= '<iframe src="' . $url . '" align="left"></iframe>';
        break;
    case 'popup':
        $PAGE->requires->js_call_amd('mod_mindmaap/popup', 'init', [$url]);
        $PAGE->requires->css('/mod/mindmaap/styles.css');
        $o .= '<a href="#" class="btn btn-primary mindmaapbutton" id="mindmaapopen">' .
                get_string('openpopup', 'mod_mindmaap') . '</a>';
        break;
    case 'window':
        $o .= '<a href="' . $url . '" class="btn btn-primary mindmaapbutton" target="_blank" rel="noopener noreferrer">' .
                get_string('newwindow', 'mod_mindmaap') . '</a>';
        break;
    case 'link':
        $o .= '<script> setInterval(function(){ window.location.href="' . $url . '"},1000); </script>';
        break;
    default:
        break;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($moduleinstance->name), 3, 'main');
echo $session;
echo $o;
echo $OUTPUT->footer();
