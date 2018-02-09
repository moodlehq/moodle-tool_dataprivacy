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
 * Chat external functions and service definitions.
 *
 * @package    tool_dataprivacy
 * @category   external
 * @copyright  2018 Jun Pataleta <jun@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
$functions = [
    'tool_dataprivacy_cancel_data_request' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'cancel_data_request',
        'classpath'     => '',
        'description'   => 'Cancel the data request made by the user',
        'type'          => 'write',
        'capabilities'  => '',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_contact_dpo' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'contact_dpo',
        'classpath'     => '',
        'description'   => 'Contact the site Data Protection Officer(s)',
        'type'          => 'write',
        'capabilities'  => '',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_get_data_request' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'get_data_request',
        'classpath'     => '',
        'description'   => 'Fetch the details of a user\'s data request',
        'type'          => 'read',
        'capabilities'  => 'tool/dataprivacy:managedatarequests',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_approve_data_request' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'approve_data_request',
        'classpath'     => '',
        'description'   => 'Approve a data request',
        'type'          => 'write',
        'capabilities'  => 'tool/dataprivacy:managedatarequests',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_deny_data_request' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'deny_data_request',
        'classpath'     => '',
        'description'   => 'Deny a data request',
        'type'          => 'write',
        'capabilities'  => 'tool/dataprivacy:managedatarequests',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_get_users' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'get_users',
        'classpath'     => '',
        'description'   => 'Fetches a list of users',
        'type'          => 'read',
        'capabilities'  => 'tool/dataprivacy:managedatarequests',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_dataprivacy_delete_purpose' => [
        'classname'     => 'tool_dataprivacy\external',
        'methodname'    => 'delete_purpose',
        'classpath'     => '',
        'description'   => 'Deletes an existing data purpose',
        'type'          => 'write',
        'capabilities'  => '',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
