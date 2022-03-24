/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    "jquery/hoverintent"
], function ($, mediaCheck) {
    'use strict';

    function is_touch_device() {
        return 'ontouchstart' in window        // works on most browsers
            || navigator.maxTouchPoints;       // works on IE10/11 and Surface
    };
    /**
     * @param {Object} options
     */
    $.fn.OXdropdown = function (options) {
        var defaults = {
                parent: null,
                autoclose: true,
                hover: true,
                btnArrow: '.arrow',
                menu: '[data-target="dropdown"]',
                activeClass: 'active',
                mediaBreakpoint: '(min-width: 1025px)'
            },
            actionElem = $(this),
            self = this;

        options = $.extend(defaults, options);
        actionElem = $(this);

        /**
         * @param {HTMLElement} elem
         */
        this.animateOpenDropdown = function (elem) {
            if (elem.hasClass(options.activeClass + '-pre'))
                return;
            let self = this;
            elem
            .addClass(options.activeClass + '-pre')
            .parent()
            .find(options.menu)
            .stop(true, true).animate({
                opacity: 1,
                height: 'toggle'
            }, 100, function () {
                self.openDropdown(elem.removeClass(options.activeClass + '-pre'));
            });
        };

        /**
         * @param {HTMLElement} elem
         */
        this.animateCloseDropdown = function (elem) {
            if (elem.hasClass(options.activeClass + '-after'))
                return;
            let self = this;
            elem
            .addClass(options.activeClass + '-after')
            .parent()
            .find(options.menu)
            .stop(true, true)
            .animate({
                opacity: 0,
                height: 'toggle'
            }, 100, function () {

                self.closeDropdown(elem.removeClass(options.activeClass + '-after'));
            });
        };

        /**
         * @param {HTMLElement} elem
         */
        this.openDropdown = function (elem) {
            let self = this;
            elem
            .addClass(options.activeClass)
            .attr('aria-expanded', true)
            .parent()
            .addClass(options.activeClass);

            elem.parent()
            .find(options.menu)
            .attr({
                'aria-hidden': false,
                'style': ''
            });

            $(options.btnArrow, elem).text('-');
        };

        /**
         * @param {HTMLElement} elem
         */
        this.closeDropdown = function (elem) {
            let self = this;
            elem.removeClass(options.activeClass)
            .attr('aria-expanded', false)
            .parent()
            .removeClass(options.activeClass);

            elem.parent()
            .find(options.menu)
            .attr('aria-hidden', true);

            $(options.btnArrow, elem).text('+');
        };

        /**
         * Reset all dropdowns.
         *
         * @param {Object} param
         */
        let _self = this;
        this.reset = function (param) {
            var params = param || {},
                dropdowns = params.elems || actionElem;

            dropdowns.each(function (index, elem) {
                _self.closeDropdown($(elem));
            });
        };
        /* document Event bindings */
        if (options.autoclose === true) {
            $(document).on('click.hideDropdown', this.reset);
            $(document).on('keyup.hideDropdown', function (e) {
                var ESC_CODE = '27';

                if (e.keyCode == ESC_CODE) { //eslint-disable-line eqeqeq
                    _self.reset();
                }
            });
        }

        if (options.events) {
            $.each(options.events, function (index, event) {
                $(document).on(event.name, event.selector, event.action);
            });
        }

        return this.each(function () {
            var elem = $(this),
                parent = $(options.parent).length > 0 ? $(options.parent) : elem.parent(),
                menu = $(options.menu, parent) || $('.dropdown-menu', parent);

            mediaCheck({
                media: options.mediaBreakpoint,
                exit: function () {
                    setTimeout(function () {
                        menu.attr('style', '');
                    }, 200)
                }
            });
            // ARIA (adding aria attributes)
            if (menu.length) {
                elem.attr('aria-haspopup', true);
            }

            if (!elem.hasClass(options.activeClass)) {
                elem.attr('aria-expanded', false);
                menu.attr('aria-hidden', true);
            } else {
                elem.attr('aria-expanded', true);
                menu.attr('aria-hidden', false);
            }

            if (!elem.is('a, button')) {
                elem.attr('role', 'button');
                elem.attr('tabindex', 0);
            }

            if (elem.attr('data-trigger-keypress-button')) {
                elem.on('keypress', function (e) {
                    var keyCode = e.keyCode || e.which,
                        ENTER_CODE = 13;

                    if (keyCode === ENTER_CODE) {
                        e.preventDefault();
                        elem.trigger('click.toggleDropdown');
                    }
                });
            }

            if (options.hover) {
                parent.hoverIntent({
                    over: function () {
                        if (!window.matchMedia(options.mediaBreakpoint).matches)
                            return false;
                        self.animateOpenDropdown(elem);

                    },
                    out: function () {
                        if (!window.matchMedia(options.mediaBreakpoint).matches)
                            return false;
                        self.animateCloseDropdown(elem);
                    },
                    timeout: 0,
                    interval: 10
                });
            }

            elem.off('click.toggleDropdown').on('click.toggleDropdown', function () {
                if (!window.matchMedia(options.mediaBreakpoint).matches && !$(this).hasClass('field-tooltip-action'))
                    return false;
                var el = actionElem;

                if (options.autoclose === true) {
                    actionElem = $();
                    $(document).trigger('click.hideDropdown');
                    actionElem = el;
                }

                self[el.hasClass(options.activeClass) ? 'animateCloseDropdown' : 'animateOpenDropdown'](elem);

                return false;
            });
        });
    };

    return function (data, el) {
        $(el).OXdropdown(data);
    };
});
