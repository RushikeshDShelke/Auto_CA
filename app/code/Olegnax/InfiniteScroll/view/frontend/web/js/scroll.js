/**
 * Olegnax Infinite Scroll
 * Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 */
define([
    'jquery',
    'jquery-ui-modules/widget',
    'oxFormValues'
], function ($) {
    $.widget('mage.OxInfiniteScroll', {
        options: {
            mode: "auto",
            container: ".column.main .products.products-grid, .column.main .products.products-list, .column.main .products-grid.grid",
            item: ".column.main .item.product.product-item",
            pagesCountSelector: 'ox-page-count',
            loader: false,
            diff: .9,
            pagerDiff: .1,
            updateUrl: true,
            prevPages: true,
            hideOnlyButton: false,
            disabled: false,
        },
        _create: function () {
            this._button_mode = 'button';
            this._infinity_mode = 'auto';

            this._button_class = 'ox-product-scroll__button';

            this._item_new_class = 'ox-scroll-loaded';
            this._body_class = 'ox-infinite-scroll-enabled';
            this._loader_class = 'ox-product-scroll';
            this._loader_last_class = 'ox-ps-hide';
            this._hidden_block_class = 'ox-hidden-content';
            this._loader_last_without_progress_class = 'ox-product-scroll-no-needed-button';
            this._loader_loading_class = 'active';
            this._progress_amount = '.ox-product-scroll__amount';
            this._progress_amount_loaded_class = 'ox-product-scroll__amount-loaded';

            this._select_template = 'script[data-role="ox-infinity-scroll"]';
            this.pagesCount = 0;
            this.pageCurrent = 0;
            this.lastPosition = 0;
            this.pagesLoaded = [];
            this.pagesCache = {};
            this.pagesLoading = {};
            this.loading_next = false;
            this.loading_prev = false;
            this.manadev_content_replaced = false;
            this.window = $(window);
            $(document).on("amscroll_refresh", $.proxy(function () {
                this.initialize();
            }, this));

            $(document).on('mana-after-content-replaced', $.proxy(function () {
                this.manadev_content_replaced = true;
            }, this));
            $(document).on('mana-after-show', $.proxy(function () {
                if (this.manadev_content_replaced) {
                    $("body").trigger('OxInfiniteScroll');
                }
            }, this));
            $("body").addClass(this._body_class).on("OxInfiniteScroll amscroll_refresh", $.proxy(function () {
                this.initialize();
            }, this));
            this.initialize();
        },
        initialize: function () {
            this.options.disabled = false;
            this.pagesLoaded = [];
            this.pagesCache = {};
            this.pagesLoading = {};
            this.loading_next = false;
            this.loading_prev = false;
            this._initPagesCount();
            this.pagesCache = {};
            if (!this._checkContainer() || this.options.disabled) {
                this._disable();
                return;
            }
            this.window.scroll($.proxy(this._initPaginator, this));
            setTimeout($.proxy(this._initPaginator, this), 7000);
            $(document).ready($.proxy(function () {
                this._preloadPages();
                switch (this.options.mode) {
                    case this._infinity_mode:
                        this._initPaginator();
                        break;
                    case this._button_mode:
                        if (this.pagesCount > this._currentPage())
                            this._generateButton();
                        if (this.options.prevPages && 1 < this._currentPage())
                            this._generateButton('prev');
                        break;
                }
            }, this));
        },
        _initPaginator: function () {
            if (this.options.disabled) {
                return;
            }
            var scrollTop = this.window.scrollTop(),
                windowHeight = this.window.height(),
                $container = $(this.options.container).last(),
                elementTop = $container.offset().top,
                elementHeight = $container.outerHeight(true);
            if (this._infinity_mode === this.options.mode && scrollTop + windowHeight >= elementTop + elementHeight * this.options.diff) {
                this.loadNextContent();
            }
            if (this.options.updateUrl)
                this._calculateCurrentPage(scrollTop, windowHeight);
        },
        _disable: function () {
            $('.' + this._loader_class + ' + .toolbar-products, .' + this._body_class + ' .products.wrapper ~' +
                ' .toolbar-products').show().find('.pages').show();
            $('.' + this._loader_class).remove();
            this.options.disabled = true;
        },
        _generateButton: function (type) {
            this._createLoader(type);
            this._selectButton(type)
            .off('click' + this._namespace())
            .on('click' + this._namespace(), $.proxy(
                'prev' === type
                    ? this.loadPrevContent
                    : this.loadNextContent
                , this));
        },
        _namespace: function () {
            return this.eventNamespace.replace(this.uuid, '');
        },
        loadPrevContent: function () {
            if (this.loading_prev || this.options.disabled) {
                return;
            }
            var page = this._getFirstPageId() - 1,
                cache = this._getPrevPagesCache();
            if (cache) {
                this._showPrevContent(cache);
            } else if (page > 0) {
                this._showLoader('prev');
                this.loading_prev = true;
                var _self = this;
                this._loadPage(page, function (response) {
                    if (response.hasOwnProperty('html')) {
                        _self._showPrevContent(_self._setPagesCache(page, response));
                    } else {
                        console.warn('Incorrect response please refresh the page!');
                        _self._disable();
                    }
                    _self._hideLoader('prev');
                    _self.loading_prev = false;
                    _self.loadPrevContent();
                }, function () {
                    _self._hideLoader('prev');
                    _self.loading = false;
                    console.warn('Error requesting page number ' + page + '!');
                });
            }
        },
        _showPrevContent: function (data) {
            if (data.hasOwnProperty('html') && data.html.hasOwnProperty('products')) {
                var page = data.page;
                if (0 > this.pagesLoaded.indexOf(page)) {
                    var html = $(data.html.products),
                        products = html.find(this.options.item).addClass(this._item_new_class + ' ' + this._item_new_class + '-' + page).data('pageItem', page).detach(),
                        scripts = html.find('script').filter($.proxy(function (i, el) {
                            return $(el).parents(this.options.item).length == 0
                        }, this)).add(html.filter('script'));
                    this._inserBeforeProducts(products);
                    this._insertAfterHidden(scripts);
                    this.appendExternalHTML(data);
                    this.pagesLoaded.unshift(page);
                    this._afterShow(page, data, -1);
                    this._preloadPagePrev(page);
                }
            } else {
                console.warn('Missing product display data!', data);
            }
        },
        loadNextContent: function () {
            if (this.loading_next || this.options.disabled) {
                return;
            }
            var page = this._getLastPageId() + 1,
                cache = this._getNextPagesCache();
            if (cache) {
                this._showNextContent(cache);
            } else if (page <= this.pagesCount) {
                this._showLoader('next');
                this.loading_next = true;
                var _self = this;
                this._loadPage(page, function (response) {
                    if (response.hasOwnProperty('html')) {
                        _self._showNextContent(_self._setPagesCache(page, response));
                    } else {
                        console.warn('Incorrect response please refresh the page!');
                        _self._disable();
                    }
                    _self._hideLoader('next');
                    _self.loading_next = false;
                }, function () {
                    _self._hideLoader('next');
                    _self.loading_next = false;
                    console.warn('Error requesting page number ' + page + '!');
                    var isFirst = true;
                    $.each(_self.pagesCache, function () {
                        isFirst = false;
                        return false;
                    });
                    if (isFirst) {
                        _self._disable();
                    }
                });
            }
        },
        _showNextContent: function (data) {
            if (data.hasOwnProperty('html') && data.html.hasOwnProperty('products')) {
                var page = data.page;
                if (0 > this.pagesLoaded.indexOf(page)) {
                    var html = $(data.html.products),
                        products = html.find(this.options.item).addClass(this._item_new_class + ' ' + this._item_new_class + '-' + page).data('pageItem', page).detach(),
                        scripts = html.find('script').filter($.proxy(function (i, el) {
                            return $(el).parents(this.options.item).length == 0
                        }, this)).add(html.filter('script'));
                    this._insertAfterProducts(products);
                    this._insertAfterHidden(scripts);
                    this.appendExternalHTML(data);
                    this.pagesLoaded.push(page);
                    this._afterShow(page, data, 1);
                    this._preloadPageNext(page);
                }
            } else {
                console.warn('Missing product display data!', data);
            }
        },
        _getNextPagesCache: function () {
            return this._getPagesCache(this._getLastPageId() + 1);
        },
        _getPrevPagesCache: function () {
            return this._getPagesCache(this._getFirstPageId() - 1);
        },
        __calculateCurrentPage: function (windowHeight) {
            windowHeight = windowHeight || this.window.height();
            var newPage = 0,
                lastStatus = false,
                topStep = this.__additionalSpace();
            windowHeight -= topStep;

            $(this.options.container).find(this.options.item + ":visible").each($.proxy(function (i, item) {
                if (this.__isVisible(item, windowHeight, topStep)) {
                    var page = $(item).data('pageItem');
                    if (page > newPage) {
                        newPage = page;
                    }
                    lastStatus = true;
                } else if (lastStatus) {
                    return false;
                }
            }, this));
            if (newPage && this._currentPage() != newPage) {
                this.updateUrl(this._setAjaxData(newPage));
                this.pageCurrent = newPage;
            }
        },
        _calculateCurrentPage: function (scrollTop, windowHeight) {
            if (Math.abs(scrollTop - this.lastPosition) > windowHeight * this.options.pagerDiff) {
                this.lastPosition = scrollTop;
                this.__calculateCurrentPage(windowHeight);
            }
        },
        updateUrl: function (data, url) {
            var _url = url || this._getClearURL(),
                _data = data || {};
            if (data.hasOwnProperty('scrollAjax')) {
                delete data.scrollAjax;
            }
            var new_url = new URL(_url);
            $.each(_data, function (i, v) {
                if ($.isArray(v)) {
                    new_url.searchParams.set(i, v.join(','));
                } else {
                    new_url.searchParams.set(i, v);
                }
            });
            new_url = new_url.toString();

            if (typeof window.history.replaceState === 'function' && (!window.history.state || new_url !== window.history.state.url)) {
                window.history.replaceState({url: new_url}, null, new_url);
            }
            return new_url;
        },
        __additionalSpace: function () {
            var topStep = $('.page-header .sticky-wrapper');
            if (topStep.length)
                return topStep.outerHeight(true);
            return 0;
        },
        __isVisible: function (item, windowHeight, topStep) {
            var sizes = item.getBoundingClientRect();
            sizes = [sizes.top - topStep, sizes.bottom - topStep];
            if (sizes[1] < 0 || sizes[0] > windowHeight) {
                return false;
            } else if (sizes[0] < 0 || sizes[1] > windowHeight) {
                return (((sizes[0] > 0 ? sizes[0] : 0) - (sizes[1] < windowHeight ? sizes[1] : windowHeight)) / (sizes[0] - sizes[1])) > 0.75; // if more than 75% of the element is visible
            }
            return true;
        },
        _getPagesCache: function (pageid) {
            if (this.pagesCache.hasOwnProperty(pageid)) {
                return this.pagesCache[pageid];
            }
            return null;
        },
        _setPagesCache: function (page, data) {
            if (data.hasOwnProperty('html') && data.html.hasOwnProperty('products')) {
                data.page = page;
                this.pagesCache[page] = data;
            }
            return data;
        },
        _getLastPageId: function () {
            if (0 == this.pagesLoaded.length) {
                return this.pageCurrent;
            }
            if (1 == this.pagesLoaded.length) {
                return this.pagesLoaded[0];
            }
            if (this.pagesLoaded.__proto__.hasOwnProperty('max')) {
                return this.pagesLoaded.max();
            }
            return this.pagesLoaded[this.pagesLoaded.length - 1];
        },
        _getFirstPageId: function () {
            if (this.pagesLoaded.__proto__.hasOwnProperty('min')) {
                return this.pagesLoaded.min();
            }
            return this.pagesLoaded[0];
        },
        _selectButton: function (type) {
            var type = type || 'next';
            return $('.' + this._loader_class + '.' + this._loader_class + '-' + type + ' .' + this._button_class);
        },
        _createLoader: function (type) {
            var type = type || 'next';
            $parent = $(this.options.container).filter($.proxy(function (i, el) {
                return $(el).find(this.options.item).length;
            }, this));
            $loader = $parent.parent().children('.' + this._loader_class + '.' + this._loader_class + '-' + type);
            if (!$loader.length) {
                var content = $(this._select_template).html(),
                    $loader = $(content).addClass(this._loader_class + '-' + type);
                if ('prev' === type) {
                    $parent.before($loader);
                } else {
                    $parent.after($loader);
                }
            }
            if ($(this._progress_amount).length)
                this._preloadPageNextFirts();
            return $loader;
        },
        _showLoader: function (type) {
            if (this.options.loader) {
                this._createLoader(type).addClass(this._loader_loading_class);
            }
        },
        _hideLoader: function (type) {
            if (this.options.loader) {
                $('.' + this._loader_class + '.' + this._loader_class + '-' + type).removeClass(this._loader_loading_class);
            }
        },
        _insertAfterProducts: function ($elements) {
            $(this.options.container).find(this.options.item).parent().append($elements);
        },
        _inserBeforeProducts: function ($elements) {
            $(this.options.container).find(this.options.item).parent().prepend($elements);
        },
        _insertAfterHidden: function ($elements) {
            var $parent = $(this.options.container).parent().children('.' + this._hidden_block_class);
            if (!$parent.length) {
                $parent = $('<div>').attr({
                    'class': this._hidden_block_class,
                    'style': 'display: none'
                });

                $(this.options.container).parent().append($parent);
            }
            $parent.html($elements);
        },
        _insertAfter: function ($elements) {
            var $parent = $(this.options.container).filter($.proxy(function (i, el) {
                return $(el).find(this.options.item).length;
            }, this));
            $parent.after($elements);
        },
        _insertBefore: function ($elements) {
            var $parent = $(this.options.container).filter($.proxy(function (i, el) {
                return $(el).find(this.options.item).length;
            }, this));
            $parent.before($elements);
        },
        _currentPage: function () {
            if (!this.pageCurrent) {
                var args = $.getURLValues();
                this.pageCurrent = parseInt(args.hasOwnProperty('p') ? args.p : 1);
            }
            return this.pageCurrent;
        },
        _initPagesCount: function () {
            var $pagesCount = $('[id="' + this.options.pagesCountSelector + '"]');
            if ($pagesCount && $pagesCount.length) {
                this.pagesCount = parseInt($pagesCount.eq(0).html());
                $pagesCount.prev('.pages').hide();
                return;
            }
            this.pagesCount = 1;
        },
        _checkContainer: function () {
            if (!this.options.container || 0 === $(this.options.container).length) {
                console.warn('Please specify DOM selectors in module settings.');
                return false;
            }
            return this.pagesCount > 1;
        },
        _preloadPages: function () {
            var page = this._currentPage();
            $(this.options.container).find(this.options.item).data('pageItem', page);
            this.pagesLoaded.push(page);
            this._preloadPageNext(page);
            if (this.options.prevPages)
                this._preloadPagePrev(page);
        },
        _getClearURL: function () {
            var url = (new URL(window.location.href));
            url.hash = '';
            url.search = '';
            return url.toString();
        },
        _setAjaxData: function (page) {
            var data = $.getURLValues();
            data['p'] = page;
            data['scrollAjax'] = 1;
            if (1 >= data['p']) {
                delete data.p;
            }
            return data;
        },
        _loadPage: function (page, callback, error_callback) {
            var _self = this,
                callback = callback || function () {
                },
                error_callback = error_callback || function (jqXHR, textStatus, errorThrown) {
                    console.warn('Request failed!', jqXHR, textStatus, errorThrown);
                    var isFirst = true;
                    $.each(_self.pagesCache, function () {
                        isFirst = false;
                        return false;
                    });
                    if (isFirst) {
                        _self._disable();
                    }
                },
                data;
            if ("number" == typeof page) {
                data = this._setAjaxData(page);
            }
            if (this.pagesLoading.hasOwnProperty(page)) {
                this.pagesLoading[page] = {success: callback, error: error_callback};
                return;
            }
            this.pagesLoading[page] = {success: callback, error: error_callback};
            var queryString = Object.keys(data).map(function (key) {
                return key + '=' + data[key]
            }).join('&');

            $.ajax({
                url: this._getClearURL() + '?' + queryString,
                cache: true,
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if (_self.pagesLoading.hasOwnProperty(page)) {
                        _self.pagesLoading[page].success.call(_self, data, textStatus, jqXHR);
                        delete _self.pagesLoading[page];
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (_self.pagesLoading.hasOwnProperty(page)) {
                        _self.pagesLoading[page].error.call(_self, jqXHR, textStatus, errorThrown);
                        delete _self.pagesLoading[page];
                    }
                }
            });
        },
        _preloadPage: function (page) {
            var _self = this;
            this._loadPage(page, function (response) {
                if (response.hasOwnProperty('html')) {
                    _self._setPagesCache(page, response);
                } else {
                    console.warn('Incorrect response please refresh the page!');
                    _self._disable();
                }
            });
        },
        _preloadPageNext: function (page) {
            var _page = page + 1;
            if (_page && _page <= this.pagesCount) {
                this._preloadPage(_page);
            }
        },
        _preloadPageNextFirts: function (page) {
            var page = page || this._getLastPageId(),
                _page = page + 1,
                __page = page - 1,
                _self = this,
                cache;
            if (_page && _page <= this.pagesCount) {
                cache = this._getNextPagesCache();
                if (cache) {
                    _self._firstValueToolbar(cache, -1);
                } else {
                    this._loadPage(_page, function (response) {
                        if (response.hasOwnProperty('html')) {
                            _self._firstValueToolbar(_self._setPagesCache(_page, response), -1);
                        } else {
                            console.warn('Incorrect response please refresh the page!');
                            _self._disable();
                        }
                    });
                }
            } else if (__page && __page >= 1) {
                cache = this._getPrevPagesCache();
                if (cache) {
                    _self._firstValueToolbar(cache, 1);
                } else {
                    this._loadPage(__page, function (response) {
                        if (response.hasOwnProperty('html')) {
                            _self._firstValueToolbar(_self._setPagesCache(__page, response), 1);
                        } else {
                            console.warn('Incorrect response please refresh the page!');
                            _self._disable();
                        }
                    });
                }
            }
        },
        _preloadPagePrev: function (page) {
            var _page = page - 1;
            if (_page && _page >= 1) {
                this._preloadPage(_page);
            }
        },
        _afterShow: function (page, data, action) {
            if (data.isFirst || 1 >= page) {
                $('.' + this._loader_class + '.' + this._loader_class + '-prev').addClass(this.options.hideOnlyButton ? this._loader_last_without_progress_class : this._loader_last_class);
            }
            if (data.isLast || this.pagesCount <= page) {
                $('.' + this._loader_class + '.' + this._loader_class + '-next').addClass(this.options.hideOnlyButton ? this._loader_last_without_progress_class : this._loader_last_class);
            }
            this._fixValueToolbar(data, action);
            if (this.options.updateUrl)
                this.__calculateCurrentPage();
            if (data.hasOwnProperty('html')) {
                if (data.html.hasOwnProperty('_before') && data.html['_before']) {
                    $(this.options.container).before(data.html['_before']);
                }
                if (data.html.hasOwnProperty('_after') && data.html['_after']) {
                    $(this.options.container).after(data.html['_after']);
                }
            }
            this.element.trigger('contentUpdated');
        },
        _firstValueToolbar: function (data, action) {
            var firstNum, lastNum;
            if (-1 == action) {
                lastNum = data.firstNum - 1;
            } else {
                firstNum = data.lastNum + 1;
            }
            this._fixValueToolbar(data, action, firstNum, lastNum);

        },
        _fixValueToolbar: function (data, action, firstNum, lastNum) {
            var container = this.options.container,
                item = this.options.item,
                loaded_class = this._progress_amount_loaded_class,
                items_count = $(container).find(item).length,
                productsCount = data.productsCount,
                progress = items_count * 100 / productsCount;
            u = 'undefined';

            if (u == typeof firstNum && u == typeof lastNum) {
                if (-1 == action) {
                    firstNum = data.firstNum;
                } else {
                    lastNum = data.lastNum;
                }
            }
            if (u == typeof firstNum) {
                firstNum = lastNum - items_count + 1;
            }
            if (u == typeof lastNum) {
                lastNum = firstNum + items_count - 1;
            }
            if (1 > firstNum || lastNum - firstNum + 1 != items_count || data.pageSize > items_count) {
                firstNum = 1;
                lastNum = items_count;
            }
            $('.toolbar-amount, ' + this._progress_amount).each(function () {
                var $toolbar = $(this),
                    $number = $toolbar.find('.toolbar-number');
                if (3 == $number.length) {
                    $number.eq(0).html(firstNum);
                    $number.eq(1).html(lastNum);
                    $number.eq(2).html(productsCount);
                }
                $toolbar.find('.toolbar-number-first').html(firstNum);
                $toolbar.find('.toolbar-number-last').html(lastNum);
                $toolbar.find('.toolbar-number-total').html(productsCount);

                $toolbar.find('.amount-count-line span').css('width', progress + "%");
                $toolbar.addClass(loaded_class);
            });
            $('body').trigger('oxToggleUpdated');
        },
        appendExternalHTML: function (data) {
            //Amasty Label
            var html = $(data.html.products),
                labels = html.filter('div.amasty-label-container').filter($.proxy(function (i, el) {
                    return $(el).parents(this.options.item).length == 0
                }, this));
            this._insertAfter(labels);

        }
    });
    return $.mage.OxInfiniteScroll;
});
