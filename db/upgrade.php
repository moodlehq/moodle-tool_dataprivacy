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
 * tool_dataprivacy plugin upgrade code
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade auth_cas.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_tool_dataprivacy_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018020703) {

        // Define table dataprivacy_purpose to be created.
        $table = new xmldb_table('dataprivacy_purpose');

        // Adding fields to table dataprivacy_purpose.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('retentionperiod', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('protected', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dataprivacy_purpose.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for dataprivacy_purpose.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dataprivacy savepoint reached.
        upgrade_plugin_savepoint(true, 2018020703, 'error', 'dataprivacy');
    }

    if ($oldversion < 2018020705) {

        // Define table dataprivacy_category to be created.
        $table = new xmldb_table('dataprivacy_category');

        // Adding fields to table dataprivacy_category.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dataprivacy_category.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for dataprivacy_category.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dataprivacy savepoint reached.
        upgrade_plugin_savepoint(true, 2018020705, 'error', 'dataprivacy');
    }

    if ($oldversion < 2018021807) {

        // Define table dataprivacy_context_instance to be created.
        $table = new xmldb_table('dataprivacy_context_instance');

        // Adding fields to table dataprivacy_context_instance.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('purposeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dataprivacy_context_instance.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('contextid', XMLDB_KEY_FOREIGN_UNIQUE, array('contextid'), 'context', array('id'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'dataprivacy_category', array('id'));
        $table->add_key('purposeid', XMLDB_KEY_FOREIGN, array('purposeid'), 'dataprivacy_purpose', array('id'));

        // Conditionally launch create table for dataprivacy_context_instance.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dataprivacy savepoint reached.
        upgrade_plugin_savepoint(true, 2018021807, 'tool', 'dataprivacy');
    }

    if ($oldversion < 2018021809) {

        // Define table dataprivacy_contextlevel to be created.
        $table = new xmldb_table('dataprivacy_contextlevel');

        // Adding fields to table dataprivacy_contextlevel.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contextlevel', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('purposeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dataprivacy_contextlevel.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('contextlevel', XMLDB_KEY_UNIQUE, array('contextlevel'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'dataprivacy_category', array('id'));
        $table->add_key('purposeid', XMLDB_KEY_FOREIGN, array('purposeid'), 'dataprivacy_purpose', array('id'));

        // Conditionally launch create table for dataprivacy_contextlevel.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Dataprivacy savepoint reached.
        upgrade_plugin_savepoint(true, 2018021809, 'tool', 'dataprivacy');
    }

    return true;
}
