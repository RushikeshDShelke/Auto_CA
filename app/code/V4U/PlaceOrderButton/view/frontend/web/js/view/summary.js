define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/summary',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(
        $,
        ko,
        Component,
        stepNavigator
    ) {
        'use strict';

        return Component.extend({

            isVisible: function () {
                return stepNavigator.isProcessed('shipping');
            },
            initialize: function () {
                $(function() {
                    $('body').on("click", '#place-order-trigger', function () {
                        $(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
                    });

                    $('body').on("click", 'input.radio', function () {
                        $("body").find("#place-order-trigger-wrapper").show();                        
                        
                        var paymemtmethodname =  $(this).parent().next(".payment-method-content").find(".action.primary.checkout span").text();
                        $("body").find("#place-order-trigger-wrapper span").text(paymemtmethodname);                        
                    });
                    
                    if ($(".payment-method").hasClass("_active")) {
                        $("body").find("#place-order-trigger-wrapper").show();
                      }
                      
                });
                var self = this;
                this._super();
            }

        });
    }
);