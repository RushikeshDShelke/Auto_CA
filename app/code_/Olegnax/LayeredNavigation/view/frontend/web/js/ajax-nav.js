/**
 * Olegnax Layered Navigation
 * Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 */
define([
    'jquery',
    'Magento_Theme/js/view/messages',
    'ko',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'oxFormValues'
], function ($, messenger, ko) {
    'use strict';

    $.widget('mage.OXAjaxNav', {
        options: {
            filterNavInputClass: '.block.filter .filter-options-content .item input[name^=filter]',
            filterClearAllClass: '.block.filter .filter-actions a.action.filter-clear',
            filterCurrentClass: '.block.filter .filter-current .item a.action',
            filters: [
                '.block.filter .filter-options-content .item a',
                '.block.filter .swatch-attribute a'
            ],
            replaceSelectors: {
                leftnav: '.sidebar.sidebar-main .block.filter, [data-move-mobile="filtersslideout"] .block.filter.filters-slideout-content.ox-move-item',
                title: '.column.main .page-title-wrapper',
                breadcrumbs: '.column.main .breadcrumbs'
            },
            replaceContentSelectors: {
                products: '.column.main .search.results, .column.main'
            },
            clearUrl: null,
            timeout: 300,
            scrollToTop: true,
            loaderFilter: false,
            loaderContent: false,
            loaderPage: false,
            abortPrev: true,
            loaderContentSelector: '',
            selectedItemClass: 'ox-seleted-item'
        },
        /**
         * @private
         */
        _create: function () {
            this._loader_template_filter = '[data-role="ox-nav-loader-filter"]';
            this._loader_template_content = '[data-role="ox-nav-loader-content"]';
            this._loader_template_page = '[data-role="ox-nav-loader-page"]';
            this._loader_class_filter = '.ox-product-nav-filter';
            this._loader_class_content = '.ox-product-nav-content';
            this._loader_class_page = '.ox-product-nav-page';
            this._disable_class = 'ox-disable-ajax';
            this._current_count = '.filter-current-count';
            this._current_item = '.filter-current .item';
            this._use_url_class = 'ox-nav-use-url';
            this.timeoutID = null;

            if (!this.options.clearUrl) {
                this.options.clearUrl = window.location.href;
            }
            this.afterUpdate();
            this._event();
        },
        /**
         * Set events
         * @private
         */
        _event: function () {
            var _self = this;
            this.element.off('filter_apply.OXAjaxNav').on('filter_apply.OXAjaxNav', $.proxy(function (event, values, url) {
                var _url = url || this.options.clearUrl;
                this.getDebounceContent(_url, values);
            }, this));
            this.element.off('change.OXAjaxNav', this.options.filterNavInputClass).on('change.OXAjaxNav', this.options.filterNavInputClass, $.proxy(function (event) {
                var $this = $(event.currentTarget),
                    values = _self.getAllFilter(),
                    url;
                if ($this.hasClass(this._use_url_class)) {
                    url = window.location.href;
                }
                $this.closest('li').eq(0).toggleClass(this.options.selectedItemClass, $this.prop('checked'));
                _self.element.trigger('filter_apply.OXAjaxNav', [values, url]);
            }, this));
            this.element.off('click.OXAjaxNav', this.options.filters.join(', ')).on('click.OXAjaxNav', this.options.filters.join(', '), $.proxy(function (event) {
                var $this = $(event.currentTarget).addClass(this.options.selectedItemClass),
                    url = $this.attr('href');

                if (url && !$this.hasClass(this._disable_class)) {
                    event.preventDefault();
                    var values = this._parseQueryString(url);
                    _self.element.trigger('filter_apply.OXAjaxNav', values);
                }
            }, this));
            this.element.off('click.OXAjaxNav', this.options.filterCurrentClass).on('click.OXAjaxNav', this.options.filterCurrentClass, $.proxy(function (event) {
                var $this = $(event.currentTarget),
                    url = $this.attr('href');

                if (url && !$this.hasClass(this._disable_class)) {
                    event.preventDefault();
                    var values = this._parseQueryString(url);
                    this.element.trigger('filter_apply.OXAjaxNav', values, url);
                }
            }, this));
            this.element.off('click.OXAjaxNav', this.options.filterClearAllClass).on('click.OXAjaxNav', this.options.filterClearAllClass, $.proxy(function (event) {
                var $this = $(event.currentTarget),
                    url = $this.attr('href');

                if (url && !$this.hasClass(this._disable_class)) {
                    event.preventDefault();
                    this.element.trigger('filter_apply.OXAjaxNav', null, url);
                }
            }, this));
        },
        /**
         * Update url current page
         * @param string url
         * @param object data
         */
        updateUrl: function (url, data) {
            var _url = url || this.options.clearUrl,
                _data = data || {};

            if (data.hasOwnProperty('navAjax')) {
                delete data.navAjax;
            }
            var new_url = new URL(_url);
            $.each(_data, function (i, v) {
                if ($.isArray(v)) {
                    new_url.searchParams.set(i, v.join(','));
                } else {
                    new_url.searchParams.set(i, v);
                }
            });

            if (typeof history.replaceState === 'function') {
                history.replaceState(null, null, new_url.toString());
            }
            return new_url.toString();
        },
        /**
         * Get url attributes
         * @param string url
         * @return string
         * @private
         */
        _parseQueryString: function (url) {
            return $.getURLValues(url);
        },
        getAllFilter: function () {
            var values = this.element.find(this.options.filterNavInputClass).getFormValues();
            if (values.hasOwnProperty('filter')) {
                values = values.filter;
                $.each(values, function (i, v) {
                    if ('object' === typeof v) {
                        var _v = [];
                        $.each(v, function (i, v) {
                            _v.push(v);
                        });
                        values[i] = _v.join(',');
                    } else if (!values[i]) {
                        delete values[i];
                    }
                });

                return values;
            }

            return {};
        },
        getDebounceContent: function (url, data, timeout) {
            var _url = url || this.options.clearUrl,
                _data = data || {},
                _timeout = timeout || this.options.timeout,
                _self = this;

            clearTimeout(this.timeoutID);
            this.timeoutID = setTimeout(function () {
                _self.getContent.call(_self, _url, _data);
            }, _timeout);

        },
        getContent: function (url, data) {
            this.startLoading();
            if (this.options.scrollToTop) {
                $(document).scrollTop(0);
            }
            var _url = url || this.options.clearUrl,
                _data = data || {},
                _self = this;
            this.current_filters = _data;
            _data['navAjax'] = 1;

            var timestamp = new Date().getTime();
            this.lastTimestamp = timestamp;
            if (this.options.abortPrev && this.xhr)
                this.xhr.abort();
            this.xhr = $.ajax({
                url: _url,
                data: _data,
                cache: true,
                dataType: 'json',
                success: function (response) {
                    if (_self.lastUpdate && _self.lastUpdate > timestamp)
                        return;
                    _self.lastUpdate = timestamp;
                    if (response.hasOwnProperty('html')) {
                        _self.replaceHTML(response.html);
                    } else {
                        if (response.hasOwnProperty('msg')) {
                            _self.setMessage({
                                type: 'error',
                                text: response.msg
                            });
                        }
                    }
                    if (this.lastTimestamp > timestamp)
                        return;
                    _self.updateUrl(_url, data);
                    _self.afterUpdate();
                    _self.element.trigger('filter_applied.OXAjaxNav', response);
                    $('body').trigger('contentUpdated').trigger('OxInfiniteScroll');
                    _self.stopLoading();
                },
                error: function (response) {
                    _self.element.trigger('filter_error.OXAjaxNav', response);
                    _self.setMessage({
                        type: 'error',
                        text: 'Sorry, something went wrong. Please try again later.'
                    });
                    window.location = _self.updateUrl(_url, data);
                }
            });
        },
        setMessage: function (obj) {
            messenger().messages({
                messages: ko.observableArray([obj])
            });
        },
        _createLoader: function (loader, parent) {
            if (!this.hasOwnProperty('_loader_class_' + loader)) {
                return;
            }
            var parent = parent || 'body',
                $parent = $(parent),
                $loader = $parent.children(this['_loader_class_' + loader]);
            if (!$loader.length) {
                var content = $(this['_loader_template_' + loader]).html();
                $(content).prependTo($parent);
                $loader = $parent.children(this['_loader_class_' + loader]);
            }
            return $loader;
        },

        startLoading: function () {
            if (this.options.loaderFilter) {
                this._createLoader('filter', this.options.replaceSelectors.leftnav).show();
            }
            if (this.options.loaderContent && this.options.loaderContentSelector) {
                this._createLoader('content', this.options.loaderContentSelector).show();
            }
            if (this.options.loaderPage) {
                this._createLoader('page').show();
            }
        },
        stopLoading: function () {
            if (this.options.loaderFilter) {
                this._createLoader('filter', this.options.replaceSelectors.leftnav).hide();
            }
            if (this.options.loaderContent && this.options.loaderContentSelector) {
                this._createLoader('content', this.options.loaderContentSelector).hide();
            }
            if (this.options.loaderPage) {
                this._createLoader('page').hide();
            }
        },
        replaceHTML: function (data) {
            $.each(data, $.proxy(function (key, html) {
                if (this.options.replaceSelectors.hasOwnProperty(key)) {
                    var $element = $(this.options.replaceSelectors[key]);

                    if ($element.length) {
                        $element.replaceWith(html);
                        $element.trigger('contentUpdated');
                    }
                } else if ('_before' === key || '_after' === key) {
                    if ('_before' === key && html) {
                        $(this.options.replaceContentSelectors.products).before(html);
                    } else if (html) {
                        $(this.options.replaceContentSelectors.products).after(html);
                    }
                } else if (html) {
                    var $elementParent;
                    if (this.options.replaceContentSelectors.hasOwnProperty(key)) {
                        $elementParent = $(this.options.replaceContentSelectors[key]).last();
                    }
                    var createSelector = function (a) {
                        if (!a.prop('tagName'))
                            return;
                        var _id = a.attr('id') || '',
                            _class = (a.attr('class') || '').replace(/\s+/g, ' ').replace(/ /g, '.'),
                            _selector = a.prop('tagName') + (_id ? '#' + _id : '') + (_class ? '.' + _class : '');
                        return _selector;
                    };
                    var items = {},
                        parent,
                        first = true,
                        prevElement;
                    $(html).each(function () {
                        var $html = $(this),
                            selector = createSelector($html);
                        if (!selector)
                            return;
                        if (/^script/i.test(selector)) {
                            $elementParent.append($html);
                            return;
                        }
                        var $element = $(selector);
                        if ($elementParent.length) {
                            $element = $elementParent.find(selector);
                        }
                        if (0 < $element.length) {
                            if (1 == $element.length) {
                                $element.replaceWith($html);
                                prevElement = selector;
                            } else {
                                var $element = $(prevElement);
                                if ($elementParent.length) {
                                    $element = $elementParent.find(prevElement);
                                }
                                $element = $element.next(selector);
                                if (0 < $element.length) {
                                    $element.replaceWith($html);
                                }
                            }
                        } else {
                            $elementParent.append($html);
                        }
                    });
                }
            }, this));
        },
        afterUpdate: function () {
            var count = $(this._current_item).length;
            $(this._current_count).html(count ? count : "");
            $(this.options.replaceSelectors.leftnav).data('moveDesktopParent', $('.sidebar.sidebar-main').eq(0));


        }
    });

    return $.mage.OXAjaxNav;
});
