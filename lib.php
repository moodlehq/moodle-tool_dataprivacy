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

    $formdata = [];
    if (!empty($args['jsonformdata'])) {
        $serialiseddata = json_decode($args['jsonformdata']);
        parse_str($serialiseddata, $formdata);
    }

    $persistent = new \tool_dataprivacy\purpose();
    $mform = new \tool_dataprivacy\form\purpose(null, ['persistent' => $persistent],
        'post', '', null, true, $formdata);

    if (!empty($args['jsonformdata'])) {
        // Show errors if data was received.
        $mform->is_validated();
    }

    return $mform->render();
}

/**
 * Fragment to add a new category.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function tool_dataprivacy_output_fragment_addcategory_form($args) {

    $formdata = [];
    if (!empty($args['jsonformdata'])) {
        $serialiseddata = json_decode($args['jsonformdata']);
        parse_str($serialiseddata, $formdata);
    }

    $persistent = new \tool_dataprivacy\category();
    $mform = new \tool_dataprivacy\form\category(null, ['persistent' => $persistent],
        'post', '', null, true, $formdata);

    if (!empty($args['jsonformdata'])) {
        // Show errors if data was received.
        $mform->is_validated();
    }

    return $mform->render();
}

/**
 * Fragment to edit a context purpose and category.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function tool_dataprivacy_output_fragment_context_form($args) {
    global $PAGE;

    $contextid = $args[0];

    $context = \context_helper::instance_by_id($contextid);
    $customdata = tool_dataprivacy_get_context_form_customdata($context);

    if (!empty($customdata['purposeretentionperiods'])) {
        $PAGE->requires->js_call_amd('tool_dataprivacy/effective_retention_period', 'init', [$customdata['purposeretentionperiods']]);
    }
    $mform = new \tool_dataprivacy\form\context_instance(null, $customdata);
    return $mform->render();
}

/**
 * Fragment to edit a contextlevel purpose and category.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function tool_dataprivacy_output_fragment_contextlevel_form($args) {
    global $PAGE;

    $contextlevel = $args[0];
    $customdata = tool_dataprivacy_get_contextlevel_form_customdata($contextlevel);

    if (!empty($customdata['purposeretentionperiods'])) {
        $PAGE->requires->js_call_amd('tool_dataprivacy/effective_retention_period', 'init', [$customdata['purposeretentionperiods']]);
    }

    $mform = new \tool_dataprivacy\form\contextlevel(null, $customdata);
    return $mform->render();
}

function tool_dataprivacy_get_context_form_customdata(\context $context) {

    $persistent = \tool_dataprivacy\context_instance::get_record_by_contextid($context->id, false);
    if (!$persistent) {
        $persistent = new \tool_dataprivacy\context_instance();
        $persistent->set('contextid', $context->id);
    }

    $purposeoptions = \tool_dataprivacy\output\data_registry_page::purpose_options(
        \tool_dataprivacy\api::get_purposes()
    );
    $categoryoptions = \tool_dataprivacy\output\data_registry_page::category_options(
        \tool_dataprivacy\api::get_categories()
    );

    $customdata = [
        'context' => $context,
        'subjectscope' => \tool_dataprivacy\api::get_subject_scope($context),
        'contextname' => $context->get_context_name(),
        'persistent' => $persistent,
        'purposes' => $purposeoptions,
        'categories' => $categoryoptions,
    ];

    $effectivepurpose = \tool_dataprivacy\api::get_effective_purpose($context);
    if ($effectivepurpose) {

        $customdata['currentretentionperiod'] = tool_dataprivacy_get_retention_display_text($effectivepurpose, $context->contextlevel, $context);

        $customdata['purposeretentionperiods'] = [];
        foreach ($purposeoptions as $optionvalue => $unused) {
            // Get the effective purpose if $optionvalue would be the selected value.
            $purpose = \tool_dataprivacy\api::get_effective_purpose($context, $optionvalue);

            $retentionperiod = tool_dataprivacy_get_retention_display_text(
                $purpose,
                $context->contextlevel,
                $context
            );
            $customdata['purposeretentionperiods'][$optionvalue] = $retentionperiod;
        }
    }

    return $customdata;
}

function tool_dataprivacy_get_contextlevel_form_customdata($contextlevel) {

    $persistent = \tool_dataprivacy\contextlevel::get_record_by_contextlevel($contextlevel, false);
    if (!$persistent) {
        $persistent = new \tool_dataprivacy\contextlevel();
        $persistent->set('contextlevel', $contextlevel);
    }

    $purposeoptions = \tool_dataprivacy\output\data_registry_page::purpose_options(
        \tool_dataprivacy\api::get_purposes()
    );
    $categoryoptions = \tool_dataprivacy\output\data_registry_page::category_options(
        \tool_dataprivacy\api::get_categories()
    );

    $customdata = [
        'contextlevel' => $contextlevel,
        'contextlevelname' => get_string('contextlevelname' . $contextlevel, 'tool_dataprivacy'),
        'persistent' => $persistent,
        'purposes' => $purposeoptions,
        'categories' => $categoryoptions,
    ];

    list($purposeid, $unused) = \tool_dataprivacy\api::get_effective_contextlevel_purpose_and_category($contextlevel);
    if ($purposeid) {

        $effectivepurpose = new \tool_dataprivacy\purpose($purposeid);

        $customdata['currentretentionperiod'] = tool_dataprivacy_get_retention_display_text($effectivepurpose, $contextlevel,
            \context_system::instance());

        $customdata['purposeretentionperiods'] = [];
        foreach ($purposeoptions as $optionvalue => $unused) {

            // Get the effective purpose if $optionvalue would be the selected value.
            list($purposeid, $unused) = \tool_dataprivacy\api::get_effective_contextlevel_purpose_and_category($contextlevel,
                $optionvalue);
            $purpose = new \tool_dataprivacy\purpose($purposeid);

            $retentionperiod = tool_dataprivacy_get_retention_display_text(
                $purpose,
                $contextlevel,
                \context_system::instance()
            );
            $customdata['purposeretentionperiods'][$optionvalue] = $retentionperiod;
        }
    }

    return $customdata;
}

function tool_dataprivacy_get_retention_display_text(\tool_dataprivacy\purpose $effectivepurpose, $retentioncontextlevel, \context $context) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('tool_dataprivacy');

    $exporter = new \tool_dataprivacy\external\purpose_exporter($effectivepurpose, ['context' => $context]);
    $exportedpurpose = $exporter->export($renderer);
    if ($retentioncontextlevel >= CONTEXT_COURSE) {
        return get_string('effectiveretentionperiodcourse', 'tool_dataprivacy',
            $exportedpurpose->formattedretentionperiod);
    }
    return $exportedpurpose->formattedretentionperiod;
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

/**
 * Returns the default purpose id and category id for the provided context level.
 *
 * The caller code is responsible of checking that $contextlevel is an integer.
 *
 * @param int $contextlevel
 * @return int[]
 */
function tool_dataprivacy_get_defaults($contextlevel) {

    $classname = \context_helper::get_class_for_level($contextlevel);
    list($purposevar, $categoryvar) = tool_dataprivacy_var_names_from_context($classname);

    $purposeid = get_config('tool_dataprivacy', $purposevar);
    $categoryid = get_config('tool_dataprivacy', $categoryvar);

    if (empty($purposeid)) {
        $purposeid = false;
    }
    if (empty($categoryid)) {
        $categoryid = false;
    }

    return [$purposeid, $categoryid];
}

/**
 * Serves any files associated with the data privacy settings.
 *
 * @param stdClass $course Course object
 * @param stdClass $cm Course module object
 * @param context $context Context
 * @param string $filearea File area for data privacy
 * @param array $args Arguments
 * @param bool $forcedownload If we are forcing the download
 * @param array $options More options
 * @return bool Returns false if we don't find a file.
 */
function tool_dataprivacy_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_USER) {
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/tool_dataprivacy/$filearea/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
        send_stored_file($file, 0, 0, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}
