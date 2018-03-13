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

        $this->_form->addElement('header', 'contextname', $this->_customdata['contextname']);

        $subjectscope = implode(', ', $this->_customdata['subjectscope']);
        if (empty($subjectscope)) {
            $subjectscope = get_string('noassignedroles', 'tool_dataprivacy');
        }
        $this->_form->addElement('static', 'subjectscope', get_string('subjectscope', 'tool_dataprivacy'), $subjectscope);

        $this->add_purpose_category($this->_customdata['context']->contextlevel);

        $this->_form->addElement('hidden', 'contextid');
        $this->_form->setType('contextid', PARAM_INT);

        parent::add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Adds purpose and category selectors.
     *
     * @param int $contextlevel Apply this context level defaults. False for no defaults.
     * @return null
     */
    protected function add_purpose_category($contextlevel = false) {

        $mform = $this->_form;

        $addcategorytext = $this->get_add_element_content(get_string('addcategory', 'tool_dataprivacy'));
        $categoryselect = $mform->createElement('select', 'categoryid', null, $this->_customdata['categories']);
        $addcategory = $mform->createElement('button', 'addcategory', $addcategorytext, ['data-add-element' => 'category']);
        $mform->addElement('group', 'categorygroup', get_string('category', 'tool_dataprivacy'),
            [$categoryselect, $addcategory], null, false);
        $mform->setType('categoryid', PARAM_INT);

        $addpurposetext = $this->get_add_element_content(get_string('addpurpose', 'tool_dataprivacy'));
        $purposeselect = $mform->createElement('select', 'purposeid', null, $this->_customdata['purposes']);
        $addpurpose = $mform->createElement('button', 'addpurpose', $addpurposetext, ['data-add-element' => 'purpose']);
        $mform->addElement('group', 'purposegroup', get_string('purpose', 'tool_dataprivacy'),
            [$purposeselect, $addpurpose], null, false);
        $mform->setType('purposeid', PARAM_INT);
    }

    /**
     * Returns the 'add' label.
     *
     * It depends on the theme in use.
     *
     * @param string $label
     * @return \renderable|string
     */
    private function get_add_element_content($label) {
        global $PAGE, $OUTPUT;

        $bs4 = false;

        $theme = $PAGE->theme;
        if ($theme->name === 'boost') {
            $bs4 = true;
        } else {
            foreach ($theme->parents as $basetheme) {
                if ($basetheme === 'boost') {
                    $bs4 = true;
                }
            }
        }

        if (!$bs4) {
            return $label;
        }
        return $OUTPUT->pix_icon('e/insert', $label);
    }
}
