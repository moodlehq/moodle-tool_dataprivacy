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
 * Adds Data privacy-related settings.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 onwards Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('root', new admin_category('dataprivacysettings', get_string('pluginname', 'tool_dataprivacy')));

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_dataprivacy', get_string('dataprivacysettings', 'tool_dataprivacy'));
    $ADMIN->add('dataprivacysettings', $settings);

    if ($ADMIN->fulltree) {
        // Contact data protection officer.
        $settings->add(new admin_setting_configcheckbox('tool_dataprivacy/contactdataprotectionofficer',
                new lang_string('contactdataprotectionofficer', 'tool_dataprivacy'),
                new lang_string('contactdataprotectionofficer_desc', 'tool_dataprivacy'), 1)
        );

        // Fetch roles that are assignable.
        $assignableroles = get_assignable_roles(context_system::instance());

        // Fetch roles that have the capability to manage data requests.
        $capableroles = get_roles_with_capability('tool/dataprivacy:managedatarequests');

        // Role(s) that map to the Data Protection Officer role. These are assignable roles with the capability to manage data requests.
        $roles = [];
        foreach ($capableroles as $key => $role) {
            if (array_key_exists($key, $assignableroles)) {
                $roles[$key] = $assignableroles[$key];
            }
        }
        if (!empty($roles)) {
            $settings->add(new admin_setting_configmultiselect('tool_dataprivacy/dporoles',
                    new lang_string('dporolemapping', 'tool_dataprivacy'),
                    new lang_string('dporolemapping_desc', 'tool_dataprivacy'), null, $roles)
            );
        }
    }
}

// Link that leads to the data requests management page.
$ADMIN->add('dataprivacysettings', new admin_externalpage('datarequests', get_string('datarequests', 'tool_dataprivacy'),
        new moodle_url('/admin/tool/dataprivacy/datarequests.php'), 'tool/dataprivacy:managedatarequests')
);

$ADMIN->add('dataprivacysettings', new admin_externalpage('dataregistry', get_string('dataregistry', 'tool_dataprivacy'),
    new moodle_url('/admin/tool/dataprivacy/dataregistry.php'), 'tool/dataprivacy:managedatarequests')
);
