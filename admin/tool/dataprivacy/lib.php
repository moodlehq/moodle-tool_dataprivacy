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
 * Data privacy plugin library
 * @package   tool_dataprivacy
 * @copyright 2018 onwards Jun Pataleta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to myprofile page.
 *
 * @param tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function tool_dataprivacy_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER;

    // Create a Privacy category.
    $categoryname = get_string('privacy', 'tool_dataprivacy');
    $dataprivacycategory = new core_user\output\myprofile\category('dataprivacy', $categoryname, 'contact');

    // Contact data protection officer link.
    if (\tool_dataprivacy\api::can_contact_dpo() && $iscurrentuser) {
        $renderer = $PAGE->get_renderer('tool_dataprivacy');
        $content = $renderer->render_contact_dpo_link($USER->email);
        $node = new core_user\output\myprofile\node('dataprivacy', 'contactdpo', null, null, null, $content);
        $dataprivacycategory->add_node($node);
        $PAGE->requires->js_call_amd('tool_dataprivacy/myrequestactions', 'init');

        $url = new moodle_url('/admin/tool/dataprivacy/mydatarequests.php');
        $node = new core_user\output\myprofile\node('dataprivacy', 'datarequests',
            get_string('datarequests', 'tool_dataprivacy'), null, $url);
        $dataprivacycategory->add_node($node);
    }

    // Add the Privacy category to the tree if it's not empty.
    $nodes = $dataprivacycategory->nodes;
    if (!empty($nodes)) {
        $tree->add_category($dataprivacycategory);
        return true;
    }

    return false;
}
