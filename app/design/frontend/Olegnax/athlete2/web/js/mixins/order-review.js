/*
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    return function (orderReview) {
        $.widget('mage.orderReview', orderReview, {
            /**
             * hide ajax loader
             */
            _ajaxComplete: function () {
                this._super();
                $('body').trigger('contentUpdated');
            },
        });

        return $.mage.orderReview;
    }
});