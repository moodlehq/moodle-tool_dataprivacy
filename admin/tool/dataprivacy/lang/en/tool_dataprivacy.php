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
$string['approve'] = 'Approve';
$string['approverequest'] = 'Approve request';
$string['cancelrequest'] = 'Cancel request';
$string['cancelrequestconfirmation'] = 'Do you really want cancel this data request?';
$string['close'] = 'Close';
$string['confirmapproval'] = 'Do you really want approve this data request?';
$string['confirmdenial'] = 'Do you really want deny this data request?';
$string['contactdataprotectionofficer'] = 'Contact Data Protection Officer';
$string['contactdataprotectionofficer_desc'] = 'Enabling this feature will provide a link for users to contact the site\'s Data Protection Officer through this site. This link will be shown on their profile page, and on the site\'s privacy policy page, as well. The link leads to a form in which the user can make a data request to the Data Protection Officer.';
$string['contactdpoviaprivacypolicy'] = 'Please contact the site\'s Data Protection Officer as described in the Privacy Policy';
$string['dataprivacy:makedatarequestsforchildren'] = 'Make data requests for children';
$string['dataprivacy:managedatarequests'] = 'Manage data requests';
$string['dataprivacysettings'] = 'Data privacy settings';
$string['datarequestemailsubject'] = 'Data request: {$a}';
$string['datarequests'] = 'Data requests';
$string['daterequested'] = 'Date requested';
$string['daterequesteddetail'] = 'Date requested:';
$string['deny'] = 'Deny';
$string['denyrequest'] = 'Deny request';
$string['download'] = 'Download';
$string['dporolemapping'] = 'Data Protection Officer role mapping';
$string['dporolemapping_desc'] = 'Select one or more roles that map to the Data Protection Officer role. Users with these roles will be able to manage data requests. This requires the selected role(s) to have the capability \'tool/dataprivacy:managedatarequests\'';
$string['emailsalutation'] = 'Dear {$a},';
$string['errorinvalidrequeststatus'] = 'Invalid request status!';
$string['errorinvalidrequesttype'] = 'Invalid request type!';
$string['errorrequestalreadyexists'] = 'You already have an ongoing request.';
$string['errorrequestnotfound'] = 'Request not found';
$string['errorrequestnotwaitingforapproval'] = 'The request is not awaiting approval. Either it is not yet ready or it has already been processed.';
$string['errorsendingmessagetodpo'] = 'An error was encountered while trying to send a message to {$a}.';
$string['messageprovider:contactdataprotectionofficer'] = 'Data requests';
$string['messageprovider:datarequestprocessingresults'] = 'Data request processing results';
$string['message'] = 'Message';
$string['messagelabel'] = 'Message:';
$string['mypersonaldatarequests'] = 'My personal data requests';
$string['nameemail'] = '{$a->name} ({$a->email})';
$string['newrequest'] = 'New request';
$string['nodatarequests'] = 'There are no data requests';
$string['nopersonaldatarequests'] = 'You don\'t have any personal data requests';
$string['nosubjectaccessrequests'] = 'There are no data requests that you need to act on';
$string['privacy'] = 'Privacy';
$string['replyto'] = 'Reply to';
$string['requestactions'] = 'Actions';
$string['requestby'] = 'Requested by';
$string['requestcomments'] = 'Comments';
$string['requestcomments_help'] = 'Please feel free to provide more details about your request';
$string['requestemailintro'] = 'You have received a data request:';
$string['requestfor'] = 'Requesting for';
$string['requeststatus'] = 'Status';
$string['requestsubmitted'] = 'Your request has been submitted to the site\'s Data Protection Officer';
$string['requesttype'] = 'Type';
$string['requesttype_help'] = 'Select the reason why you would like to contact the site\'s Data Protection Officer';
$string['requesttypedelete'] = 'Delete all of my personal data';
$string['requesttypedeleteshort'] = 'Delete';
$string['requesttypeexport'] = 'Export all of my personal data';
$string['requesttypeexportshort'] = 'Export';
$string['requesttypeothers'] = 'General inquiry';
$string['requesttypeothersshort'] = 'Others';
$string['resultdeleted'] = 'You recently requested to have your account and personal data in {$a} to be deleted. This process has been completed and you will no longer be able to log in.';
$string['resultdownloadready'] = 'Your copy of your personal data in {$a} that you recently requested is now available for download. Please click on the link below to go to the download page.';
$string['reviewdata'] = 'Review data';
$string['send'] = 'Send';
$string['statusapproved'] = 'Approved';
$string['statusawaitingapproval'] = 'Awaiting approval';
$string['statuscancelled'] = 'Cancelled';
$string['statuscomplete'] = 'Complete';
$string['statusdetail'] = 'Status:';
$string['statuspreprocessing'] = 'Pre-processing';
$string['statusprocessing'] = 'Processing';
$string['statuspending'] = 'Pending';
$string['statusrejected'] = 'Rejected';
$string['user'] = 'User';
$string['viewrequest'] = 'View the request';
