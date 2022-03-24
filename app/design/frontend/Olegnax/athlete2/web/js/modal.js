define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'jquery-ui-modules/dialog',
    'matchMedia',
    // 'jquery/animation.transition.end',
], function ($) {
    'use strict';

    function debouncer(func, timeout) {
        var timeoutID,
            timeout = timeout || 500;
        return function () {
            var scope = this,
                args = arguments;
            clearTimeout(timeoutID);
            timeoutID = setTimeout(function () {
                func.apply(scope, Array.prototype.slice.call(args));
            }, timeout);
        }
    }

    $.widget('mage.OXmodal', $.ui.dialog, {
        options: {
            appendTo: '.ox-modals__holder',
            autoOpen: false,
            autoPosition: false,
            autoSize: false,
            htmlClass: 'ox-modal-showed',
            buttons: [],
            closeOnClickOutside: true,
            closeOnMouseLeave: false,
            closeButtonTrigger: null,
            createTitleBar: false,
            defaultModalClass: 'ox-dialog',
            draggable: false,
            hide: true,
            hoverOpen: false,
            minHeight: null,
            minWidth: null,
            modal: false,
            modalClass: 'active',
            modalCloseClass: 'close',
            modalContentClass: 'ox-modal-content',
            overlayClass: 'ox-modal-overlay',
            positionSlideout: null,
            resizable: false,
            timeout: null,
            triggerClass: 'active',
            triggerEvent: 'click',
            triggerTarget: null,
            type: 'dropdown',
            width: null,
            timeoutOpen: 200,
        },
        _isClosing: false,
        _isOpening: false,
        timer: null,
        _create: function () {
            var _self = this;
            switch (this.options.type) {
                case 'modal':
                    this.options.modal = true;
                    break;
                case 'overlay':
                case 'slideout':
                    break;
                case 'dropdown':
                default:
                    this.options.type = 'dropdown';
                    this.options.modal = false;
                    break;
            }
            this.extraClass = null;
            this._getScrollWidth();
            this._super();
            this._autoPositionSlideout();
            this.uiDialog.addClass(this.options.defaultModalClass).addClass('ox-' + this.options.type);
            if ('slideout' === this.options.type) {
                this.uiDialog.addClass('ox-' + this.options.type + '-' + this.options.positionSlideout);
            }
            if (_self.options.closeButtonTrigger) {
                this.element.on(_self.options.triggerEvent, _self.options.closeButtonTrigger, function () {
                    _self.close();
                });
            }
            this.element.show();
            _self.options._type = window.matchMedia('(min-width: 1025px)').matches ? _self.options.type : 'slideout';
            if ('dropdown' === _self.options.type) {
                $(window).on('resize', function () {
                    _self.options._type = window.matchMedia('(min-width: 1025px)').matches ? _self.options.type : 'slideout';
                    _self._dropdownPosition();
                });
            }
            if (_self.options.closeOnMouseLeave) {
                this._mouseEnter(_self.uiDialog);
                this._mouseLeave(_self.uiDialog);
                if (_self.options.triggerTarget) {
                    this._mouseLeave($(_self.options.triggerTarget));
                }
            }
            this.update();
            $('body').on('contentUpdated', $.proxy(_self.update, _self));
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
                    } else if (_self._isOpen + _self.options.timeoutOpen < new Date().getTime()) {
                        _self.close();
                    }
                });
                if (_self.options.hoverOpen) {
                    $(_self.options.triggerTarget).on('mouseenter', debouncer(function (event) {
                        if (_self.timer) {
                            clearTimeout(_self.timer);
                        }
                        if ('dropdown' === _self.options._type) {
                            event.stopPropagation();
                            _self.uiDialog.addClass('ox-' + _self.options.type + '-hover');
                            if (_self._isOpening || _self._isOpen) {
                                return;
                            }
                            _self.open();
                        }
                    }, 100));
                }
            }
        },
        _getScrollWidth: function () {
            var $tempElement = $('<div>').css({
                overflowY: 'scroll',
                width: '50px',
                height: '50px',
                visibility: 'hidden'
            });
            $('body').append($tempElement);
            this._scrollWidth = $tempElement[0].offsetWidth - $tempElement[0].clientWidth;
            $tempElement.remove();
        },
        _dropdownPosition: function () {
            var $triggerTarget = $(this.options.triggerTarget);
            if (window.matchMedia('(min-width: 1025px)').matches
                && $triggerTarget.length
                && ('body' === this.options.appendTo
                    || '.ox-modals__holder' === this.options.appendTo
                )
            ) {
                var top = $triggerTarget.height() + $triggerTarget.offset().top - $(window).scrollTop();
                var left = $triggerTarget.offset().left + $triggerTarget.outerWidth() - this.uiDialog.outerWidth() + .5;
                this.uiDialog.css({
                    top: top + 'px',
                    left: left + 'px'
                });
                return;
            }
            this.uiDialog.css({
                top: '',
                left: ''
            });
        },
        open: function (extra_class) {
            this.extraClass = extra_class || null;
            var _self = this;
            if (_self._isClosing || _self._isOpening || _self._isOpen) {
                return;
            }
            // _self.uiDialog.onAnimationStart(function () {
            //     _self._isOpening = true;
            // }, 'ox-modal-active');
            _self._isOpening = true;
            // _self.uiDialog.onAnimationEnd(function () {
            //     _self._isOpening = false;
            // }, 'ox-modal-active');
            setTimeout(function () {
                _self._isOpening = false;
            }, 200);
            var modalList = ['mageOXmodal', 'mageOXmodalMinicart', 'mageOXmodalWishlist'];
            $.each(_self.options.defaultModalClass.split(' ').filter(a => 0 < a.length), function (index, element) {
                $('.' + element + ' > .ui-dialog-content').not(_self.element).each(function () {
                    var $this = $(this);
                    for (var i = 0; i < modalList.length; i++)
                        if ('undefined' !== typeof $(this).data(modalList[i])) {
                            $(this).data(modalList[i]).close(1);
                            return;
                        }
                });
            });
            if ('dropdown' === this.options.type) {
                _self._dropdownPosition();
            }
            this._super();
            this._createOverlay();
            if (_self.options.modalContentClass) {
                _self.element.addClass(_self.options.modalContentClass);
            }
            if (_self.options.triggerClass) {
                $(_self.options.triggerTarget).addClass(_self.options.triggerClass);
            }
            if (_self.options.htmlClass && 'dropdown' !== _self.options._type) {
                var $html = $('html');
                $html.addClass(_self.options.htmlClass).addClass(_self.options.htmlClass + '-' + _self.options.type);
                if ('slideout' === _self.options._type) {
                    $html.addClass(_self.options.htmlClass + '-' + _self.options._type + '-' + _self.options.positionSlideout);
                    if ('right' === _self.options.positionSlideout) {
                        $html.find('.page-wrapper, .sticky .sticky-wrapper').css('padding-right', this._scrollWidth);
                    }
                }
            }
            if (_self.extraClass) {
                _self.element.addClass(_self.extraClass);
            }
            if (_self.options.modalCloseClass) {
                _self.uiDialog.removeClass(_self.options.modalCloseClass).removeClass('ox-' + this.options.type + '-' + _self.options.modalCloseClass);
            }
            if (_self.options.modalClass) {
                _self.uiDialog.addClass(_self.options.modalClass).addClass('ox-' + this.options.type + '-' + _self.options.modalClass);
            }
            if (_self.options.closeOnClickOutside) {
                $('body').on('click.outsideDropdown', function (event) {
                    if (_self._isOpen && !$(event.target).closest('.ui-dialog').length) {
                        if (_self.timer) {
                            clearTimeout(_self.timer);
                        }
                        _self.close();
                    }
                });
            }
            this._isOpen = new Date().getTime();
        },
        close: function (forse) {
            var forse = forse || false,
                _self = this,
                close_func = function () {
                    if (_self._isClosing) {
                        _self._isClosing = false;
                        if (_self.extraClass) {
                            _self.element.removeClass(_self.extraClass);
                            _self.extraClass = null;
                        }
                        if (_self.options.hide) {
                            _self.uiDialog.hide();
                        }
                    }
                };
            if (forse) {
                _self._isClosing = true;
                _self._isOpening = false;
                close_func();
            } else {
                if (_self._isOpening || _self._isClosing || !_self._isOpen) {
                    return;
                }
                // _self.uiDialog.onAnimationStart(function () {
                //     _self._isClosing = true;
                // }, 'ox-modal-close');
                _self._isClosing = true;
                // _self.uiDialog.onAnimationEnd(close_func, 'ox-modal-close');
                setTimeout(close_func, 200);
            }
            if (this.options.modalContentClass) {
                this.element.removeClass(this.options.modalContentClass);
            }
            if (_self.options.modalClass) {
                _self.uiDialog.removeClass(_self.options.modalClass).removeClass('ox-' + this.options.type + '-' + _self.options.modalClass);
            }
            if (_self.options.modalCloseClass) {
                _self.uiDialog.addClass(_self.options.modalCloseClass).addClass('ox-' + this.options.type + '-' + _self.options.modalCloseClass);
            }
            if (this.options.triggerClass) {
                $(this.options.triggerTarget).removeClass(this.options.triggerClass);
            }
            if (this.options.htmlClass) {
                var $html = $('html');
                $html.removeClass(this.options.htmlClass).removeClass(this.options.htmlClass + '-' + this.options.type).removeClass(_self.options.htmlClass + '-' + _self.options._type + '-' + _self.options.positionSlideout);
                if ('right' === _self.options.positionSlideout) {
                    $html.find('.page-wrapper, .sticky .sticky-wrapper').css('padding-right', '');
                }
            }
            if (_self.timer) {
                clearTimeout(_self.timer);
            }
            $('body').off('click.outsideDropdown');
            _self._isOpen = false;
        },
        _position: function () {
            if (this.options.autoPosition) {
                this._super();
            }
        },
        _createTitlebar: function () {
            if (this.options.createTitleBar) {
                this._super();
            } else {
                this.uiDialogTitlebarClose = $('<div>');
            }
        },
        _size: function () {
            if (this.options.autoSize) {
                this._super();
            }
        },
        _mouseLeave: function (handler) {
            var _self = this;
            handler.on('mouseleave', function (event) {
                event.stopPropagation();
                if (_self._isOpen && !_self._isOpening && 'dropdown' === _self.options._type) {
                    if (_self.timer) {
                        clearTimeout(_self.timer);
                    }
                    _self.timer = setTimeout(function () {
                        _self.close();
                    }, _self.options.timeout);
                }
            });
        },
        _mouseEnter: function (handler) {
            var _self = this;
            handler.on('mouseenter', function (event) {
                event.stopPropagation();
                if (!_self._isOpening) {
                    clearTimeout(_self.timer);
                }
            });
        },
        _setOption: function (key, value) {
            this._super(key, value);
            if (key === 'triggerTarget') {
                this.options.triggerTarget = value;
            }
        },
        _createOverlay: function () {
            if (!this.options.overlayClass) {
                return;
            }
            var isOpening = true;
            this._delay(function () {
                isOpening = false;
            });
            if (!this.overlay) {
                var $body = $('body');
                if ($body.children('.' + this.options.overlayClass).length) {
                    this.overlay = $body.children('.' + this.options.overlayClass).eq(0);
                } else {
                    this.overlay = $('<div>').addClass(this.options.overlayClass).appendTo($('body').eq(0));
                }
                this._on(this.overlay, {
                    mousedown: "_keepFocus"
                });
                if (this.options.closeOnClickOutside) {
                    this.overlay.on('click', $.proxy(function () {
                        if (this._isOpen) {
                            this.close();
                        }
                    }, this));
                }
            }
        },
        _destroyOverlay: function () {
            if (this.overlay) {
                this.overlay.remove();
                this.overlay = null;
            }
        },
        _autoPositionSlideout: function () {
            if ('slideout' !== this.options.type || this.options.positionSlideout) {
                return;
            }
            if (0 < $(this.options.triggerTarget).closest('.modal__right').length) {
                this.options.positionSlideout = 'right';
            } else if (0 < $(this.options.triggerTarget).closest('.modal__left').length) {
                this.options.positionSlideout = 'left';
            }
        },
        _focusTabbable: function () {
            var hasFocus = this._focusedElement;
            if (!hasFocus) {
                hasFocus = this.element.find("[autofocus]");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialogButtonPane.find(":tabbable");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialogTitlebarClose.filter(":tabbable");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialog;
            }
            hasFocus.eq(0).trigger("focus");
        }
    });
    return $.mage.OXmodal;
});
