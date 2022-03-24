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
            'jquery-plot':'Cminds_Supplierfrontendproductuploader/js/supplierfrontendproductuploader/plot/jquery.flot',
            'jquery-plot-time':'Cminds_Supplierfrontendproductuploader/js/supplierfrontendproductuploader/plot/jquery.flot.time'
        }
    },
    shim: {
        'jquery-plot': {
            deps: ['jquery']
        },
        'jquery-plot-time': {
            deps: ['jquery', 'jquery-plot']
        }
    }
};