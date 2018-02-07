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
 * Adhoc task that processes a data request and prepares the user's metadata for review.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\task;

use coding_exception;
use core\message\message;
use core\task\adhoc_task;
use core_user;
use moodle_exception;
use moodle_url;
use tool_dataprivacy\api;
use tool_dataprivacy\data_request;

defined('MOODLE_INTERNAL') || die();

/**
 * Class that processes a data request and prepares the user's metadata for review.
 *
 * Custom data accepted:
 * - requestid -> The ID of the data request to be processed.
 *
 * @package     tool_dataprivacy
 * @copyright   2018 Jun Pataleta
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class initiate_data_request_task extends adhoc_task {

    /**
     * Run the task to initiate the data request process.
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function execute() {
        global $CFG, $OUTPUT, $SITE;

        require_once($CFG->dirroot . '/admin/tool/dataprivacy/lib.php');

        if (!isset($this->get_custom_data()->requestid)) {
            throw new coding_exception('The custom data \'requestid\' is required.');
        }
        $requestid = $this->get_custom_data()->requestid;

        $requestpersistent = new data_request($requestid);
        $request = $requestpersistent->to_record();

        // Check if this request still needs to be processed. e.g. The user might have cancelled it before this task has run.
        if (!api::is_active($request->status)) {
            mtrace('Request ' . $request->id . ' with status ' . $request->status . ' doesn\'t need to be processed. Skipping...');
            return;
        }

        // Update the status of this request as pre-processing.
        mtrace('Generating user metadata...');
        api::update_request_status($requestid, api::DATAREQUEST_STATUS_PREPROCESSING);

        // TODO: Add code here to process the request and prepare the metadata to for review.

        // When the preparation of the metadata finishes, update the request status to awaiting approval.
        api::update_request_status($requestid, api::DATAREQUEST_STATUS_AWAITING_APPROVAL);
        mtrace('User metadata generation complete...');

        // Create message to send to the Data Protection Officer(s).
        $typetext = null;
        switch ($request->type) {
            case api::DATAREQUEST_TYPE_EXPORT:
                $typetext = get_string('requesttypeexport', 'tool_dataprivacy');
                break;
            case api::DATAREQUEST_TYPE_DELETE:
                $typetext = get_string('requesttypedelete', 'tool_dataprivacy');
                break;
            default:
                throw new moodle_exception('errorinvalidrequesttype', 'tool_dataprivacy');
        }
        $subject = get_string('datarequestemailsubject', 'tool_dataprivacy', $typetext);

        $requestedby = core_user::get_user($request->requestedby);
        $datarequestsurl = new moodle_url('/admin/tool/dataprivacy/datarequests.php');
        $message = new message();
        $message->courseid          = $SITE->id;
        $message->component         = 'tool_dataprivacy';
        $message->name              = 'contactdataprotectionofficer';
        $message->userfrom          = $requestedby;
        $message->replyto           = $requestedby->email;
        $message->replytoname       = fullname($requestedby->email);
        $message->subject           = $subject;
        $message->fullmessageformat = FORMAT_HTML;
        $message->notification      = 1;
        $message->contexturl        = $datarequestsurl;
        $message->contexturlname    = get_string('datarequests', 'tool_dataprivacy');

        // Prepare the context data for the email message body.
        $messagetextdata = [
            'requestedby' => fullname($requestedby),
            'requesttype' => $typetext,
            'requestdate' => userdate($request->timecreated),
            'requestcomments' => text_to_html($request->comments),
            'datarequestsurl' => $datarequestsurl
        ];
        $requestingfor = core_user::get_user($request->userid);
        if ($requestedby->id == $requestingfor->id) {
            $messagetextdata['requestfor'] = $messagetextdata['requestedby'];
        } else {
            $messagetextdata['requestfor'] = fullname(core_user::get_user($requestingfor));
        }

        // Get the list of the site Data Protection Officers.
        $dpos = api::get_site_dpos();

        // Email the data request to the Data Protection Officer(s)/Admin(s).
        foreach ($dpos as $dpo) {
            $messagetextdata['dponame'] = fullname($dpo);
            // Render message email body.
            $messagehtml = $OUTPUT->render_from_template('tool_dataprivacy/data_request_email', $messagetextdata);
            $message->userto = $dpo;
            $message->fullmessage = html_to_text($messagehtml);
            $message->fullmessagehtml = $messagehtml;
            // Send message.
            message_send($message);
            mtrace('Message sent to DPO: ' . $messagetextdata['dponame']);
        }
    }
}
