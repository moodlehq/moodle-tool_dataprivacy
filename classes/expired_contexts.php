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
 * Expired contexts manager.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;

use tool_dataprivacy\api;
use tool_dataprivacy\purpose;
use tool_dataprivacy\context_instance;
use tool_dataprivacy\data_registry;

defined('MOODLE_INTERNAL') || die();

/**
 * Expired contexts manager.
 *
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class expired_contexts {

    const DELETE_LIMIT = 200;

    /**
     * Returns the list of contexts that are "Finished".
     *
     * "Finished" is a very ambiguous concept and its meaning highly depends
     * on what are we expiring.
     *
     * Recordset records should include \context_helper::get_preload_record_columns_sql and
     * a 'comparisontime' field containing the timestamp that makes this record "finished".
     *
     * A LEFT JOIN to {tool_dataprivacy_ctxinstance} should also be added so that the
     * context instance purposeid is returned if available.
     *
     * @return \moodle_recordset
     */
    abstract protected function get_contexts_recordset();

    /**
     * Returns the function that should be applied to the recordset to filter out results.
     *
     * @return \callable The callback name.
     */
    abstract protected function recordset_callback();

    /**
     * Deletes the expired contexts.
     *
     * @return int The number of deleted contexts.
     */
    public function delete() {

        if (!$this->check_requirements()) {
            return [];
        }

        $contexts = $this->get_recordset_walk();
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

        return $numprocessed;
    }

    /**
     * Check that the requirements to start deleting contexts are satisified.
     *
     * @return bool
     */
    protected function check_requirements() {
        api::check_can_manage_data_registry(\context_system::instance()->id);

        if (!data_registry::defaults_set()) {
            return false;
        }
        return true;
    }

    /**
     * Sets up the returned contexts walker.
     *
     * @return \core\dml\recordset_walk Recordset iterator (it returns \context|false[] in practice).
     */
    final protected function get_recordset_walk() {

        $recordset = $this->get_contexts_recordset();
        $callable = $this->recordset_callback();

        return new \core\dml\recordset_walk($recordset, $callable);
    }

    /**
     * This is a recordset_walk callback, it discards contexts that are not yet ready to be deleted.
     *
     * Treat this method as protected. It is only public because it is used as a callable.
     *
     * @param stdClass $record
     * @return \context|false
     */
    public function check_retention_periods($record) {

        \context_helper::preload_from_record($record);

        // No strict checking as the context may already be deleted (e.g. we just deleted a course,
        // module contexts below it will not exist).
        $context = \context::instance_by_id($record->id, false);
        if (!$context) {
            return false;
        }

        // We pass the value we just got from SQL so get_effective_context_purpose don't need to query
        // the db again to retrieve it. If there is no tool_dataprovider_ctxinstance record
        // $record->purposeid will be null which is ok as it would force get_effective_context_purpose
        // to return the default purpose for the context context level (no db queries involved).
        $purposevalue = $record->purposeid !== null ? $record->purposeid : context_instance::NOTSET;

        // It should be cheap as system purposes and context instance will be retrieved from a cache most of the time.
        $purpose = api::get_effective_context_purpose($context, $purposevalue);

        $dt = new \DateTime();
        $dt->setTimestamp($record->comparisontime);
        $di = new \DateInterval($purpose->get('retentionperiod'));
        $dt->add($di);

        if (time() < $dt->getTimestamp()) {
            // Discard this element if we have not reached the retention period yet.
            return false;
        }

        return $context;
    }
}
