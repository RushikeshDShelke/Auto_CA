/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'sticky-sidebar',
    'jquery-ui-modules/widget',
    'slide'
], function ($, stickySidebar) {
    'use strict';

    $.widget('mage.oxslide', $.mage.slide, {
        options: {
            "stickyWrapper": "",
            "stickyItem": ""
        },
        /**
         * @private
         */
        _show: function () {
            $(this.options.bundleOptionsContainer).slideDown(800, $.proxy(function () {
                if (this.options.stickyWrapper && this.options.stickyItem && !this.stickyinited) {
                    stickySidebar({
                        wrapper: this.options.stickyWrapper,
                        sticky: this.options.stickyItem
                    });
                    this.stickyinited = 1;
                }
            }, this));
            var topOffset = $(this.options.bundleOptionsContainer).offset().top,
                $ox = $(".ox-sticky");
            topOffset -= ($ox.length > 0 ? $ox.height() : 0) + 40;
            $('html, body').animate({
                scrollTop: topOffset
            }, 600);
            $('#product-options-wrapper > fieldset').focus();
        },
    });

    return $.mage.oxslide;
});
