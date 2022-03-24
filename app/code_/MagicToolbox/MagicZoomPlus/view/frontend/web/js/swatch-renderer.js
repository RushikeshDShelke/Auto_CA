
define([
    'jquery',
    'MagicToolbox_MagicZoomPlus/js/swatch-renderer-mixin'
], function ($, mixin) {
    'use strict';

    return function (swatchRenderer) {
        /* NOTE: for Magento v2.0.0 - v2.0.10 */
        if (typeof(swatchRenderer) == 'undefined') {
            swatchRenderer = $.custom.SwatchRenderer;
        }
        /* NOTE: to skip multiple mixins */
        if (typeof(swatchRenderer.prototype.options.mtConfig) != 'undefined') {
            return swatchRenderer;
        }
        $.widget('mage.SwatchRenderer', swatchRenderer, mixin);
        return $.mage.SwatchRenderer;
    };
});
