/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            "jquery/hoverintent": "lib/jquery-hoverintent/jquery.hoverIntent.min",
            "OXmodal": "js/modal",
            "OXmodalMinicart": "js/modal-minicart",
            "OXmodalWishlist": "js/modal-wishlist",
            "OXmodalPhotoswipe": "js/modal-photoswipe",
            "mobileMenu": "js/mobile-menu",
            "Athlete2/modal": "js/modal",
            'AtloopOwlAddtocart': 'js/loopaddtocart-owl.carousel',
            "AtProductValidate": 'js/validate-product',            
            "OXExpand": "js/expand",
            "ox-product": "js/ox-product",
            "ox-catalog": "js/ox-catalog",
            "oxslide": "Magento_Bundle/js/oxslide",
            "sticky-sidebar": "js/sticky-sidebar",
            "ox-video": "js/ox-video",
            "OXmobileNoSlider": "js/mobile-noslider",
            "OXsticky": "js/sticky",
            "photoswipe": "lib/photoswipe/photoswipe",
            "photoswipe-ui": "lib/photoswipe/photoswipe-ui-default",
            "photoswipe-init":  "js/photoswipe"
        }
    },
    paths:{},
    shim:{},
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'js/mixins/catalog-add-to-cart': true
            },
            'Cynoinfotech_FreeShippingMessage/js/catalog-add-to-cart': {
                'js/mixins/catalog-add-to-cart-CFSM': true
            },
            'Magento_Paypal/js/order-review': {
                'js/mixins/order-review': true
            }
        }
    }
};

if(OX_OWL_DISABLE){
    delete config.map['*']['AtloopOwlAddtocart'];
}
if(OX_WAYPOINTS){
    config.paths['waypoints'] = "js/waypoints";
    config.shim['js/waypoints'] = ["jquery"];
    config.map['*']['waypoints'] = "js/waypoints";
    config.map['*']['ox-waypoints-init'] = "js/waypoints-init";
}