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
 * API tests.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\invalid_persistent_exception;
use core\task\manager;
use tool_dataprivacy\api;
use tool_dataprivacy\data_request;
use tool_dataprivacy\task\initiate_data_request_task;
use tool_dataprivacy\task\process_data_request_task;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * API tests.
 *
 * @package    core_competency
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dataprivacy_api_testcase extends advanced_testcase {

    /**
     * setUp.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test for api::update_request_status().
     */
    public function test_update_request_status() {
        $generator = new testing_data_generator();
        $s1 = $generator->create_user();

        // Create the sample data request.
        $datarequest = api::create_data_request($s1->id, api::DATAREQUEST_TYPE_EXPORT);

        $requestid = $datarequest->get('id');

        // Update with a valid status.
        $result = api::update_request_status($requestid, api::DATAREQUEST_STATUS_COMPLETE);
        $this->assertTrue($result);

        // Fetch the request record again.
        $datarequest = new data_request($requestid);
        $this->assertEquals(api::DATAREQUEST_STATUS_COMPLETE, $datarequest->get('status'));

        // Update with an invalid status.
        $this->expectException(invalid_persistent_exception::class);
        api::update_request_status($requestid, -1);
    }

    /**
     * Test for api::get_site_dpos() when there are no users with the DPO role.
     */
    public function test_get_site_dpos_no_dpos() {
        $admin = get_admin();

        $dpos = api::get_site_dpos();
        $this->assertCount(1, $dpos);
        $dpo = reset($dpos);
        $this->assertEquals($admin->id, $dpo->id);
    }

    /**
     * Test for api::get_site_dpos() when there are no users with the DPO role.
     */
    public function test_get_site_dpos() {
        global $DB;
        $generator = new testing_data_generator();
        $u1 = $generator->create_user();
        $u2 = $generator->create_user();

        $context = context_system::instance();

        // Give the manager role with the capability to manage data requests.
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u1 as a manager.
        role_assign($managerroleid, $u1->id, $context->id);

        // Give the editing teacher role with the capability to manage data requests.
        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $editingteacherroleid, $context->id, true);
        // Assign u1 as an editing teacher as well.
        role_assign($editingteacherroleid, $u1->id, $context->id);
        // Assign u2 as an editing teacher.
        role_assign($editingteacherroleid, $u2->id, $context->id);

        // Only map the manager role to the DPO role.
        set_config('dporoles', $managerroleid, 'tool_dataprivacy');

        $dpos = api::get_site_dpos();
        $this->assertCount(1, $dpos);
        $dpo = reset($dpos);
        $this->assertEquals($u1->id, $dpo->id);
    }

    /**
     * Test for api::approve_data_request().
     */
    public function test_approve_data_request() {
        global $DB;

        $generator = new testing_data_generator();
        $s1 = $generator->create_user();
        $u1 = $generator->create_user();

        $context = context_system::instance();

        // Manager role.
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        // Give the manager role with the capability to manage data requests.
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u1 as a manager.
        role_assign($managerroleid, $u1->id, $context->id);

        // Map the manager role to the DPO role.
        set_config('dporoles', $managerroleid, 'tool_dataprivacy');

        // Create the sample data request.
        $datarequest = api::create_data_request($s1->id, api::DATAREQUEST_TYPE_EXPORT);
        $requestid = $datarequest->get('id');

        // Make this ready for approval.
        api::update_request_status($requestid, api::DATAREQUEST_STATUS_AWAITING_APPROVAL);

        $this->setUser($u1);
        $result = api::approve_data_request($requestid);
        $this->assertTrue($result);
        $datarequest = new data_request($requestid);
        $this->assertEquals($u1->id, $datarequest->get('dpo'));
        $this->assertEquals(api::DATAREQUEST_STATUS_APPROVED, $datarequest->get('status'));

        // Test adhoc task creation.
        $adhoctasks = manager::get_adhoc_tasks(process_data_request_task::class);
        $this->assertCount(1, $adhoctasks);
    }

    /**
     * Test for api::approve_data_request() with the request not yet waiting for approval.
     */
    public function test_approve_data_request_not_yet_ready() {
        global $DB;

        $generator = new testing_data_generator();
        $s1 = $generator->create_user();
        $u1 = $generator->create_user();

        $context = context_system::instance();

        // Manager role.
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        // Give the manager role with the capability to manage data requests.
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u1 as a manager.
        role_assign($managerroleid, $u1->id, $context->id);

        // Map the manager role to the DPO role.
        set_config('dporoles', $managerroleid, 'tool_dataprivacy');

        // Create the sample data request.
        $datarequest = api::create_data_request($s1->id, api::DATAREQUEST_TYPE_EXPORT);
        $requestid = $datarequest->get('id');

        $this->setUser($u1);
        $this->expectException(moodle_exception::class);
        api::approve_data_request($requestid);
    }

    /**
     * Test for api::approve_data_request() when called by a user who doesn't have the DPO role.
     */
    public function test_approve_data_request_non_dpo_user() {
        $generator = new testing_data_generator();
        $student = $generator->create_user();
        $teacher = $generator->create_user();

        // Create the sample data request.
        $datarequest = api::create_data_request($student->id, api::DATAREQUEST_TYPE_EXPORT);

        $requestid = $datarequest->get('id');

        // Login as a user without DPO role.
        $this->setUser($teacher);
        $this->expectException(required_capability_exception::class);
        api::approve_data_request($requestid);
    }

    /**
     * Test for api::can_contact_dpo()
     */
    public function test_can_contact_dpo() {
        // Default (enabled).
        $this->assertTrue(api::can_contact_dpo());

        // Disable.
        set_config('contactdataprotectionofficer', 0, 'tool_dataprivacy');
        $this->assertFalse(api::can_contact_dpo());

        // Enable again.
        set_config('contactdataprotectionofficer', 1, 'tool_dataprivacy');
        $this->assertTrue(api::can_contact_dpo());
    }

    /**
     * Test for api::can_manage_data_requests()
     */
    public function test_can_manage_data_requests() {
        global $DB;

        // No configured site DPOs yet.
        $admin = get_admin();
        $this->assertTrue(api::can_manage_data_requests($admin->id));

        $generator = new testing_data_generator();
        $dpo = $generator->create_user();
        $nondpocapable = $generator->create_user();
        $nondpoincapable = $generator->create_user();

        $context = context_system::instance();

        // Manager role.
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        // Give the manager role with the capability to manage data requests.
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u1 as a manager.
        role_assign($managerroleid, $dpo->id, $context->id);

        // Editing teacher role.
        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
        // Give the editing teacher role with the capability to manage data requests.
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u2 as an editing teacher.
        role_assign($editingteacherroleid, $nondpocapable->id, $context->id);

        // Map only the manager role to the DPO role.
        set_config('dporoles', $managerroleid, 'tool_dataprivacy');

        // User with capability and has DPO role.
        $this->assertTrue(api::can_manage_data_requests($dpo->id));
        // User with capability but has no DPO role.
        $this->assertFalse(api::can_manage_data_requests($nondpocapable->id));
        // User without the capability and has no DPO role.
        $this->assertFalse(api::can_manage_data_requests($nondpoincapable->id));
    }

    /**
     * Test for api::create_data_request()
     */
    public function test_create_data_request() {
        $generator = new testing_data_generator();
        $user = $generator->create_user();
        $comment = 'sample comment';

        // Login as user.
        $this->setUser($user->id);

        // Test data request creation.
        $datarequest = api::create_data_request($user->id, api::DATAREQUEST_TYPE_EXPORT, $comment);
        $this->assertEquals($user->id, $datarequest->get('userid'));
        $this->assertEquals($user->id, $datarequest->get('requestedby'));
        $this->assertEquals(api::DATAREQUEST_TYPE_EXPORT, $datarequest->get('type'));
        $this->assertEquals(api::DATAREQUEST_STATUS_PENDING, $datarequest->get('status'));
        $this->assertEquals($comment, $datarequest->get('comments'));

        // Test adhoc task creation.
        $adhoctasks = manager::get_adhoc_tasks(initiate_data_request_task::class);
        $this->assertCount(1, $adhoctasks);
    }

    /**
     * Test for api::deny_data_request()
     */
    public function test_deny_data_request() {
        $generator = new testing_data_generator();
        $user = $generator->create_user();
        $comment = 'sample comment';

        // Login as user.
        $this->setUser($user->id);

        // Test data request creation.
        $datarequest = api::create_data_request($user->id, api::DATAREQUEST_TYPE_EXPORT, $comment);

        // Login as the admin (default DPO when no one is set).
        $this->setAdminUser();

        // Make this ready for approval.
        api::update_request_status($datarequest->get('id'), api::DATAREQUEST_STATUS_AWAITING_APPROVAL);

        // Deny the data request.
        $result = api::deny_data_request($datarequest->get('id'));
        $this->assertTrue($result);
    }

    /**
     * Test for api::deny_data_request()
     */
    public function test_deny_data_request_without_permissions() {
        $generator = new testing_data_generator();
        $user = $generator->create_user();
        $comment = 'sample comment';

        // Login as user.
        $this->setUser($user->id);

        // Test data request creation.
        $datarequest = api::create_data_request($user->id, api::DATAREQUEST_TYPE_EXPORT, $comment);

        // Login as a non-DPO user and try to call deny_data_request.
        $user2 = $generator->create_user();
        $this->setUser($user2);
        $this->expectException(required_capability_exception::class);
        api::deny_data_request($datarequest->get('id'));
    }

    /**
     * Test for api::get_data_requests()
     */
    public function test_get_data_requests() {
        $generator = new testing_data_generator();
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $comment = 'sample comment';

        // Make a data request as user 1.
        $d1 = api::create_data_request($user1->id, api::DATAREQUEST_TYPE_EXPORT, $comment);
        // Make a data request as user 2.
        $d2 = api::create_data_request($user2->id, api::DATAREQUEST_TYPE_EXPORT, $comment);

        // Fetching data requests of specific users.
        $requests = api::get_data_requests($user1->id);
        $this->assertCount(1, $requests);
        $datarequest = reset($requests);
        $this->assertEquals($d1->to_record(), $datarequest->to_record());

        $requests = api::get_data_requests($user2->id);
        $this->assertCount(1, $requests);
        $datarequest = reset($requests);
        $this->assertEquals($d2->to_record(), $datarequest->to_record());

        // Fetching data requests of all users.
        // As guest.
        $this->setGuestUser();
        $requests = api::get_data_requests();
        $this->assertEmpty($requests);

        // As DPO (admin in this case, which is default if no site DPOs are set).
        $this->setAdminUser();
        $requests = api::get_data_requests();
        $this->assertCount(2, $requests);
    }

    /**
     * Data provider for test_has_ongoing_request.
     */
    public function status_provider() {
        return [
            [api::DATAREQUEST_STATUS_PENDING, true],
            [api::DATAREQUEST_STATUS_PREPROCESSING, true],
            [api::DATAREQUEST_STATUS_AWAITING_APPROVAL, true],
            [api::DATAREQUEST_STATUS_APPROVED, true],
            [api::DATAREQUEST_STATUS_PROCESSING, true],
            [api::DATAREQUEST_STATUS_COMPLETE, false],
            [api::DATAREQUEST_STATUS_CANCELLED, false],
            [api::DATAREQUEST_STATUS_REJECTED, false],
        ];
    }

    /**
     * Test for api::has_ongoing_request()
     *
     * @dataProvider status_provider
     * @param $status
     * @param $expected
     */
    public function test_has_ongoing_request($status, $expected) {
        $generator = new testing_data_generator();
        $user1 = $generator->create_user();

        // Make a data request as user 1.
        $request = api::create_data_request($user1->id, api::DATAREQUEST_TYPE_EXPORT);
        // Set the status.
        api::update_request_status($request->get('id'), $status);

        // Check if this request is ongoing.
        $result = api::has_ongoing_request($user1->id, api::DATAREQUEST_TYPE_EXPORT);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for api::is_active()
     *
     * @dataProvider status_provider
     * @param $status
     * @param $expected
     */
    public function test_is_active($status, $expected) {
        // Check if this request is ongoing.
        $result = api::is_active($status);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for api::is_site_dpo()
     */
    public function test_is_site_dpo() {
        global $DB;

        // No configured site DPOs yet.
        $admin = get_admin();
        $this->assertTrue(api::is_site_dpo($admin->id));

        $generator = new testing_data_generator();
        $dpo = $generator->create_user();
        $nondpo = $generator->create_user();

        $context = context_system::instance();

        // Manager role.
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        // Give the manager role with the capability to manage data requests.
        assign_capability('tool/dataprivacy:managedatarequests', CAP_ALLOW, $managerroleid, $context->id, true);
        // Assign u1 as a manager.
        role_assign($managerroleid, $dpo->id, $context->id);

        // Map only the manager role to the DPO role.
        set_config('dporoles', $managerroleid, 'tool_dataprivacy');

        // User is a DPO.
        $this->assertTrue(api::is_site_dpo($dpo->id));
        // User is not a DPO.
        $this->assertFalse(api::is_site_dpo($nondpo->id));
    }

    /**
     * Data provider function for test_notify_dpo
     *
     * @return array
     */
    public function notify_dpo_provider() {
        return [
            [api::DATAREQUEST_TYPE_EXPORT, 'requesttypeexport'],
            [api::DATAREQUEST_TYPE_DELETE, 'requesttypedelete'],
            [api::DATAREQUEST_TYPE_OTHERS, 'requesttypeothers'],
        ];
    }

    /**
     * Test for api::notify_dpo()
     *
     * @dataProvider notify_dpo_provider
     */
    public function test_notify_dpo($type, $typestringid) {
        $generator = new testing_data_generator();
        $user1 = $generator->create_user();

        // Make a data request as user 1.
        $this->setUser($user1);
        $request = api::create_data_request($user1->id, $type);

        $sink = $this->redirectMessages();
        // Let's just use admin as DPO (It's the default if not set).
        $dpo = get_admin();
        $messageid = api::notify_dpo($dpo, $request);
        $this->assertNotFalse($messageid);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $message = reset($messages);

        // Check some of the message properties.
        $this->assertEquals($user1->id, $message->useridfrom);
        $this->assertEquals($dpo->id, $message->useridto);
        $typestring = get_string($typestringid, 'tool_dataprivacy');
        $subject = get_string('datarequestemailsubject', 'tool_dataprivacy', $typestring);
        $this->assertEquals($subject, $message->subject);
        $this->assertEquals('tool_dataprivacy', $message->component);
        $this->assertEquals('contactdataprotectionofficer', $message->eventtype);
        $this->assertContains(fullname($dpo), $message->fullmessage);
        $this->assertContains(fullname($user1), $message->fullmessage);
    }
}
