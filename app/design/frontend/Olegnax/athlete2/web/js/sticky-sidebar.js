define(['jquery'], function ($) {
    'use strict';
    "use strict";
    // Sticky sidebars and content
    var stickySidebar = function (so) {
        $(function () {
            var sd = {
                    wrapper: "",
                    sticky: ""
                },
                s = $.extend(true, {}, sd, so),
                $body = $("body");
            if (s.wrapper && s.sticky) {
                /* merge two sidebar block in one div */
                var wrapSidebar = function () {
                        var $ox;
                        if ($(window).width() >= 1025 || !$("body.catalog-product-view").length) {
                            if (($(".page-layout-2columns-left").length || $(".page-layout-2columns-right").length)) {
                                $('div:not(.ox-sticky-sidebar) > .sidebar').wrapAll('<div class="ox-sticky-sidebar">');
                            }
                            /* wrap left sidebar for 3 cols and set sidebar width to wrapper, because content block jumps left when sidebar became sticky */
                            if ($(".page-layout-3columns").length) {
                                $ox = $(".sidebar-main");
                                var $sm_ow = $ox.outerWidth(true);
                                if (!$ox.parent().is(".ox-3col-sticky-wrapper")) {
                                    $ox.wrapAll('<div class="ox-3col-sticky-wrapper">');
                                    $(".ox-3col-sticky-wrapper").css({
                                        width: $sm_ow
                                    });
                                }
                            }
                        } else {
                            $ox = $(".ox-sticky-sidebar");
                            if ($ox.length) {
                                $ox.contents().unwrap();
                            }
                            $ox = $(".ox-3col-sticky-wrapper");
                            if ($ox.length) {
                                $ox.contents().unwrap();
                            }
                        }
                    },
                    _spacing = 40,
                    stickyItems = [],
                    stickyItem = function (item) {
                        this.$item = $(item);
                        this.getVals = function () {
                            this.outerWidth = this.$item.outerWidth(true);
                            this.offsetLeft = this.$item.offset().left;
                            this.positionLeft = this.$item.position().left;
                            this.alwaysTop = this.$item.hasClass('ox-sticky-always-top');
                            return this;
                        };
                        this.reset = function () {
                            this.clearStyles().getVals();
                        };
                        this.outerHeightNew = function () {
                            return this.outerHeight = this.$item.outerHeight(true);
                        };
                        this.addClass = function (_class) {
                            return this.$item.addClass(_class);
                        };
                        this.removeClass = function (_class) {
                            return this.$item.removeClass(_class);
                        };
                        this.css = function (style) {
                            return this.$item.css(style);
                        };
                        this.clearStyles = function () {
                            this.$item.removeClass("ox-sticky-static ox-sticky-fixed").css({
                                bottom: "",
                                top: "",
                                left: "",
                                width: ""
                            });
                            return this;
                        };
                        this.getVals();
                    };
                wrapSidebar();
                $(s.sticky).each(function () {
                    stickyItems.push(new stickyItem(this));
                });
                if (stickyItems.length) {
                    if(!$(s.wrapper).length) {
                        return;
                    }
                    var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame,
                        $window = $(window),
                        windowHeight = $window.height(),
                        $wrapper = $(s.wrapper),
                        wrapperTop = $wrapper.offset().top,
                        stickyItemsReset = function () {
                            wrapperTop = $wrapper.offset().top;
                            for (var i = 0; i < stickyItems.length; i++) {
                                stickyItems[i].reset();
                            }
                        },
                        update = function () {
                            if (0 > wrapperTop) {
                                wrapperTop = $(s.wrapper).offset().top;
                            }
                            var wrapperHeight = $wrapper.height(),
                                scrollTo = wrapperTop + wrapperHeight,
                                isSmall = $(window).width() < 1025,
                                isEdg = window.navigator.userAgent.indexOf(" Edg/") > -1, // @todo use for Edge browser
                                scrollTop,
                                scrollBottom,
                                _height,
                                __height, // @todo original:
                                topOffset,
                                $ox,
                                _is,
                                _static = "ox-sticky-static",
                                _fixed = "ox-sticky-fixed";
                            for (var i = 0; i < stickyItems.length; i++) {
                                var _stickyItem = stickyItems[i];
                                if (isSmall || wrapperHeight <= _stickyItem.outerHeightNew() + 100) {
                                    _stickyItem.clearStyles();
                                } else {
                                    scrollTop = scrollTop || $window.scrollTop();
                                    scrollBottom = scrollBottom || scrollTop + windowHeight;
                                    topOffset = _stickyItem.outerHeight + wrapperTop;
                                    if (windowHeight > topOffset || _stickyItem.alwaysTop) {
                                        $ox = $ox || $(".ox-sticky");
                                        _height = _height || _spacing + ($ox.length > 0 ? $ox.height() : 0);
                                        if (scrollTop >= scrollTo - _stickyItem.outerHeight - _height) {
                                            _stickyItem.addClass(_static).removeClass(_fixed).css({
                                                top: "auto",
                                                left: _stickyItem.positionLeft,
                                                bottom: "",
                                                width: ""
                                            });
                                        } else {
                                            _is = scrollTop + _height >= wrapperTop;
                                            _stickyItem.removeClass(_static).toggleClass(_fixed, _is);
                                            if (_is) {
                                                _stickyItem.css({
                                                    top: _height,
                                                    width: _stickyItem.outerWidth,
                                                    left: _stickyItem.offsetLeft
                                                });
                                            } else {
                                                _stickyItem.clearStyles();
                                            }
                                        }
                                    } else {
                                        if (scrollBottom >= scrollTo + _spacing) {
                                            _stickyItem.addClass(_static).removeClass(_fixed).css({
                                                top: "",
                                                left: _stickyItem.positionLeft,
                                                bottom: 0,
                                                width: ""
                                            });
                                        } else {
                                            _is = scrollBottom >= topOffset + _spacing;
                                            _stickyItem.removeClass(_static).toggleClass(_fixed, _is);
                                            if (_is) {
                                                _stickyItem.css({
                                                    bottom: _spacing,
                                                    width: _stickyItem.outerWidth,
                                                    left: _stickyItem.offsetLeft,
                                                    top: ""
                                                });
                                            } else {
                                                _stickyItem.clearStyles();
                                            }
                                        }
                                    }
                                }
                            }
                        };
                    requestAnimationFrame(update);
                    $body.on("contentUpdated oxToggleUpdated", function () {
                        stickyItemsReset();
                        requestAnimationFrame(update);
                    });
                    $window.on("scroll", function () {
                        requestAnimationFrame(update);
                    }).on("resize", function () {
                        wrapSidebar();
                        windowHeight = $window.height();
                        stickyItemsReset();
                        requestAnimationFrame(update);
                    });
                }
            }
        });
    };
    return stickySidebar;
});
