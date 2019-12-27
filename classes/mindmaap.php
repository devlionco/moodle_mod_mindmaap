<?php
/**
 * @package report
 * @subpackage mindmaap
 * @copyright 2019 ofekpoint
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html@package l GNU GPL v3 or later
 */

namespace mod_mindmaap;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

class mindmaap extends \core\persistent
{
    const TABLE = 'mindmaap';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
            'course' => array(
                'type' => PARAM_INT
            ),
            'name' => array(
                'type' => PARAM_TEXT
            ),
            'type' => array(
                'type' => PARAM_TEXT
            ),
            'intro' => array(
                'type' => PARAM_TEXT
            ),
            'introformat' => array(
                'type' => PARAM_INT
            )
        );
    }
}