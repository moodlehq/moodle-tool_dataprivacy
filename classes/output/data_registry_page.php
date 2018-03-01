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
        $elements = [
            'text' => get_string('site'),
            'contextlevel' => CONTEXT_SYSTEM,
            'children' => [
                [
                    'text' => get_string('user'),
                    'contextlevel' => CONTEXT_USER,
                ], [
                    'text' => get_string('category'),
                    'contextlevel' => CONTEXT_COURSECAT,
                    'children' => [
                        [
                            'text' => get_string('course'),
                            'contextlevel' => CONTEXT_COURSE,
                        ]
                    ]
                ], [
                    'text' => get_string('activitymodule'),
                    'contextlevel' => CONTEXT_MODULE,
                ], [
                    'text' => get_string('block'),
                    'contextlevel' => CONTEXT_BLOCK,
                ]
            ]
        ];

        // Returned as an array to follow a common array format.
        return [$this->complete($elements)];
    }

    /**
     * Completes tree nodes with default values.
     *
     * @param array $node
     * @param int $counter
     * @return array
     */
    private function complete($node, $counter = 0) {
        if (!isset($node['active'])) {
            $node['active'] = $this->defaultcontextlevel == $node['contextlevel'] ? true : null;
        }
        if (!isset($node['children'])) {
            $node['children'] = null;
        } else {
            foreach ($node['children'] as $key => $childnode) {
                $node['children'][$key] = $this->complete($childnode, $counter + 1);
            }
        }
        $node['padding'] = $counter;

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
