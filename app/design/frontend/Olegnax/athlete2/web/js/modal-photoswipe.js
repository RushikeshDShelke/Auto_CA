define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'OXmodal'
], function ($) {
    'use strict';

    $.widget('mage.OXmodalPhotoswipe', $.mage.OXmodal, {

        _create: function () {
            this.modalPrefix = '#image-';
            this._super();
            let hash = this.__curentHashTag();
            if (0 == hash.indexOf(this.modalPrefix)) {
                let index = hash.replace(this.modalPrefix, '');
                if (0 < index) {
                    this.open();
                    this._scrollModal(index);
                }
            }
        },
        _updateHistory: function (id) {
            if (typeof history.replaceState === 'function') {
                history.replaceState(null, null, this._getPostUrl(id));
            }
        },
        __curentHashTag: function () {
            return (new URL(window.location.href)).hash.toString();
        },
        _getPostUrl: function (id) {
            return window.location.href.replace(this.__curentHashTag(), '') + (id ? this.modalPrefix + id : '');
        },
        _scrollModal: function (index) {
            let _gallery = this.element.find('.gallery'),
                image = _gallery.find(this.modalPrefix + index),
                _nav = this.element.find('.lil-nav'),
                nav_item = _nav.find('[href="#image-' + index + '"]');
            if (image.length) {
                this._updateHistory(index);
                setTimeout(() => {
                    let img_top = image[0].getBoundingClientRect().top,
                        nav_top = nav_item[0].getBoundingClientRect().top;
                    if (0 != img_top) {
                        _gallery.animate({scrollTop: _gallery.scrollTop() + img_top});
                    }
                    if (0 != nav_top) {
                        _nav.animate({scrollTop: _nav.scrollTop() + nav_top});
                    }
                }, 600);
            }
        },
        update: function () {
            var _self = this;
            if (_self.options.triggerTarget) {
                $(_self.options.triggerTarget).off(_self.options.triggerEvent).on(_self.options.triggerEvent, function (event) {
                    if (event.cancelable) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    if (!_self._isOpen) {
                        _self.open();
                        _self._scrollModal($(event.target).data('index')); // new code
                    } else if (_self._isOpen + _self.options.timeoutOpen < new Date().getTime()) {
                        _self.close();
                    }
                });
            }
        },
    });

    return $.mage.OXmodalPhotoswipe;
});
