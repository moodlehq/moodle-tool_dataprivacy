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
 * This page lets users manage categories.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$id = optional_param('id', 0, PARAM_INT);

require_login();
$context = \context_system::instance();
// TODO Check that data privacy is enabled.
require_capability('tool/dataprivacy:managedataregistry', $context);

$url = new \moodle_url('/admin/tool/dataprivacy/editcategory.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$category = new \tool_dataprivacy\category($id);
$form = new \tool_dataprivacy\form\category($PAGE->url->out(false), array('persistent' => $category));

$categoriesurl = new \moodle_url('/admin/tool/dataprivacy/categories.php');
if ($form->is_cancelled()) {
    redirect($categoriesurl);
} else if ($data = $form->get_data()) {
    if (empty($data->id)) {
        \tool_dataprivacy\api::create_category($data);
        $messagesuccess = get_string('categorycreated', 'tool_dataprivacy');
    } else {
        \tool_dataprivacy\api::update_category($data);
        $messagesuccess = get_string('categoryupdated', 'tool_dataprivacy');
    }
    redirect($categoriesurl, $messagesuccess, 0, \core\output\notification::NOTIFY_SUCCESS);
}

$output = $PAGE->get_renderer('tool_dataprivacy');

if ($id === 0) {
    $pagetitle = get_string('createcategory', 'tool_dataprivacy');
} else {
    $categoryexporter = new \tool_dataprivacy\external\category_exporter($category, ['context' => $context]);
    $exporteddata = $categoryexporter->export($output);
    $pagetitle = get_string('editcategory', 'tool_dataprivacy', $exporteddata->name);
}

$PAGE->set_heading($pagetitle);
echo $output->header();
$form->display();
echo $output->footer();
