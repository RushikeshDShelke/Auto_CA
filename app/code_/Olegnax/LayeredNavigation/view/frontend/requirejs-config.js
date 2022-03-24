var config = {
    map: {
        '*': {
            'OXPriceSlider': 'Olegnax_LayeredNavigation/js/price-slider',
            'wNumb': 'Olegnax_LayeredNavigation/js/wNumb.min',
            'OXAjaxNav': 'Olegnax_LayeredNavigation/js/ajax-nav',
            'oxFormValues': 'Olegnax_LayeredNavigation/js/form-values',
            'noUiSlider': 'Olegnax_LayeredNavigation/js/nouislider.min',
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Olegnax_LayeredNavigation/js/swatch-renderer': true
            }
        }
    }
};