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
use tool_dataprivacy\external\purpose_exporter;
use tool_dataprivacy\external\category_exporter;

/**
 * Class containing the data registry setup renderable
 *
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_registry_setup_page implements renderable, templatable {

    /** @var array $categories All system categories. */
    protected $categories = [];

    /** @var array $purposes All system purposes. */
    protected $purposes = [];

    /**
     * Construct this renderable.
     *
     * @param \tool_dataprivacy\category[] $categories
     * @param \tool_dataprivacy\purpose[] $purposes
     */
    public function __construct($categories, $purposes) {
        $this->categories = $categories;
        $this->purposes = $purposes;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $context = \context_system::instance();

        $PAGE->requires->js_call_amd('tool_dataprivacy/categoriesactions', 'init');
        $PAGE->requires->js_call_amd('tool_dataprivacy/add_category', 'getModal', [$context->id]);

        $PAGE->requires->js_call_amd('tool_dataprivacy/purposesactions', 'init');
        $PAGE->requires->js_call_amd('tool_dataprivacy/add_purpose', 'getModal', [$context->id]);

        $data = new stdClass();

        // Navigation links.
        $data->navigation = [];
        $back = new \single_button(
            new \moodle_url('/admin/tool/dataprivacy/dataregistry.php'),
            get_string('back'),
            'get'
        );
        $data->navigation[] = $output->render($back);
        $defaults = new \single_button(
            new \moodle_url('/admin/tool/dataprivacy/defaults.php'),
            get_string('setdefaults', 'tool_dataprivacy'),
            'get'
        );
        $data->navigation[] = $output->render($defaults);

        $data->purposes = [];
        foreach ($this->purposes as $purpose) {
            $exporter = new purpose_exporter($purpose, ['context' => \context_system::instance()]);
            $purpose = $exporter->export($output);

            $actionmenu = $this->action_menu('purpose', $purpose->id, $purpose->name);
            $purpose->actions = $actionmenu->export_for_template($output);
            $data->purposes[] = $purpose;
        }

        $data->categories = [];
        foreach ($this->categories as $category) {
            $exporter = new category_exporter($category, ['context' => \context_system::instance()]);
            $category = $exporter->export($output);

            $actionmenu = $this->action_menu('category', $category->id, $category->name);
            $category->actions = $actionmenu->export_for_template($output);
            $data->categories[] = $category;
        }

        return $data;
    }

    /**
     * Adds an action menu for the provided element
     *
     * @param string $elementname 'purpose' or 'category'
     * @param int $id
     * @param string $name
     * @return null
     */
    private function action_menu($elementname, $id, $name) {

        // Actions.
        $actionsmenu = new \action_menu();
        $actionsmenu->set_menu_trigger(get_string('actions'));
        $actionsmenu->set_owner_selector($elementname . '-' . $id . '-actions');
        $actionsmenu->set_alignment(\action_menu::TL, \action_menu::BL);

        $url = new \moodle_url('/admin/tool/dataprivacy/edit' . $elementname . '.php',
            ['id' => $id]);
        $link = new \action_menu_link_secondary($url, new \pix_icon('t/edit',
            get_string('edit')), get_string('edit'));
        $actionsmenu->add($link);

        $url = new \moodle_url('#');
        $attrs = ['data-id' => $id, 'data-action' => 'delete' . $elementname, 'data-name' => $name];
        $link = new \action_menu_link_secondary($url, new \pix_icon('t/delete',
            get_string('delete')), get_string('delete'), $attrs);
        $actionsmenu->add($link);

        return $actionsmenu;
    }
}
