define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],function(Component,renderList){
    'use strict';
    renderList.push({
        type : 'lpcheckout',
        component : 'Billdesk_Bdcheckout/js/view/payment/method-renderer/bdcheckout-method'
    });

    return Component.extend({});
})
