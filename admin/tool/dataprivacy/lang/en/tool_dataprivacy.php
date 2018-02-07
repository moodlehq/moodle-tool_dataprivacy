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
 * Strings for component 'tool_dataprivacy'
 *
 * @package    tool_dataprivacy
 * @copyright  2018 onwards Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Data privacy';
$string['pluginname_help'] = 'Data privacy plugin';
$string['contactdataprotectionofficer'] = 'Contact Data Protection Officer';
$string['contactdataprotectionofficer_desc'] = 'Enabling this feature will provide a link for users to contact the site\'s Data Protection Officer through this site. This link will be shown on their profile page, and on the site\'s privacy policy page, as well. The link leads to a form in which the user can make a data request to the Data Protection Officer.';
$string['contactdpoviaprivacypolicy'] = 'Please contact the site\'s Data Protection Officer as described in the Privacy Policy';
$string['dataprivacysettings'] = 'Data privacy settings';
$string['datarequestemailsubject'] = 'Data request: {$a}';
$string['datarequests'] = 'Data requests';
$string['dporolemapping'] = 'Data Protection Officer role mapping';
$string['dporolemapping_desc'] = 'Select one or more roles that map to the Data Protection Officer role. Users with these roles will be able to manage data requests. This requires the selected role(s) to have the capability \'tool/dataprivacy:managedatarequests\'';
