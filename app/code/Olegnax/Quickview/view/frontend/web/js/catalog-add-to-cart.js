/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'catalogAddToCart',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
], function ($, idsResolver) {
    'use strict';

    $.widget('mage.OxQuickviewcatalogAddToCart', $.mage.catalogAddToCart, {
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

            $(window.parent.document.body).find(self.options.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);
            formData = new FormData(form[0]);

            window.parent.ajaxCartTransport = false;

            window.parent.jQuery.ajax({
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
                        $(window.parent.document.body).trigger(self.options.processStart);
                    }
                },

                /** @inheritdoc */
                success: function (res) {
                    var eventData, parameters;

                    $(window.parent.document).trigger('ajax:addToCart', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'form': form,
                        'response': res
                    });

                    if (self.isLoaderEnabled()) {
                        $(window.parent.document.body).trigger(self.options.processStop);
                    }

                    if (res.backUrl) {
                        eventData = {
                            'form': form,
                            'redirectParameters': []
                        };
                        // trigger global event, so other modules will be able add parameters to redirect url
                        $(window.parent.document.body).trigger('catalogCategoryAddToCartRedirect', eventData);

                        if (eventData.redirectParameters.length > 0) {
                            parameters = res.backUrl.split('#');
                            parameters.push(eventData.redirectParameters.join('&'));
                            res.backUrl = parameters.join('#');
                        }

                        self._redirect(res.backUrl);

                        return;
                    }

                    if (res.messages) {
                        $(window.parent.document.body).find(self.options.messagesSelector).html(res.messages);
                    }

                    if (res.minicart) {
                        $(window.parent.document.body).find(self.options.minicartSelector).replaceWith(res.minicart);
                        $(window.parent.document.body).find(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    if (res.product && res.product.statusText) {
                        $(window.parent.document.body).find(self.options.productStatusSelector)
                        .removeClass('available')
                        .addClass('unavailable')
                        .find('span')
                        .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form);

                    window.parent.ajaxCartTransport = !window.OXAjaxNShowMCart;
                },

                /** @inheritdoc */
                error: function (res) {
                    $(window.parent.document).trigger('ajax:addToCart:error', {
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
                        window.parent.location.reload();
                    }
                }
            });
        },
    });

    return $.mage.OxQuickviewcatalogAddToCart;
});
