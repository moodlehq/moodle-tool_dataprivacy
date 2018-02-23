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
 * Request actions.
 *
 * @module     tool_dataprivacy/data_request_modal
 * @package    tool_dataprivacy
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/notification', 'core/modal_factory', 'core/modal_events', 'core/fragment',
    'tool_dataprivacy/add_purpose', 'tool_dataprivacy/add_category'],
    function($, Str, Notification, ModalFactory, ModalEvents, Fragment, AddPurpose, AddCategory) {

        var DataRegistry = function(contextId) {
            this.contextId = contextId;
            this.init();
        };

        /**
         * @var {int} contextId
         * @private
         */
        DataRegistry.prototype.contextId = 0;

        DataRegistry.prototype.init = function() {
            AddPurpose.getModal(this.contextId);
            AddCategory.getModal(this.contextId);
        };

        return /** @alias module:tool_dataprivacy/data_registry */ {

            /**
             * Initialise the page.
             */
            init: function(contextId) {
                return new DataRegistry(contextId);
            }
        };
    }
);

