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
 * Class containing helper methods for processing data requests.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;

use coding_exception;
use context_system;
use core\invalid_persistent_exception;
use core\task\manager;
use dml_exception;
use tool_dataprivacy\task\initiate_data_request_task;
use tool_dataprivacy\task\process_data_request_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing helper methods for processing data requests.
 *
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /** Data export request type. */
    const DATAREQUEST_TYPE_EXPORT = 1;

    /** Data deletion request type. */
    const DATAREQUEST_TYPE_DELETE = 2;

    /** Newly submitted and we haven't yet started finding out where they have data. */
    const DATAREQUEST_STATUS_PENDING = 0;

    /** Newly submitted and we have started to find the location of data. */
    const DATAREQUEST_STATUS_PREPROCESSING = 1;

    /** Metadata ready and awaiting review and approval by the Data Protection officer. */
    const DATAREQUEST_STATUS_AWAITING_APPROVAL = 2;

    /** Request approved and will be processed soon. */
    const DATAREQUEST_STATUS_APPROVED = 3;

    /** The request is now being processed. */
    const DATAREQUEST_STATUS_PROCESSING = 4;

    /** Data request completed. */
    const DATAREQUEST_STATUS_COMPLETE = 5;

    /** Data request cancelled by the user. */
    const DATAREQUEST_STATUS_CANCELLED = 6;

    /** Data request rejected by the DPO. */
    const DATAREQUEST_STATUS_REJECTED = 7;

    /**
     * Determines whether the user can contact the site's Data Protection Officer via Moodle.
     *
     * @return boolean True when tool_dataprivacy|contactdataprotectionofficer is enabled.
     * @throws dml_exception
     */
    public static function can_contact_dpo() {
        return get_config('tool_dataprivacy', 'contactdataprotectionofficer');
    }

    /**
     * Fetches the list of users with the Data Protection Officer role.
     *
     * @throws dml_exception
     */
    public static function get_site_dpos() {
        // Get role(s) that can manage data requests.
        $dporoles = explode(',', get_config('tool_dataprivacy', 'dporoles'));

        $dpos = [];
        $context = context_system::instance();
        foreach ($dporoles as $roleid) {
            // Fetch users that can manage data requests.
            $dpos += get_role_users($roleid, $context, false, 'u.*');
        }

        // If the site has no data protection officer, defer to site admin(s).
        if (empty($dpos)) {
            $dpos = get_admins();
        }
        return $dpos;
    }

    /**
     * Lodges a data request and sends the request details to the site Data Protection Officer(s).
     *
     * @param int $foruser The user whom the request is being made for.
     * @param int $type The request type.
     * @param string $comments Request comments.
     * @throws invalid_persistent_exception
     * @throws coding_exception
     */
    public static function create_data_request($foruser, $type, $comments) {
        global $USER;

        $datarequest = new data_request();
        // The user the request is being made for.
        $datarequest->set('userid', $foruser);
        // The user making the request.
        $datarequest->set('requestedby', $USER->id);
        // Set status.
        $datarequest->set('status', self::DATAREQUEST_STATUS_PENDING);
        // Set request type.
        $datarequest->set('type', $type);
        // Set request comments.
        $datarequest->set('comments', $comments);

        // Store subject access request.
        $datarequest->create();

        // Fire an ad hoc task to initiate the data request process.
        $task = new initiate_data_request_task();
        $task->set_custom_data(['requestid' => $datarequest->get('id')]);
        manager::queue_adhoc_task($task, true);
    }

    /**
     * Fetches the list of the data requests.
     *
     * If user ID is provided, it fetches the data requests for the user.
     * Otherwise, it fetches all of the data requests, provided that the user has the capability to manage data requests.
     * (e.g. Users with the Data Protection Officer roles)
     *
     * @param int $userid The User ID.
     * @return data_request[]
     * @throws dml_exception
     */
    public static function get_data_requests($userid = 0) {
        global $USER;
        $results = [];
        if ($userid) {
            $results = data_request::get_records(['userid' => $userid], 'status');
        } else {
            // If the current user is one of the site's Data Protection Officers, then fetch all data requests.
            $dpoids = self::get_site_dpos();
            if (array_key_exists($USER->id, $dpoids)) {
                $results = data_request::get_records(null, 'status');
            }
        }

        return $results;
    }

    /**
     * Checks whether there is already an existing pending/in-progress data request for a user for a given request type.
     *
     * @param int $userid The user ID.
     * @param int $type The request type.
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function has_ongoing_request($userid, $type) {
        global $DB;

        // Check if the user already has an incomplete data request of the same type.
        $nonpendingstatuses = [
            self::DATAREQUEST_STATUS_COMPLETE,
            self::DATAREQUEST_STATUS_CANCELLED,
            self::DATAREQUEST_STATUS_REJECTED,
        ];
        list($insql, $inparams) = $DB->get_in_or_equal($nonpendingstatuses, SQL_PARAMS_NAMED);
        $select = 'type = :type AND userid = :userid AND status NOT ' . $insql;
        $params = array_merge([
            'type' => $type,
            'userid' => $userid
        ], $inparams);

        return data_request::record_exists_select($select, $params);
    }

    /**
     * Determines whether a request is active or not based on its status.
     *
     * @param int $status The request status.
     * @return bool
     */
    public static function is_active($status) {
        // List of statuses which doesn't require any further processing.
        $finalstatuses = [
            self::DATAREQUEST_STATUS_COMPLETE,
            self::DATAREQUEST_STATUS_CANCELLED,
            self::DATAREQUEST_STATUS_REJECTED,
        ];

        return !in_array($status, $finalstatuses);
    }

    /**
     * Cancels the data request for a given request ID.
     *
     * @param int $requestid The request identifier.
     * @param int $status The request status.
     * @return bool
     * @throws invalid_persistent_exception
     * @throws coding_exception
     */
    public static function update_request_status($requestid, $status) {
        // Update the request.
        $datarequest = new data_request($requestid);
        $datarequest->set('status', $status);
        return $datarequest->update();
    }

    /**
     * Fetches a request based on the request ID.
     *
     * @param int $requestid The request identifier
     * @return data_request
     */
    public static function get_request($requestid) {
        return new data_request($requestid);
    }
}
