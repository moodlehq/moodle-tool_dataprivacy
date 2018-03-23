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
 * Expired contexts manager for CONTEXT_USER.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;

use tool_dataprivacy\purpose;
use tool_dataprivacy\context_instance;

defined('MOODLE_INTERNAL') || die();

/**
 * Expired contexts manager for CONTEXT_USER.
 *
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class expired_user_contexts extends \tool_dataprivacy\expired_contexts {

    /**
     * Returns a recordset with user context instances that are possibly expired (to be confirmed by get_recordset_callback).
     *
     * @return \moodle_recordset
     */
    protected function get_contexts_recordset() {
        global $DB;

        // Including context info + last login timestamp + purposeid (this last one only if defined).
        $fields = 'ctx.id AS id, u.lastlogin AS comparisontime, dpctx.purposeid AS purposeid, ' .
            \context_helper::get_preload_record_columns_sql('ctx');

        $purpose = api::get_effective_contextlevel_purpose(CONTEXT_USER);

        // Calculate what is considered expired according to the context level effective purpose (= now + retention period).
        $expired = new \DateTime();
        $retention = new \DateInterval($purpose->get('retentionperiod'));
        $expired->sub($retention);

        $sql = "SELECT $fields FROM {context} ctx
                  JOIN {user} u ON ctx.contextlevel = ? AND ctx.instanceid = u.id
                  LEFT JOIN {tool_dataprivacy_ctxinstance} dpctx ON dpctx.contextid = ctx.id
                 WHERE u.lastaccess <= ? AND u.lastaccess > 0
                ORDER BY ctx.path, ctx.contextlevel ASC";
        return $DB->get_recordset_sql($sql, [CONTEXT_USER, $expired->getTimestamp()]);
    }

    /**
     * Returns the callback to execute for each get_contexts_recordset returned record.
     *
     * @return \callable
     */
    protected function recordset_callback() {
        return [$this, 'check_user_courses'];
    }

    /**
     * Discard any user with ongoing courses or with courses without end date.
     *
     * @return \context|false
     */
    public function check_user_courses($record) {

        \context_helper::preload_from_record($record);

        // No strict checking as the context may already be deleted (e.g. we just deleted a course,
        // module contexts below it will not exist).
        $context = \context::instance_by_id($record->id, false);
        if (!$context) {
            return false;
        }

        $courses = enrol_get_users_courses($context->instanceid, false, ['enddate']);

        foreach ($courses as $course) {
            if (!$course->enddate) {
                // We can not know it what is going on here, so we prefer to be conservative.
                return false;
            }

            if ($course->enddate > time()) {
                // Future or ongoing course.
                return false;
            }
        }

        return $context;
    }
}
