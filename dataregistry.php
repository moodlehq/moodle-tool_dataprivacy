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
 * Prints the data registry main page.
 *
 * @copyright 2018 onwards David Monllao
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */

require_once("../../../config.php");
require_once('lib.php');

$courseid = optional_param('course', 0, PARAM_INT);

$url = new moodle_url('/admin/tool/dataprivacy/dataregistry.php');
$PAGE->set_url($url);

require_login();
if (isguestuser()) {
    print_error('noguest');
}

$context = context_system::instance();

require_capability('tool/dataprivacy:managedataregistry', $context);

$PAGE->set_context($context);

$title = get_string('dataregistry', 'tool_dataprivacy');
$PAGE->set_heading($title);
$PAGE->set_title($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$dataregistry = new tool_dataprivacy\output\data_registry_page();
$renderer = $PAGE->get_renderer('tool_dataprivacy');
echo $renderer->render($dataregistry);

echo $OUTPUT->footer();
