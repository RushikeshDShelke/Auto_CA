define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('amasty_conf.SwatchRenderer', widget, {
            _AmOnClick: function ($this, $widget) {
                $widget._OnClick($this, $widget);
                var slickSlide = $this,
                    possibleSlide = $this.parent();
                // fix for when price in title enabled
                if (possibleSlide.hasClass('slick-slide')) {
                    slickSlide = possibleSlide;
                }
                if (slickSlide.hasClass('slick-slide') && $this.hasClass('selected')) {
                    slickSlide.parent()
                    .find('[option-id="' + $this.attr('option-id') + '"]:not(.selected)')
                    .addClass('selected');
                }

                if (this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                    $widget._addHashToUrl($this, $widget);
                }

                $widget._reloadProductInformation($this, $widget);

                var isProductViewExist = $('body.catalog-product-view').length > 0;

                if (isProductViewExist) {
                    $widget._RenderPricesForControls();
                }
                if (isProductViewExist || this.ajaxCart) {
                    this._saveLastRowContent();
                    $widget._RenderProductMatrix();
                    if (this.options.jsonConfig.swatches_slider) {
                        this._generateSliderSwatches();
                    }
                }
                $widget._addOutOfStockLabels();
            },
            onMouseLeave: function($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    selectedOption = $parent.find('.' + $widget.options.classes.optionClass + '.selected'),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput),
                    attributeId = $this.attr('attribute-id')
                        || $this.attr('data-attribute-id')
                        || selectedOption.attr('attribute-id')
                        || selectedOption.attr('data-attribute-id'),
                    selectedOptionAttributeClass = $widget.options.jsonConfig.selected_option_attribute_class || 'option-selected';
                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }
                if (selectedOption.length > 0) {
                    $parent.attr(selectedOptionAttributeClass, selectedOption.attr('option-id'));
                } else {
                    $parent.removeAttr(selectedOptionAttributeClass).find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    $this.attr('aria-checked', false);
                }
                $widget._loadMedia();
            },
        });

        return $.amasty_conf.SwatchRenderer;
    }
});