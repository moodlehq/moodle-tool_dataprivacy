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
 * Data registry renderable.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;

require_once($CFG->dirroot . '/lib/coursecatlib.php');

/**
 * Class containing the data registry renderable
 *
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_registry_page implements renderable, templatable {

    /**
     * @var int
     */
    private $defaultcontextlevel;

    /**
     * @var int
     */
    private $defaultcontextid;

    /**
     * Constructor.
     *
     * @param int $defaultcontextlevel
     * @param int $defaultcontextid
     * @return null
     */
    public function __construct($defaultcontextlevel = false, $defaultcontextid = false) {
        $this->defaultcontextlevel = $defaultcontextlevel;
        $this->defaultcontextid = $defaultcontextid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $params = [\context_system::instance()->id, $this->defaultcontextlevel, $this->defaultcontextid];
        $PAGE->requires->js_call_amd('tool_dataprivacy/data_registry', 'init', $params);

        $data = new stdClass();

        $defaultsbutton = new \single_button(
            new \moodle_url('/admin/tool/dataprivacy/defaults.php'),
            get_string('setdefaults', 'tool_dataprivacy'),
            'get'
        );
        $data->defaultsbutton = $defaultsbutton->export_for_template($output);

        $data->categoriesurl = new \moodle_url('/admin/tool/dataprivacy/categories.php');
        $data->purposesurl = new \moodle_url('/admin/tool/dataprivacy/purposes.php');

        $data->contextlevels = $this->get_tree_structure();

        return $data;
    }

    /**
     * Returns the tree default structure.
     *
     * @return array
     */
    private function get_tree_structure() {

        // They come sorted by depth ASC.
        $categories = \coursecat::get_all(['returnhidden' => true]);

        $categoriesbranch = [];
        while (count($categories) > 0) {
            foreach ($categories as $key => $category) {

                $newnode = [
                    'text' => $category->name,
                    'categoryid' => $category->id,
                    'contextid' => \context_coursecat::instance($category->id)->id,
                ];
                if ($category->coursecount > 0) {
                    $newnode['children'] = [
                        [
                            'text' => get_string('courses'),
                            'categoryid' => $category->id,
                            'expanded' => true
                        ]
                    ];
                }

                $added = false;
                if ($category->parent == 0) {
                    // New categories root-level node.
                    $categoriesbranch[] = $newnode;
                    $added = true;

                } else {
                    // Add the new node under the appropriate parent.
                    if ($this->add_to_parent_category_branch($category, $newnode, $categoriesbranch)) {
                        $added = true;
                    }
                }

                if ($added) {
                    unset($categories[$key]);
                }
            }
        }

        $elements = [
            'text' => get_string('contextlevelname' . CONTEXT_SYSTEM, 'tool_dataprivacy'),
            'contextlevel' => CONTEXT_SYSTEM,
            'children' => [
                [
                    'text' => get_string('contextlevelname' . CONTEXT_USER, 'tool_dataprivacy'),
                    'contextlevel' => CONTEXT_USER,
                ], [
                    'text' => get_string('contextlevelname' . CONTEXT_COURSECAT, 'tool_dataprivacy'),
                    'contextlevel' => CONTEXT_COURSECAT,
                    'children' => $categoriesbranch,
                ], [
                    'text' => get_string('contextlevelname' . CONTEXT_COURSE, 'tool_dataprivacy'),
                    'contextlevel' => CONTEXT_COURSE,
                ], [
                    'text' => get_string('contextlevelname' . CONTEXT_MODULE, 'tool_dataprivacy'),
                    'contextlevel' => CONTEXT_MODULE,
                ], [
                    'text' => get_string('contextlevelname' . CONTEXT_BLOCK, 'tool_dataprivacy'),
                    'contextlevel' => CONTEXT_BLOCK,
                ]
            ]
        ];

        // Returned as an array to follow a common array format.
        return [$this->complete($elements)];
    }

    private function add_to_parent_category_branch($category, $newnode, &$categoriesbranch) {

        foreach ($categoriesbranch as $key => $branch) {
            if (!empty($branch['categoryid']) && $branch['categoryid'] == $category->parent) {
                // It may be empty (if it does not contain courses and this is the first child cat).
                if (!isset($categoriesbranch[$key]['children'])) {
                    $categoriesbranch[$key]['children'] = [];
                }
                $categoriesbranch[$key]['children'][] = $newnode;
                return true;
            }
            if (!empty($branch['children'])) {
                $parent = $this->add_to_parent_category_branch($category, $newnode, $categoriesbranch[$key]['children']);
                if ($parent) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Completes tree nodes with default values.
     *
     * @param array $node
     * @return array
     */
    private function complete($node) {
        if (!isset($node['active'])) {
            if ($this->defaultcontextlevel && !empty($node['contextlevel']) &&
                    $this->defaultcontextlevel == $node['contextlevel'] &&
                    empty($this->defaultcontextid)) {
                // This is the active context level, we also checked that there
                // is no default contextid set.
                $node['active'] = true;
            } else if ($this->defaultcontextid && !empty($node['contextid']) &&
                    $this->defaultcontextid == $node['contextid']) {
                $node['active'] = true;
            } else {
                $node['active'] = null;
            }
        }

        if (!isset($node['children'])) {
            $node['children'] = null;
        } else {
            foreach ($node['children'] as $key => $childnode) {
                $node['children'][$key] = $this->complete($childnode);
            }
        }

        if (!isset($node['contextid'])) {
            $node['contextid'] = null;
        }

        if (!isset($node['contextlevel'])) {
            $node['contextlevel'] = null;
        }

        if (!empty($node['children'])) {
            $node['expandable'] = 1;
        } else if (empty($node['expandable'])) {
            // Apply a default value.
            $node['expandable'] = 0;
        }

        if (empty($node['expanded'])) {
            $node['expanded'] = null;
        }

        return $node;
    }

    /**
     * From a list of purpose persistents to a list of id => name purposes.
     *
     * @param \tool_dataprivacy\purpose $purposes
     * @return string[]
     */
    public static function purpose_options($purposes) {
        $options = [0 => get_string('notset', 'tool_dataprivacy')];
        foreach ($purposes as $purpose) {
            $options[$purpose->get('id')] = $purpose->get('name');
        }

        return $options;
    }

    /**
     * From a list of category persistents to a list of id => name categories.
     *
     * @param \tool_dataprivacy\category $categories
     * @return string[]
     */
    public static function category_options($categories) {
        $options = [0 => get_string('notset', 'tool_dataprivacy')];
        foreach ($categories as $category) {
            $options[$category->get('id')] = $category->get('name');
        }

        return $options;
    }
}
