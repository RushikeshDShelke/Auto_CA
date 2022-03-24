define([
    'jquery',
    'mage/translate',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
], function ($, $t) {
    'use strict';

    $.widget('mage.OXExpand', {
        options: {
            isMin: true,
            maxHeight: 90,
            more: $t('Show more'),
            less: $t('Show less'),
            checkCurrentSize: true
        },
        _create: function () {
            $('body').off('click.OXExpand').on('click.OXExpand', '.ox-expand .ox-expand__link', $.proxy(this._click, this));
        },
        _click: function (event) {
            event.preventDefault();
            var $this = $(event.currentTarget).closest('.ox-expand').eq(0);
            this.toggle($this);
        },
        toggle: function (expand) {
            var $expand = expand || this.element.children('.ox-expand'),
                $expandInner = $expand.children('.ox-expand__inner'),
                max_height = $expand.data('max-height') || 90,
                isMin = $expand.hasClass('minimized');

            $expand.toggleClass('minimized', !isMin);
            $expandInner.attr("aria-expanded", isMin ? "true" : "false")
            .css('max-height', isMin ? '100%' : max_height);
        },
        _init: function () {
            this._super();
            this._create_html();
            var $expand = this.element.children('.ox-expand'),
                $expandInner = $expand.children('.ox-expand__inner'),
                $expandLink = $expand.children('.ox-expand__link');

            $expand.toggleClass('minimized', this.options.isMin).data({
                'max-height': this.options.maxHeight
            });
            $expandInner.attr('aria-expanded', this.options.isMin ? 'false' : 'true')
            .css('max-height', this.options.isMin ? this.options.maxHeight : '100%');
            $expandLink.find('.more').html(this.options.more);
            $expandLink.find('.less').html(this.options.less);
        },
        _create_html: function () {
            if (this.element.children('.ox-expand').length || (this.options.checkCurrentSize && this.options.maxHeight > this.element.height()))
                return;
            var content = this.element.html(),
                new_content = $('<div class="ox-expand">').append(
                    $('<div class="ox-expand__inner">').html(content)
                ).append('<div class="ox-expand__link"><span class="more"></span><span class="less"></span></div>');
            this.element.html('').append(new_content);
        }
    });

    return $.mage.OXExpand;
});
