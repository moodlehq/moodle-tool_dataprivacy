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
 * @param stdClass $user User object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function tool_dataprivacy_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER;

    // Get the Privacy and policies category.
    if (!array_key_exists('privacyandpolicies', $tree->__get('categories'))) {
        // Create the category.
        $categoryname = get_string('privacyandpolicies', 'admin');
        $category = new core_user\output\myprofile\category('privacyandpolicies', $categoryname, 'contact');
        $tree->add_category($category);
    } else {
        // Get the existing category.
        $category = $tree->__get('categories')['privacyandpolicies'];
    }

    // Contact data protection officer link.
    if (\tool_dataprivacy\api::can_contact_dpo() && $iscurrentuser) {
        $renderer = $PAGE->get_renderer('tool_dataprivacy');
        $content = $renderer->render_contact_dpo_link($USER->email);
        $node = new core_user\output\myprofile\node('privacyandpolicies', 'contactdpo', null, null, null, $content);
        $category->add_node($node);
        $PAGE->requires->js_call_amd('tool_dataprivacy/myrequestactions', 'init');

        $url = new moodle_url('/admin/tool/dataprivacy/mydatarequests.php');
        $node = new core_user\output\myprofile\node('privacyandpolicies', 'datarequests',
            get_string('datarequests', 'tool_dataprivacy'), null, $url);
        $category->add_node($node);
    }

    // Add the Privacy category to the tree if it's not empty and it doesn't exist.
    $nodes = $category->nodes;
    if (!empty($nodes)) {
        if (!array_key_exists('privacyandpolicies', $tree->__get('categories'))) {
            $tree->add_category($category);
        }
        return true;
    }

    return false;
}

/**
 * Fragment to add a new purpose.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function tool_dataprivacy_output_fragment_addpurpose_form($args) {
    $persistent = new \tool_dataprivacy\purpose();
    $mform = new \tool_dataprivacy\form\purpose(null, ['persistent' => $persistent]);
    return $mform->render();
}


/**
 * Fragment to add a new category.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function tool_dataprivacy_output_fragment_addcategory_form($args) {
    $persistent = new \tool_dataprivacy\category();
    $mform = new \tool_dataprivacy\form\category(null, ['persistent' => $persistent]);
    return $mform->render();
}

/**
 * Returns purpose and category var names from a context class name
 *
 * @param string $classname
 * @return string[]
 */
function tool_dataprivacy_var_names_from_context($classname) {
    return [
        $classname . '_purpose',
        $classname . '_category',
    ];
}
