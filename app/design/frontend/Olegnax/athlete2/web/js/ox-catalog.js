require(['jquery', 'domReady!'], function ($) {
    (function () {
        if ($('body').hasClass('page-layout-1column') || $('body').hasClass('page-layout-1column-fullwidth')) {
            var $this = $('.ox-move-item-filtersslideout, .ox-block-filter, .sidebar-additional'),
                $parent = $('[data-move-mobile="filtersslideout"]').eq(0);
            if (!$this.length || !$parent.length || $this.parent().is($parent)) {
                return;
            }
            var element = $this.detach();
            $parent.append(element);
            $('body').addClass('ox-show-filter');
        }
    })();
});

require([
    'jquery',
], function ($) {
    'use strict';

    function init() {
        $('.js-toolbar-switch').off('mouseenter').on('mouseenter', function (e) {
            var $this = $(this),
                $dropdown = $('.js-toolbar-dropdown', $this),
                width;
            $this.addClass('over');
            if ($this.closest('.sorter').length) {
                width = $this.width() + 50;
            } else {
                width = $this.width() - parseInt($dropdown.css('padding-left')) * 2;
            }
            $dropdown
            .css('width', width)
            .stop(true, true)
            .hide()
            .animate({
                opacity: 1,
                height: 'toggle'
            }, 100);
        }).off('mouseleave').on('mouseleave', function (e) {
                var $this = $(this),
                    $dropdown = $('.js-toolbar-dropdown', $this);
                $dropdown.stop(true, true).animate({
                    opacity: 0,
                    height: 'toggle'
                }, 100, function () {
                    $this.removeClass('over');
                });
            }
        );
    }

    $(init);
    $('body').on('contentUpdated', init);

});