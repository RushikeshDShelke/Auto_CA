define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, placeOrderAction, selectPaymentMethodAction, customer, checkoutData, additionalValidators) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Indiaideas_Billdesk/payment/indiaideas'
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },
            afterPlaceOrder: function () {
                var url_params = "";
                var url = window.checkoutConfig.payment.billdesk.transactionUrl;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        url_params = JSON.parse(xhr.responseText);
                        console.log("URL : " + url);
                        console.log("URL Params : " + url_params.msg);
                        console.log(document.getElementById('post_form').action  +"\n"+ document.getElementById('post_params').value);
                        document.getElementById('post_form').action = url;
                        document.getElementById('post_params').value = url_params.msg;
                        document.getElementById('post_form').submit();
                    }
                };
                xhr.open('GET', window.checkoutConfig.payment.billdesk.ajaxController);
                xhr.send();
            }
        });
    }
);