/**
 * Cminds Marketplace requirejs config.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
var config = {
    map: {
        '*': {
            'Magento_Checkout/js/view/cart/shipping-rates':'Cminds_Marketplace/js/view/cart/shipping-rates'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Cminds_Marketplace/js/view/shipping-mixin': true
            }
        }
    }
};