'use strict';

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Cminds_Marketplace/js/model/shipping-methods',
    'Magento_Catalog/js/price-utils'
], function (
    $,
    quote,
    shippingMethods,
    priceUtils
) {

    return function (originalComponent) {
        return originalComponent.extend({
            defaults: {
                template: 'Cminds_Marketplace/shipping-methods'
            },

            vendorsProducts: shippingMethods.getViewModel(),
            shippingMethodsEnabled: window.checkoutConfig.shippingMethodsEnabled,
            nonSupplierShippingPrice: window.checkoutConfig.nonSupplierShippingPrice,
            canDisplaySupplierShippingMethods: window.checkoutConfig.shippingMethodsEnabled
                && window.checkoutConfig.shippingMethodsExist,

            initialize: function () {
                var countryId = '';
                $(document).on('change', "[name='country_id']", function () {
                    var countryId = $('[name="country_id"] > option:selected').val();
                    if (countryId == '') {
                        var countryId = quote.shippingAddress().countryId;
                    }
                    shippingMethods.getPruductsByVendors(quote.getItems(), countryId);
                });

                $(document).on('click', ".action-select-shipping-item", function () {
                    var countryId = quote.shippingAddress().countryId;
                    shippingMethods.getPruductsByVendors(quote.getItems(), countryId);
                    $('[name="country_id"] > option:selected').val(countryId);
                });

                this._super();

                return this;
            },

            validateShippingInformation: function() {
                var parentResult = this._super();

                if (!parentResult) {
                    return false;
                }

                var error_supplier_method = false;

                if ($('#s_method_supplier_supplier').is(':checked') || $('#s_method_supplier').is(':checked')) {
                    $('.supplier_methods').each(function() {
                        var name = $(this).attr('name');

                        if(!$('input[name='+name+']:checked').is(':checked')) {
                            error_supplier_method = true;
                        }

                    });

                }

                if (error_supplier_method) {
                    this.errorValidationMessage('Please specify shipping method for each supplier.');
                    return false;
                }

                return true;

            },

            setShippingPrice: function() {
                $.ajax({
                    url: window.checkoutConfig.baseUrl+'/marketplace/checkout/setshippingprice',
                    type: 'POST',
                    data: {
                        price: this.price,
                        method_id: this.id,
                        supplier_id: this.supplier_id,
                        currency_price: this.currency_price
                    },
                    dataType: 'json',
                    success: function (data) {
                        var price = priceUtils.formatPrice(
                            data.price_total.toFixed(2), window.checkoutConfig.priceFormat
                        );

                        $('#s_method_supplier_supplier')
                            .parent()
                            .next('td')
                            .html('<span><span class="text">'+ price +'</span></span>');

                        $('#s_method_supplier')
                            .parent()
                            .next('td')
                            .html('<span><span class="text">'+ price +'</span></span>');
                    }
                });

                return true;
            },

        });
    };
});
