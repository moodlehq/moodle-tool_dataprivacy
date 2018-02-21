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
 * This file contains the form add/update context instance data.
 *
 * @package   tool_dataprivacy
 * @copyright 2018 David Monllao
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\form;
defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

/**
 * Context instance data form.
 *
 * @package   tool_dataprivacy
 * @copyright 2018 David Monllao
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context_instance extends persistent {

    /**
     * @var The persistent class.
     */
    protected static $persistentclass = 'tool_dataprivacy\\context_instance';

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        $this->add_purpose_category();
        $this->add_action_buttons();
    }

    /**
     * Adds purpose and category selectors.
     *
     * @return null
     */
    protected function add_purpose_category() {
        $mform = $this->_form;

        $mform->addElement('select', 'purposeid', get_string('purpose', 'tool_dataprivacy'));
        $mform->setType('purposeid', PARAM_INT);
        $mform->addRule('purposeid', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'categoryid', get_string('category', 'tool_dataprivacy'));
        $mform->setType('categoryid', PARAM_INT);
        $mform->addRule('categoryid', get_string('required'), 'required', null, 'client');
    }

    /**
     * Adds action buttons.
     *
     * @return null
     */
    public function add_action_buttons() {
        if (!$this->get_persistent()->get('id')) {
            $savetext = get_string('add');
        } else {
            $savetext = get_string('savechanges');
        }
        parent::add_action_buttons(true, $savetext);
    }
}
