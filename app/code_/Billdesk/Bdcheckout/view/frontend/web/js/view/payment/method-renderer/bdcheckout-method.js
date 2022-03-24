define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Billdesk_Bdcheckout/js/action/set-payment-method',
    ],
    function(Component,setPaymentMethod){
    'use strict';

    return Component.extend({
        defaults:{
            'template':'Billdesk_Bdcheckout/payment/bdcheckout'
        },
        redirectAfterPlaceOrder: false,
        
        afterPlaceOrder: function () {
            setPaymentMethod();    
        }

    });
});
