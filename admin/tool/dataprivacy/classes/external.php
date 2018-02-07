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
}
