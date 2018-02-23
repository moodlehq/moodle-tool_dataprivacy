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
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $PAGE->requires->js_call_amd('tool_dataprivacy/data_registry', 'init', [\context_system::instance()->id]);

        $data = new stdClass();

        $settingsbutton = new \single_button(
            new \moodle_url('/admin/tool/dataprivacy/dataregistrysetup.php'),
            get_string('settings'),
            'get'
        );
        $data->settingsbutton = $settingsbutton->export_for_template($output);

        return $data;
    }

    /**
     * Adds the add elements menu.
     *
     * @return \action_menu
     */
    private function add_menu() {

        // Actions.
        $actionsmenu = new \action_menu();
        $actionsmenu->set_menu_trigger(get_string('add'));
        $actionsmenu->set_owner_selector('add-actions');
        $actionsmenu->set_alignment(\action_menu::TR, \action_menu::TR);

        $url = new \moodle_url('#');
        $attrs = ['data-add-element' => 'purpose'];
        $link = new \action_menu_link_secondary($url, null, get_string('addpurpose', 'tool_dataprivacy'), $attrs);
        $actionsmenu->add($link);

        $url = new \moodle_url('#');
        $attrs = ['data-add-element' => 'category'];
        $link = new \action_menu_link_secondary($url, null, get_string('addcategory', 'tool_dataprivacy'), $attrs);
        $actionsmenu->add($link);

        return $actionsmenu;
    }
}
