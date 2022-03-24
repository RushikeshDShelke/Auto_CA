/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'Magento_Catalog/product/view/validation',
    'catalogAddToCart'
], function ($) {
    'use strict';

    $.widget('ox.AtProductValidate', {
        options: {
            bindSubmit: true,
			radioCheckboxClosest: '.nested',
			addToCartButtonSelector: '.action.tocart'
        },

        /**
         * Uses Magento's validation widget for the form object.
         * @private
         */
        _create: function () {
            var bindSubmit = this.options.bindSubmit;

            this.element.validation({
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                /**
                 * Uses catalogAddToCart widget as submit handler.
                 * @param {Object} form
                 * @returns {Boolean}
                 */
                submitHandler: function (form) {
                    var jqForm = $(form).catalogAddToCart({
                        bindSubmit: bindSubmit
                    });

                    jqForm.catalogAddToCart('submitForm', jqForm);

                    return false;
                }
            });
			$(this.options.addToCartButtonSelector).attr('disabled', false);
        }
    });

    return $.ox.AtProductValidate;
});
