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
 * Event observers used in mindmaap.
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Include the mindmaap library to make use of the mindmaap_instance_created function.
require_once($CFG->dirroot . '/mod/mindmaap/lib.php');

/**
 * Event observer for mod_mindmaap.
 */
class mod_mindmaap_observer {

    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $CFG;

        if ($event->other['modulename'] === 'mindmaap') {
            $mindmaap = $event->get_record_snapshot('mindmaap', $event->other['instanceid']);
            mindmaap_instance_created($event->get_context(), $mindmaap);
        }
    }

    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_viewed(\core\event\course_module_viewed $event) {
        global $CFG;

        if ($event->other['modulename'] === 'mindmaap') {
            $mindmaap = $event->get_record_snapshot('mindmaap', $event->other['instanceid']);
            mindmaap_instance_created($event->get_context(), $mindmaap);
        }
    }
}
