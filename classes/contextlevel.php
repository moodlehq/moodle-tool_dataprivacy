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
 * Class for loading/storing context level data from the DB.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;
defined('MOODLE_INTERNAL') || die();

/**
 * Class for loading/storing context level data from the DB.
 *
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contextlevel extends \core\persistent {

    /**
     * Database table.
     */
    const TABLE = 'dataprivacy_contextlevel';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'contextlevel' => array(
                'type' => PARAM_INT,
                'description' => 'The context level.',
            ),
            'purposeid' => array(
                'type' => PARAM_INT,
                'description' => 'The purpose id.',
            ),
            'categoryid' => array(
                'type' => PARAM_INT,
                'description' => 'The category id.',
            ),
            'applyallinstances' => array(
                'type' => PARAM_INT,
                'description' => 'Purpose and category applied to all instances of this context',
            ),
        );
    }

    /**
     * Returns an instance by contextlevel.
     *
     * @param mixed $contextlevel
     * @param mixed $exception
     * @return null
     */
    public static function get_record_by_contextlevel($contextlevel, $exception = true) {
        global $DB;

        if (!$record = $DB->get_record(self::TABLE, array('contextlevel' => $contextlevel))) {
            if (!$exception) {
                return false;
            } else {
                throw new \dml_missing_record_exception(self::TABLE);
            }
        }

        return new static(0, $record);
    }
}
