define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'catalogAddToCart'
], function ($) {
    'use strict';

    $.widget('ox.loopOwlAddtocart', {
        options: {},
        _create: function () {
            this.element.find('.cloned [data-role=tocart-form]').catalogAddToCart();
            this.element.on('initialize.owl.carousel initialized.owl.carousel', function (e) {
                $(this).find('.cloned [data-role=tocart-form]').catalogAddToCart();
            });
        }
    });

    return $.ox.loopOwlAddtocart;
});
