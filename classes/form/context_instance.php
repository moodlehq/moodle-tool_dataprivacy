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
        $this->_form->setDisableShortforms();
        $this->add_purpose_category();
        parent::add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Adds purpose and category selectors.
     *
     * @return null
     */
    protected function add_purpose_category() {
        $mform = $this->_form;

        // Purpose options.
        $purposes = [];
        foreach ($this->_customdata['purposes'] as $purposeid => $purpose) {
            $purposes[$purposeid] = $purpose->get('name');
        }
        $mform->addElement('select', 'purposeid', get_string('purpose', 'tool_dataprivacy'), $purposes);
        $mform->setType('purposeid', PARAM_INT);
        $mform->addRule('purposeid', get_string('required'), 'required', null, 'client');

        // Category options.
        $categories = [];
        foreach ($this->_customdata['categories'] as $categoryid => $category) {
            $categories[$categoryid] = $category->get('name');
        }
        $mform->addElement('select', 'categoryid', get_string('category', 'tool_dataprivacy'), $categories);
        $mform->setType('categoryid', PARAM_INT);
        $mform->addRule('categoryid', get_string('required'), 'required', null, 'client');
    }
}
