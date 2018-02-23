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
 * Renderer class for tool_dataprivacy
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\output;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use html_writer;
use moodle_exception;
use plugin_renderer_base;

/**
 * Renderer class for tool_dataprivacy.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the user's data requests page.
     *
     * @param my_data_requests_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_my_data_requests_page(my_data_requests_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_dataprivacy/my_data_requests', $data);
    }

    /**
     * Render the contact DPO link.
     *
     * @param string $replytoemail The Reply-to email address
     * @return string The HTML for the link.
     * @throws coding_exception
     */
    public function render_contact_dpo_link($replytoemail) {
        $params = [
            'data-action' => 'contactdpo',
            'data-replytoemail' => $replytoemail
        ];
        return html_writer::link('#', get_string('contactdataprotectionofficer', 'tool_dataprivacy'), $params);
    }

    /**
     * Render the data requests page for the DPO.
     *
     * @param data_requests_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_data_requests_page(data_requests_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_dataprivacy/data_requests', $data);
    }
}
