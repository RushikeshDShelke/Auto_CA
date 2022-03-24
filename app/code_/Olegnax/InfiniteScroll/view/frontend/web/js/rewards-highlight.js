/**
 * Olegnax
 * Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 */
define([
    'jquery',
    'text!Amasty_Rewards/template/highlight.html',
    'mage/translate',
], function ($, template, $t) {
    'use strict';
    var xhr = {};

    return function (config) {
        $.each(config.components, function (index, config) {
            var item = $('[data-bind="scope: \'' + index + '\'"]'),
                loader = item.find('.amasty-rewards-loader');
            if (xhr.hasOwnProperty(config.productId)) {
                xhr[config.productId].abort();
            }
            loader.show();
            xhr[config.productId] = $.ajax({
                url: config.refreshUrl,
                data: JSON.stringify({
                    productId: this.productId,
                    attributes: $(this.formSelector).serialize()
                }),
                cache: true,
                method: 'post',
                dataType: 'json',
                contentType: "application/json",
                success: function (result) {
                    var c = '.amasty-rewards-highlight';
                    if (result) {
                        if (!item.find(c).length) {
                            loader.after(template);
                            item.find(c + ' .caption strong').prepend($t('Earn'));
                        }
                        item.find(c).toggle(result.visible).find('b[data-bind]').html(result.caption_text).css('color', result.caption_color);
                    }
                },
                complete: function () {
                    loader.hide();
                    delete xhr[config.productId];
                }
            });
        });
    };
});
