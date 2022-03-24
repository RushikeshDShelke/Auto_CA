define(
    [
        'jquery',
        'uiComponent'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ulmod_OrderComment/checkout/order-comment-block'
            }
        });
    }
);
