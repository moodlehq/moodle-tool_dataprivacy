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
 * Prints the contact form to the site's Data Protection Officer
 *
 * @copyright 2018 onwards Jun Pataleta
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package tool_dataprivacy
 */

require_once('../../../config.php');
require_once('lib.php');
require_once('classes/api.php');
require_once('createdatarequest_form.php');

$manage = optional_param('manage', false, PARAM_BOOL);

$url = new moodle_url('/admin/tool/dataprivacy/createdatarequest.php');

$PAGE->set_url($url);

require_login();
if (isguestuser()) {
    print_error('noguest');
}

$usercontext = context_user::instance($USER->id);
$PAGE->set_context($usercontext);

// Return URL.
if ($manage) {
    $returnurl = new moodle_url($CFG->wwwroot . '/admin/tool/dataprivacy/datarequests.php');
} else {
    $returnurl = new moodle_url($CFG->wwwroot . '/admin/tool/dataprivacy/mydatarequests.php');
}

// If contactdataprotectionofficer is disabled, send the user back to the profile page, or the privacy policy page.
if (!\tool_dataprivacy\api::can_contact_dpo()) {
    redirect($returnurl, get_string('contactdpoviaprivacypolicy', 'tool_dataprivacy'), \core\output\notification::NOTIFY_ERROR);
}

$mform = new tool_dataprivacy_data_request_form();

// Data request cancelled.
if ($mform->is_cancelled()) {
    redirect($returnurl);
}

// Data request submitted.
if ($data = $mform->get_data()) {
    \tool_dataprivacy\api::create_data_request($data->userid, $data->type, $data->comments);

    redirect($returnurl, get_string('requestsubmitted', 'tool_dataprivacy'));
}

$title = get_string('contactdataprotectionofficer', 'tool_dataprivacy');
$PAGE->set_heading($title);
$PAGE->set_title($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo $OUTPUT->box_start();
$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
