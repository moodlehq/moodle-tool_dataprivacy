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
 * Page helper.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy;
defined('MOODLE_INTERNAL') || die();

/**
 * Page helper.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 David Monllao
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page_helper {

    public static function setup(\moodle_url $url, $title) {
        global $PAGE;

        $context = \context_system::instance();

        require_login();
        // TODO Check that data privacy is enabled.
        require_capability('tool/dataprivacy:managedataregistry', $context);

        $PAGE->navigation->override_active_url($url);

        $PAGE->set_url($url);
        $PAGE->set_context($context);
        $PAGE->set_pagelayout('admin');
        $PAGE->set_title($title);
        $PAGE->set_heading($title);

        $dataregistry = new \moodle_url('/admin/tool/dataprivacy/dataregistry.php');

        // No need to update data registry navbar as it is already part of the site navigation.
        if (!$url->compare($dataregistry)) {
            if ($siteadmin = $PAGE->settingsnav->find('root', \navigation_node::TYPE_SITE_ADMIN)) {
                $PAGE->navbar->add($siteadmin->get_content(), $siteadmin->action());
            }
            if ($dataprivacy = $PAGE->settingsnav->find('dataprivacysettings', \navigation_node::TYPE_SETTING)) {
                $PAGE->navbar->add($dataprivacy->get_content(), $dataprivacy->action());
            }
            if ($dataregistry = $PAGE->settingsnav->find('dataregistry', \navigation_node::TYPE_SETTING)) {
                $PAGE->navbar->add($dataregistry->get_content(), $dataregistry->action());
            }

            $PAGE->navbar->add($title, $url);
        }
    }
}
