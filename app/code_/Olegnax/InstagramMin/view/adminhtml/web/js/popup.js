define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict';
    $.widget('mage.OxPopup', {
        options: {
            url: '',
            windowName: '',
            windowFeatures: {
                width: 500,
                height: 500,
            },
            position: 'center',
            afterCloseModal: function () {
            },
        },
        _create: function () {
            this.options.windowFeatures = this._windowFeatures();

            this._on({
                'click': $.proxy(this._click, this),
            });
        },
        _click: function () {
            this.popupWindow = window.open(
                this.options.url,
                this.options.windowName,
                this.options.windowFeatures.toString()
            );
            this._updateState();
        },
        _updateState: function () {
            var self = this,
                timer = setInterval(function () {
                    if (self.popupWindow.closed) {
                        self.options.afterCloseModal();
                        self.afterCloseModal();
                        clearInterval(timer);
                    }
                }, 1000);
        },
        _preparePosition: function () {
            var position = (this.options.position + '').split('-');
            for (var i = 0; i < position.length; i++) {
                switch (position[i]) {
                    case 'left':
                    case 'top':
                        position[i] = 0;
                        break;
                    case 'center':
                    case 'middle':
                        position[i] = 0.5;
                        break;
                    case 'right':
                    case 'bottom':
                        position[i] = 1;
                        break;
                    default:
                        position[i] = parseInt(position[i]) / 100;
                }
            }
            if (1 == position.length) {
                position.push(position[0]);
            }

            return position;
        },
        _windowFeatures: function () {
            var position = this._preparePosition();
            if (this.options.hasOwnProperty('width') && !this.options.hasOwnProperty('left')) {
                this.options.windowFeatures.left = (window.screen.width - this.options.windowFeatures.width) * position[0];
            }
            if (this.options.hasOwnProperty('height') && !this.options.hasOwnProperty('top')) {
                this.options.windowFeatures.top = (window.screen.height - this.options.windowFeatures.height) * position[1];
            }

            var options = [];
            $.each(this.options.windowFeatures, function (index, element) {
                options.push(index + '=' + ("boolean" === typeof element ? (element ? '1' : '0') : element));
            });
            return options.join(',');
        },
        afterCloseModal: function () {
        }
    });
    return $.mage.OxPopup;
});
