/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Theme JS */
require([
    'jquery',
    'mage/cookies',
    'domReady!'
], function ($) {
    'use strict';
    /* dynamic input size for overlay search */
    $.fn.textWidth = function (text, font) {
        if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
        $.fn.textWidth.fakeEl.text(text || this.val() || this.text() || this.attr('placeholder')).css({
            'font': font || this.css('font'),
            'text-transform': 'uppercase',
            'letter-spacing': this.css('letter-spacing')
        });
        return $.fn.textWidth.fakeEl.width();
    };
    $('.width-dynamic').on('input', function () {
        var inputWidth = $(this).textWidth() + 40; //40 is padding
        $(this).css({
            width: inputWidth
        })
    }).trigger('input');
    $('.width-dynamic').blur(function () {
        if (!$(this).val().length) {
            $(this).css({
                width: ''
            })
        }
    });
    /* input plus minus */
    $("body").on('click', ".qty-controls-wrap .qty-plus", function (event) {
        event.preventDefault();
        var _input = $(this).parent().find('input');
        _input.val(parseInt(_input.val()) + 1);
        _input.change();
        $(this).parent().find('button.update-cart-item').show();

    });
    $("body").on('click', ".qty-controls-wrap .qty-minus", function (event) {
        event.preventDefault();
        var _input = $(this).parent().find('input');
        var count = parseInt(_input.val()) - 1;
        count = count < 0 ? 0 : count;
        _input.val(count).change();
        $(this).parent().find('button.update-cart-item').show();

    });
    $("body").on('change', ".qty-controls-wrap input", function () {
        var $this = $(this);
        var min = 0;
        var val = parseInt($this.val(), 10);
        var max = 10000000;
        val = Math.min(val, max);
        val = Math.max(val, min);
        if (isNaN(val)) {
            val = 0;
        }
        $this.val(val);
        $this.trigger('keypress');
    });


    /* Filter toggle */
    var item = $('.block.filter, .filters-slideout-content').find('.filter-options, .filter-current'),
        itemContent = item.find('.filter-options-content, .ox-toggle-content');

    if (item.length) {
        item.each(function () {
            if ($(this).hasClass('collapsible')) {
                if ($(this).hasClass('open')) {
                    $(this).find(itemContent).slideDown(function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                } else {
                    $(this).find(itemContent).slideUp(function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                }
            }
        });
        $(document).on('click', '.filter-options-title, .filter-current-subtitle', function (e) {
            e.preventDefault();
            var speed = 300;
            var thisParent = $(this).parent(),
                nextLevel = $(this).next('.filter-options-content, .ox-toggle-content');
            if (thisParent.hasClass('collapsible')) {
                if (thisParent.hasClass('open')) {
                    thisParent.removeClass('open');
                    nextLevel.slideUp(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                } else {
                    thisParent.addClass('open');
                    nextLevel.slideDown(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                }
            }
        })
    }

    $.fn.ox_toggles = function () {
        var $this = $(this);
        $this.each(function () {
            if ($(this).hasClass('collapsible')) {
                if ($(this).hasClass('open')) {
                    $(this).find(itemContent).slideDown();
                } else {
                    $(this).find(itemContent).slideUp();
                }
            }
        });

        $this.on('click', '.ox-toggle-title', function (e) {
            e.preventDefault();
            var speed = 300;
            var thisParent = $(this).parent(),
                nextLevel = $(this).next('.ox-toggle-content');
            if (thisParent.hasClass('collapsible')) {
                if (thisParent.hasClass('open')) {
                    thisParent.removeClass('open');
                    nextLevel.slideUp(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                } else {
                    thisParent.addClass('open');
                    nextLevel.slideDown(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                }
            }
        })
    };
    $('.ox-toggle').ox_toggles();
    
    /* Collapsible text */
    $('body').off('click.OXExpand').on('click.OXExpand', '.ox-expand .ox-expand__link', function (e) {
        e.preventDefault();
        var $this = $(this).closest('.ox-expand').eq(0),
            max_height = $this.data('max-height') || 90,
            isMin = $this.hasClass('minimized');
        $this.toggleClass('minimized', !isMin)
        .children('.ox-expand__inner')
        .attr("aria-expanded", isMin ? "true" : "false")
        .css('max-height', isMin ? '100%' : max_height);
    });
    $(function () {
        $('.ox-expand').each(function () {
            let $this = $(this),
                $inner = $this.find('.ox-expand__inner'),
                max_height = $this.data('max-height') || 90;
            if (parseInt($inner.css('height')) < max_height) {
                let isMin = true;
                $this.toggleClass('minimized', !isMin)
                .children('.ox-expand__inner')
                .attr("aria-expanded", isMin ? "true" : "false")
                .css('max-height', isMin ? '100%' : max_height);
                $this.find('.ox-expand__link').hide();
            }
        });
    });


});



require([
    'jquery',
    'jquery-ui-modules/effect',
    'domReady!'
], function ($) {
    /* cms banner animation */
    var debounce = function (func, wait, immediate) {
        var timeout, args, context, timestamp, result;
        return function () {
            context = this;
            args = arguments;
            timestamp = new Date();
            var later = function () {
                var last = (new Date()) - timestamp;
                if (last < wait) {
                    timeout = setTimeout(later, wait - last);
                } else {
                    timeout = null;
                    if (!immediate)
                        result = func.apply(context, args);
                }
            };
            var callNow = immediate && !timeout;
            if (!timeout) {
                timeout = setTimeout(later, wait);
            }
            if (callNow)
                result = func.apply(context, args);
            return result;
        };
    };
    $.fn.OxBanner = function () {

        var cmsBanner = $(this);
        var cmsBannerText = cmsBanner.find('.ox-banner-animated-container');

        if (!cmsBannerText.length)
            return;

        $('.text', cmsBanner).wrap('<div class="animation-wrapper animation-text" />');
        $('.link', cmsBanner).wrap('<div class="animation-wrapper animation-link" />');
        $(' br', cmsBannerText).hide();

        var initTitle = function () {
            //$('.text, .link', cmsBanner).removeAttr('style');
            $('.animation-wrapper', cmsBanner).removeAttr('style').css({visibility: 'hidden'}).attr({'data-width': '', 'data-height': ''});

            cmsBannerText.css('visibility', 'visible');
            $('.text, .link', cmsBanner).each(function () {
                var w = $(this).outerWidth(),
                    h = $(this).outerHeight();

                $(this).parent()
                .attr('data-width', w)
                .attr('data-height', h)
                .width(0)
                .height(h);
            });
            /*
            $( '.ox-banner-animated-container.center', cmsBanner ).each( function () {
                $( this ).css( 'marginTop', parseInt( ( $( this ).parent().height() - $( this ).height() ) / 2 ) + 'px' );
            } );*/
        };

        var showTitle = function () {
            initTitle();
            $('.animation-wrapper', cmsBannerText).each(function (i) {
                $(this)
                .css('visibility', 'visible')
                .delay(32 * i)
                .queue(function (next) {
                    $(this).animate({width: $(this).attr('data-width')}, 256, 'easeOutExpo');
                    next();
                });
            });
        };

        //banner hover
        $('.ox-banner').hover(
            function () {
                $('.ox-banner-animated-container .animation-wrapper', this).each(function (i) {
                    $(this)
                    .delay(64 * (i))
                    .queue(function (next) {
                        $(this).addClass('animate-me');
                        next();
                    });
                });
            },
            function () {
                $('.ox-banner-animated-container .animation-wrapper', this).each(function (i) {
                    $(this)
                    .delay(64 * i)
                    .queue(function (next) {
                        $(this).removeClass('animate-me');
                        next();
                    });
                });
            }
        );

        setTimeout(function () {
            showTitle();
            $(window).on('resize', function () {
                $('.animation-wrapper', cmsBannerText).css({width: 0});
            });
            $(window).resize(debounce(showTitle, 400));
        }, 1000);
    };
    $('.ox-banner-animated-text').OxBanner();


});

require(['jquery', 'matchMedia'], function ($) {
    $(function () {
        var _this = '.header__item-account-links',
            _class = 'store.links',
            mediaBreakpoint = '(min-width: 1025px)';
        if (_this.length) {
            mediaCheck({
                media: mediaBreakpoint,
                entry: function () {
                    var $this = $(_this),
                        $desktop_parent = $('[data-move-desktop="header.myaccount"]').eq(0),
                        position = $this.data('moveDesktopPosition') || 0;
                    if (!$desktop_parent.length || $this.parent().is($desktop_parent)) {
                        return;
                    }

                    var element = $this.detach();
                    if (0 < position) {
                        var prev = $desktop_parent.children().eq(position - 1);
                        if (prev.length) {
                            prev.after(element);
                        } else {
                            $desktop_parent.append(element);
                        }
                    } else {
                        $desktop_parent.append(element);
                    }
                },
                exit: function () {
                    var $this = $(_this),
                        $mobile_parent = $('[data-move-mobile="' + _class + '"]').eq(0);
                    if (!$this.length || !$mobile_parent.length || $this.parent().is($mobile_parent)) {
                        return;
                    }
                    $this.data('moveDesktopPosition', $this.parent().children().index($this));
                    var element = $this.detach();
                    $mobile_parent.append(element);
                }
            });
        }
    });
});

/* resize fullheight menu if its width bigger than the header area */
require(["jquery", "domReady!"], function ($) {
    "use strict";
    $(function () {
        var menu2 = $('.menu-style-2'),
            menu4 = $('.menu-style-4'),
            menu5 = $('.menu-style-5'),
            headerContent = $('.page-header'),
            headerLeft = headerContent.find('.ox-megamenu-navigation'),
            headerLeftWidth = headerLeft.innerWidth();

        function debouncer(func, timeout) {
            var timeoutID, timeout = timeout || 500;
            return function () {
                var scope = this,
                    args = arguments;
                clearTimeout(timeoutID);
                timeoutID = setTimeout(function () {
                    func.apply(scope, Array.prototype.slice.call(args));
                }, timeout);
            }
        }

        var calcMenuWidth = function () {
            headerLeft = headerContent.find('.ox-megamenu-navigation');
            if ((menu2.length || menu4.length || menu5.length) && headerLeft.length && $(window).width() > 1024) {
                var count = 0,
                    padding = 0;
                if( menu5.length){
                     padding = 24;
                }
                headerLeftWidth = headerLeft.innerWidth() - padding;
                headerLeft.find("> li").each(function () {
                    count += $(this).outerWidth(true);
                });
                $('body').toggleClass('ox-mm-resize', headerLeftWidth < count);
            }
        };
        calcMenuWidth();
        $(window).on('resize', function () {
            debouncer(calcMenuWidth());
            setTimeout(function () {
                debouncer(calcMenuWidth());
            }, 150);
        });
    });
});

require(["jquery"], function ($) {
    "use strict";
    $(function () {
        $('.block-search.block-search--type-panel button.action-search').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            $(this).closest('.block-search').eq(0).find('form').trigger('submit');
        });
    });
});

require(["jquery"], function ($) {
    "use strict";
    $(function () {
        $(".js-input-focus").each(function () {
            if ($(this).val().length) {
                $(this).closest(".control").addClass('input-focused');
                $("html").addClass('ox-search-focused');
            }
        });
        $(".js-input-focus").focus(function () {
            $(this).closest(".control").addClass('input-focused');
            $("html").addClass('ox-search-focused');
        }).blur(function () {
            if (!$(this).val().length) {
                $(this).closest(".control").removeClass('input-focused');
                $("html").removeClass('ox-search-focused');

            }
        })

    });
});

require(["jquery"], function ($) {
    "use strict";
    $(function () {
        //check device type
        //touch on IOS and Android
        var isAndroid = /(android)/i.test(navigator.userAgent);
        var isMobile = /(mobile)/i.test(navigator.userAgent);
        if (navigator.userAgent.match(/(iPod|iPhone|iPad)/) || isAndroid || isMobile) {
            $('html').addClass('touch').removeClass('no-touch');
        } else {
            $('html').addClass('no-touch').removeClass('touch');
        }

    });
});


require(["jquery"], function ($) {
    "use strict";
    $(function () {

        var $toTop = $("#toTop");
        if ($toTop.length > 0) {
            $(window).on('scroll', function () {
                if ($(this).scrollTop() > 100) {
                    $toTop.show();
                } else {
                    $toTop.hide();
                }
            });
            $toTop.on("click", function () {
                $('body,html').animate({
                    scrollTop: 0
                }, 256);
                return false;
            });
        }

    });
});

