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
define(['jquery', 'core/str', 'core/ajax', 'core/notification', 'core/templates', 'core/modal_factory', 'core/modal_events', 'core/fragment',
    'tool_dataprivacy/add_purpose', 'tool_dataprivacy/add_category'],
    function($, Str, Ajax, Notification, Templates, ModalFactory, ModalEvents, Fragment, AddPurpose, AddCategory) {

        var SELECTORS = {
            TREE_NODES: '[data-context-tree-node=1]',
            FORM_CONTAINER: '#context-form-container',
        };

        var DataRegistry = function(systemContextId, initContextLevel) {
            this.systemContextId = systemContextId;
            this.currentContextLevel = initContextLevel;
            this.init();
        };

        /**
         * @var {int} systemContextId
         * @private
         */
        DataRegistry.prototype.systemContextId = 0;

        /**
         * @var {AddPurpose} addpurpose
         * @private
         */
        DataRegistry.prototype.addpurpose = null;

        /**
         * @var {AddCategory} addcategory
         * @private
         */
        DataRegistry.prototype.addcategory = null;

        DataRegistry.prototype.init = function() {
            // Add purpose and category modals always at system context.
            this.addpurpose = AddPurpose.getInstance(this.systemContextId);
            this.addcategory = AddCategory.getInstance(this.systemContextId);

            var stringKeys = [
                {
                    key: 'changessaved',
                    component: 'moodle'
                },
                {
                    key: 'contextpurposecategorysaved',
                    component: 'tool_dataprivacy'
                }
            ];
            this.strings = Str.get_strings(stringKeys);

            this.registerEventListeners();

            // Load the default context level form.
            if (this.currentContextLevel) {
                this.loadContextLevelForm();
            }
        };

        DataRegistry.prototype.registerEventListeners = function() {
            $(SELECTORS.TREE_NODES).on('click', function(ev) {

                var trigger = $(ev.currentTarget);

                // Active node.
                $(SELECTORS.TREE_NODES).removeClass('active');
                trigger.addClass('active');

                var contextLevel = trigger.attr('data-contextlevel');
                if (contextLevel) {

                    window.history.pushState({}, null, '?contextlevel=' + contextLevel);

                    // Remove previous add purpose and category listeners to avoid memory leaks.
                    this.addpurpose.removeListeners();
                    this.addcategory.removeListeners();

                    // Load the context level form.
                    this.currentContextLevel = contextLevel;
                    this.loadContextLevelForm();
                }
                // TODO Specific context ids.

            }.bind(this));
        };

        DataRegistry.prototype.loadContextLevelForm = function() {

            // For the previously loaded form.
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });

            // Remove previous listeners.
            $(SELECTORS.FORM_CONTAINER).off('submit', 'form');

            var fragment = Fragment.loadFragment('tool_dataprivacy', 'contextlevel_form', this.systemContextId, [this.currentContextLevel]);
            fragment.done(function(html, js) {
                $(SELECTORS.FORM_CONTAINER).html(html);
                Templates.runTemplateJS(js);

                this.addpurpose.registerEventListeners();
                this.addcategory.registerEventListeners();

                // We also catch the form submit event and use it to submit the form with ajax.
                $(SELECTORS.FORM_CONTAINER).on('submit', 'form', this.submitContextLevelFormAjax.bind(this));

            }.bind(this)).fail(Notification.exception);
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        DataRegistry.prototype.submitForm = function(e) {
            e.preventDefault();
            $(SELECTORS.FORM_CONTAINER).find('form').submit();
        };

        DataRegistry.prototype.submitContextLevelFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            // Convert all the form elements values to a serialised string.
            var formData = $(SELECTORS.FORM_CONTAINER).find('form').serialize();
            return this.strings.then(function(strings) {
                Ajax.call([{
                    methodname: 'tool_dataprivacy_set_contextlevel_form',
                    args: {jsonformdata: JSON.stringify(formData)},
                    done: function() {
                        Notification.alert(strings[0], strings[1]);
                    },
                    fail: Notification.exception
                }]);
            }.bind(this));
        };

        return /** @alias module:tool_dataprivacy/data_registry */ {

            /**
             * Initialise the page.
             */
            init: function(systemContextId, initContextLevel) {
                return new DataRegistry(systemContextId, initContextLevel);
            }
        };
    }
);

