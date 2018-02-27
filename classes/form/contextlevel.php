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
 * This file contains the form add/update context level data.
 *
 * @package   tool_dataprivacy
 * @copyright 2018 David Monllao
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\form;
defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

/**
 * Context level data form.
 *
 * @package   tool_dataprivacy
 * @copyright 2018 David Monllao
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contextlevel extends context_instance {

    /**
     * @var The persistent class.
     */
    protected static $persistentclass = 'tool_dataprivacy\\contextlevel';

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {

        $this->_form->setDisableShortforms();

        $this->_form->addElement('header', 'contextlevelname', $this->_customdata['contextlevelname']);

        $this->add_purpose_category();

        // Add apply to all instances checkbox.
        $this->_form->addElement('advcheckbox', 'applyallinstances', get_string('applyallinstances', 'tool_dataprivacy'),
            get_string('applyallinstanceslabel', 'tool_dataprivacy'));

        $this->_form->addElement('hidden', 'contextlevel');
        $this->_form->setType('contextlevel', PARAM_INT);

        parent::add_action_buttons(false, get_string('savechanges'));
    }
}
