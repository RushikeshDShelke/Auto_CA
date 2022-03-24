define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _RenderControls: function () {
                var container = this.element,
                    classes = this.options.classes;
                if (container.find('.' + classes.attributeClass).length) {
                    return;
                }
                return this._super();
            }
        });

        return $.mage.SwatchRenderer;
    }
});
