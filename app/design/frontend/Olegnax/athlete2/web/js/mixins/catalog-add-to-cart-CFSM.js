define([
    'jquery',
    'Cynoinfotech_FreeShippingMessage/js/freeshippingmessage',
    'Magento_Catalog/js/product/view/product-ids-resolver'
], function ($, fsm, idsResolver) {
    return function (catalogAddToCart) {
        $.widget('mage.catalogAddToCart', catalogAddToCart, {
            options: {
                bindSubmit: true,
                selectorNewElements: "[data-role=tocart-form], .form.map.checkout"
            },
            _create: function () {
                this._super();
                if (!window.OXNAjaxCatalog) {
                    var __select = this.options.selectorNewElements;
                    $('body').off('contentUpdated.catalogAddToCart').on('contentUpdated.catalogAddToCart', function () {
                        $(__select).catalogAddToCart();
                    });
                }
            },
            _bindSubmit: function () {
                var self = this;

                if (this.element.data('catalog-addtocart-initialized')) {
                    return;
                }

                this.element.data('catalog-addtocart-initialized', 1);
                this.element.off('submit').on('submit', function (e) {
                    e.preventDefault();
                    self.submitForm($(this));
                });
            },

            disableAddToCartButton: function (form) {
                this.element.addClass('ox-add-to-cart-loading');
                this._super(form);
            },
            enableAddToCartButton: function (form) {
                this.element.removeClass('ox-add-to-cart-loading');
                this._super(form);
            },
            /**
             * @param {jQuery} form
             */
            ajaxSubmit: function (form) {
                var self = this,
                    productIds = idsResolver(form),
                    formData;

                $(self.options.minicartSelector).trigger('contentLoading');
                self.disableAddToCartButton(form);
                formData = new FormData(form[0]);

                window.ajaxCartTransport = false;
                $.ajax({
                    url: form.attr('action'),
                    data: formData,
                    type: 'post',
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,

                    /** @inheritdoc */
                    beforeSend: function () {
                        if (self.isLoaderEnabled()) {
                            $('body').trigger(self.options.processStart);
                        }
                    },

                    /** @inheritdoc */
                    success: function (res) {
                        var eventData, parameters;

                        $(document).trigger('ajax:addToCart', {
                            'sku': form.data().productSku,
                            'productIds': productIds,
                            'form': form,
                            'response': res
                        });

                        if (self.isLoaderEnabled()) {
                            $('body').trigger(self.options.processStop);
                        }

                        if (res.backUrl) {
                            eventData = {
                                'form': form,
                                'redirectParameters': []
                            };
                            // trigger global event, so other modules will be able add parameters to redirect url
                            $('body').trigger('catalogCategoryAddToCartRedirect', eventData);

                            if (eventData.redirectParameters.length > 0 &&
                                window.location.href.split(/[?#]/)[0] === res.backUrl
                            ) {
                                parameters = res.backUrl.split('#');
                                parameters.push(eventData.redirectParameters.join('&'));
                                res.backUrl = parameters.join('#');
                            }

                            self._redirect(res.backUrl);

                            return;
                        }

                        if (res.messages) {
                            $(self.options.messagesSelector).html(res.messages);
                        }

                        if (res.minicart) {
                            $(self.options.minicartSelector).replaceWith(res.minicart);
                            $(self.options.minicartSelector).trigger('contentUpdated');
                        }

                        if (res.product && res.product.statusText) {
                            $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                        }
                        self.enableAddToCartButton(form);
                        /*custom code here ajax_call_funtion call*/
                        fsm.ajax_call_funtion();
                        window.ajaxCartTransport = !window.OXAjaxNShowMCart;
                    },

                    /** @inheritdoc */
                    error: function (res) {
                        $(document).trigger('ajax:addToCart:error', {
                            'sku': form.data().productSku,
                            'productIds': productIds,
                            'form': form,
                            'response': res
                        });
                        self.enableAddToCartButton(form);
                    },

                    /** @inheritdoc */
                    complete: function (res) {
                        if (res.state() === 'rejected') {
                            location.reload();
                        }
                    }
                });
            }
        });

        return $.mage.catalogAddToCart;
    }
});