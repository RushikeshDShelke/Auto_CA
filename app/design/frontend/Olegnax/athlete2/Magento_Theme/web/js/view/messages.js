/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: [],
            timeout: 10,
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            this._super();

            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            // Force to clean obsolete messages
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');
        },
        updateHeight: function () {
            $('body').trigger('oxToggleUpdated');

            return true;
        },
        closeMessage: function () {
            $('[role="alert"]').on('click close.messages', '[data-action="close"]', function (event) {
                event.preventDefault();
                let $this = $(this).hide(),
                    $parent = $this.parent('[data-ui-id]').removeAttr('data-ui-id').removeAttr('class');
                $parent.find('[data-bind^="html"]').text('');
            });
            if ($('body').hasClass('ox-messages-fixed')) {
                if (this.timer) {
                    clearTimeout(this.timer);
                }
                this.timer = setTimeout(function () {
                    $('[role="alert"] [data-action="close"]').trigger('close.messages');
                }, this.timeout * 1000);
            }

            return true;
        }
    });
});
