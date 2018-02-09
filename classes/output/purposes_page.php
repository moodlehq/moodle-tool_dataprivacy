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
 * Class containing data for the purposes page
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use tool_dataprivacy\external\purpose_exporter;

/**
 * Class containing data for the purposes page
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purposes_page implements renderable, templatable {

    /** @var array $purposes All system purposes. */
    protected $purposes = [];

    /**
     * Construct this renderable.
     *
     * @param \tool_dataprivacy\purpose[]
     */
    public function __construct($purposes) {
        $this->purposes = $purposes;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $addpurposebutton = new \single_button(
            new \moodle_url('/admin/tool/dataprivacy/editpurpose.php'),
            get_string('addpurpose', 'tool_dataprivacy'),
            'get'
        );
        $data->actions = [$output->render($addpurposebutton)];

        $data->purposes = [];
        foreach ($this->purposes as $purpose) {
            $exporter = new purpose_exporter($purpose, ['context' => \context_system::instance()]);
            $purpose = $exporter->export($output);

            $purpose->actions = $this->purpose_actions_menu($purpose->id, $purpose->name)->export_for_template($output);
            $data->purposes[] = $purpose;
        }

        if (!empty($data->purposes)) {
            $data->purposesexist = true;
        }

        return $data;
    }

    /**
     * Purpose actions menu.
     *
     * @param int $id
     * @param string $name
     * @return null
     */
    private function purpose_actions_menu($id, $name) {

        // Actions.
        $actionsmenu = new \action_menu();
        $actionsmenu->set_menu_trigger(get_string('actions'));
        $actionsmenu->set_owner_selector('purpose-' . $id . '-actions');
        $actionsmenu->set_alignment(\action_menu::TL, \action_menu::BL);

        $url = new \moodle_url('/admin/tool/dataprivacy/editpurpose.php',
            ['id' => $id]);
        $link = new \action_menu_link_secondary($url, new \pix_icon('t/edit',
            get_string('edit')), get_string('edit'));
        $actionsmenu->add($link);

        $url = new \moodle_url('#');
        $attrs = ['data-purposeid' => $id, 'data-action' => 'delete', 'data-purposename' => $name];
        $link = new \action_menu_link_secondary($url, new \pix_icon('t/delete',
            get_string('delete')), get_string('delete'), $attrs);
        $actionsmenu->add($link);

        return $actionsmenu;
    }
}
