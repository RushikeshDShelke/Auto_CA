define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'OXmodal'
], function ($) {
    'use strict';

    $.widget('mage.OXmodalMinicart', $.mage.OXmodal, {
        timerUH: null,
        /**
         * Extend default functionality to close the dropdown
         * with custom delay on mouse out and also to close when clicking outside
         */
        _create: function () {
            this._super();
            $(this.element).off('calculatedHeight').on('calculatedHeight', $.proxy(function (event, target, counter) {
                if (this._isOpening || this._isOpen) {
                    if (this.timerUH) {
                        clearTimeout(this.timerUH);
                    }
                    var _self = this,
                        _counter = 0;
                    this.timerUH = setTimeout(function () {
                        var height = 0,
                            outerHeight;
                        target.children().each(function () {

                            if ($(this).find('.options').length > 0) {
                                $(this).collapsible();
                            }
                            outerHeight = $(this).outerHeight();

                            _counter++;
                            if (counter > _counter) {
                                height += outerHeight;
                            }
                            self.scrollHeight += outerHeight;
                        });

                        target.parent().height(height);
                        _self._updateHeigh()
                    }, 300);
                }
                this._updateHeigh();
            }, this));
        },
        open: function (extra_class) {
            this._super(extra_class);
            this.updateHeigh();
        },
        close: function (forse) {
            this._super(forse);
            if (this.timerUH) {
                clearTimeout(this.timerUH);
            }
        },
        updateHeigh: function () {
            if (this.element.data('mageSidebar')) {
                this.element.sidebar('update');
            }
        },
        _updateHeigh: function () {
            var delta = this.uiDialog.offset().top - $(window).scrollTop() + this.uiDialog.outerHeight() - $(window).height(),
                $menu = $(this.element.data('mageSidebar').options.minicart.list, this.element).parent();
            if (0 < delta) {
                $menu.height($menu.height() - delta);
            }
        }
    });

    return $.mage.OXmodalMinicart;
});
