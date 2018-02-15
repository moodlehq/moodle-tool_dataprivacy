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
 * Class containing the external API functions functions for the Data Privacy tool.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use coding_exception;
use context_system;
use context_user;
use core\invalid_persistent_exception;
use dml_exception;
use external_api;
use external_description;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use restricted_context_exception;
use tool_dataprivacy\external\data_request_exporter;

/**
 * Class external.
 *
 * The external API for the Data Privacy tool.
 *
 * @copyright  2017 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Parameter description for cancel_data_request().
     *
     * @return external_function_parameters
     */
    public static function cancel_data_request_parameters() {
        return new external_function_parameters([
            'requestid' => new external_value(PARAM_INT, 'The request ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Cancel a data request.
     *
     * @param int $requestid The request ID.
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function cancel_data_request($requestid) {
        global $USER;

        $warnings = [];
        $params = external_api::validate_parameters(self::cancel_data_request_parameters(), [
            'requestid' => $requestid
        ]);
        $requestid = $params['requestid'];

        // Validate context.
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Ensure the request exists.
        $select = 'id = :id AND requestedby = :requestedby';
        $params = ['id' => $requestid, 'requestedby' => $USER->id];
        $requestexists = data_request::record_exists_select($select, $params);

        $result = false;
        if ($requestexists) {
            // TODO: Do we want a request to be non-cancellable past a certain point? E.g. When it's already approved/processing.
            $result = api::update_request_status($requestid, api::DATAREQUEST_STATUS_CANCELLED);
        } else {
            $warnings[] = [
                'item' => $requestid,
                'warningcode' => 'errorrequestnotfound',
                'message' => get_string('errorrequestnotfound', 'tool_dataprivacy')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for cancel_data_request().
     *
     * @return external_description
     */
    public static function cancel_data_request_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Parameter description for contact_dpo().
     *
     * @return external_function_parameters
     */
    public static function contact_dpo_parameters() {
        return new external_function_parameters([
            'message' => new external_value(PARAM_TEXT, 'The user\'s message to the Data Protection Officer(s)', VALUE_REQUIRED)
        ]);
    }

    /**
     * Deny a data request.
     *
     * @param string $message The message to be sent to the DPO.
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @throws restricted_context_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function contact_dpo($message) {
        global $USER;

        $warnings = [];
        $params = external_api::validate_parameters(self::contact_dpo_parameters(), [
            'message' => $message
        ]);
        $message = $params['message'];

        // Validate context.
        $userid = $USER->id;
        $context = context_user::instance($userid);
        self::validate_context($context);

        // Lodge the request.
        $datarequest = new data_request();
        // The user the request is being made for.
        $datarequest->set('userid', $userid);
        // The user making the request.
        $datarequest->set('requestedby', $userid);
        // Set status.
        $datarequest->set('status', api::DATAREQUEST_STATUS_PENDING);
        // Set request type.
        $datarequest->set('type', api::DATAREQUEST_TYPE_OTHERS);
        // Set request comments.
        $datarequest->set('comments', $message);

        // Store subject access request.
        $datarequest->create();

        // Get the list of the site Data Protection Officers.
        $dpos = api::get_site_dpos();

        // Email the data request to the Data Protection Officer(s)/Admin(s).
        $result = true;
        foreach ($dpos as $dpo) {
            $sendresult = api::notify_dpo($dpo, $datarequest);
            if (!$sendresult) {
                $result = false;
                $warnings[] = [
                    'item' => $dpo->id,
                    'warningcode' => 'errorsendingtodpo',
                    'message' => get_string('errorsendingmessagetodpo', 'tool_dataprivacy')
                ];
            }
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for deny_data_request().
     *
     * @return external_description
     */
    public static function contact_dpo_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Parameter description for get_data_request().
     *
     * @return external_function_parameters
     */
    public static function get_data_request_parameters() {
        return new external_function_parameters([
            'requestid' => new external_value(PARAM_INT, 'The request ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Fetch the details of a user's data request.
     *
     * @param int $requestid The request ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function get_data_request($requestid) {
        global $PAGE;

        $warnings = [];
        $params = external_api::validate_parameters(self::get_data_request_parameters(), [
            'requestid' => $requestid
        ]);
        $requestid = $params['requestid'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/dataprivacy:managedatarequests', $context);

        $requestpersistent = new data_request($requestid);
        $exporter = new data_request_exporter($requestpersistent, ['context' => $context]);
        $renderer = $PAGE->get_renderer('tool_dataprivacy');
        $result = $exporter->export($renderer);

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for get_data_request().
     *
     * @return external_description
     */
    public static function get_data_request_returns() {
        return new external_single_structure([
            'result' => data_request_exporter::get_read_structure(),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Parameter description for approve_data_request().
     *
     * @return external_function_parameters
     */
    public static function approve_data_request_parameters() {
        return new external_function_parameters([
            'requestid' => new external_value(PARAM_INT, 'The request ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Approve a data request.
     *
     * @param int $requestid The request ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function approve_data_request($requestid) {
        $warnings = [];
        $params = external_api::validate_parameters(self::approve_data_request_parameters(), [
            'requestid' => $requestid
        ]);
        $requestid = $params['requestid'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/dataprivacy:managedatarequests', $context);

        // Ensure the request exists.
        $requestexists = data_request::record_exists($requestid);

        $result = false;
        if ($requestexists) {
            $result = api::approve_data_request($requestid);
        } else {
            $warnings[] = [
                'item' => $requestid,
                'warningcode' => 'errorrequestnotfound',
                'message' => get_string('errorrequestnotfound', 'tool_dataprivacy')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for approve_data_request().
     *
     * @return external_description
     */
    public static function approve_data_request_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Parameter description for deny_data_request().
     *
     * @return external_function_parameters
     */
    public static function deny_data_request_parameters() {
        return new external_function_parameters([
            'requestid' => new external_value(PARAM_INT, 'The request ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Deny a data request.
     *
     * @param int $requestid The request ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function deny_data_request($requestid) {
        $warnings = [];
        $params = external_api::validate_parameters(self::deny_data_request_parameters(), [
            'requestid' => $requestid
        ]);
        $requestid = $params['requestid'];

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/dataprivacy:managedatarequests', $context);

        // Ensure the request exists.
        $requestexists = data_request::record_exists($requestid);

        $result = false;
        if ($requestexists) {
            $result = api::deny_data_request($requestid);
        } else {
            $warnings[] = [
                'item' => $requestid,
                'warningcode' => 'errorrequestnotfound',
                'message' => get_string('errorrequestnotfound', 'tool_dataprivacy')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for deny_data_request().
     *
     * @return external_description
     */
    public static function deny_data_request_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }
}
