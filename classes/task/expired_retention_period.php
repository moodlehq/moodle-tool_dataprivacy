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
 * Scheduled task to delete context instances once their retention period expired.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\task;

use coding_exception;
use core\task\scheduled_task;
use tool_dataprivacy\api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/dataprivacy/lib.php');

/**
 * Scheduled task to delete context instances once their retention period expired.
 *
 * @package     tool_dataprivacy
 * @copyright   2018 David Monllao
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class expired_retention_period extends scheduled_task {

    /**
     * Number of deleted contexts per task run.
     */
    const DELETE_LIMIT = 200;

    /**
     * Returns the task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('expiredretentionperiodtask', 'tool_dataprivacy');
    }

    /**
     * Run the task to delete context instances based on their retention periods.
     *
     */
    public function execute() {

        $contexts = api::get_expired_course_context_instances();

        $privacymanager = new \core_privacy\manager();

        $numprocessed = 0;
        foreach ($contexts as $context) {
            if (!$context) {
                // The recordset_walk callback returns false for not expired contexts.
                continue;
            }

            mtrace('Deleting context ' . $context->id . ' - ' .
                shorten_text($context->get_context_name(true, true)));

            $privacymanager->delete_data_for_all_users_in_context($context);

            $numprocessed += 1;

            if ($numprocessed == self::DELETE_LIMIT) {
                // Close the recordset.
                $contexts->close();
                break;
            }
        }
    }
}
