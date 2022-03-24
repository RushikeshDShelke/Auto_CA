define([
    'jquery',
    'jquery/ui',
    "domReady!",
    'Jajuma_WebpImages/js/lib/modernizr-webp'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _OnClick: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    attributeId = $parent.attr('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput);

                if (typeof this.options.jsonSwatchConfig[attributeId]['additional_data'] != 'undefined') {
                    var checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']);
                }

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.hasClass('disabled')) {
                    return;
                }

                if ($this.hasClass('selected')) {
                    $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    $this.attr('aria-checked', false);
                } else {
                    $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                    $label.text($this.attr('option-label'));
                    $input.val($this.attr('option-id'));
                    $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                    $this.addClass('selected');
                    $widget._toggleCheckedAttributes($this, $wrapper);
                }

                $widget._Rebuild();

                if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                ) {
                    $widget._UpdatePrice();
                }

                $(document).trigger('updateMsrpPriceBlock',
                    [
                        parseInt($this.attr('index'), 10) + 1,
                        $widget.options.jsonConfig.optionPrices
                    ]);

                if (typeof checkAdditionalData != 'undefined') {
                    if (checkAdditionalData['update_product_preview_image'] == '1') {
                        $widget._loadMedia();
                    }
                } else {
                    $widget._loadMedia();
                }

                $input.trigger('change');
            },
            updateBaseImage: function (images, context, isInProductView) {
                var justAnImage = images[0],
                    initialImages = this.options.mediaGalleryInitial,
                    imagesToUpdate,
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    isInitial,
                    self = this;

                if (isInProductView) {
                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                    isInitial = _.isEqual(imagesToUpdate, initialImages);

                    if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                        imagesToUpdate = imagesToUpdate.concat(initialImages);
                    }

                    imagesToUpdate = this._setImageIndex(imagesToUpdate);

                    if (!_.isUndefined(gallery)) {
                        Modernizr.on('webp', function(result) {
                            if (result) {
                                // custom replace images with webp images
                                self.convertImgToWebp(imagesToUpdate, context, isInProductView, justAnImage);
                            }
                        });
                    } else {
                        context.find(this.options.mediaGallerySelector).on('gallery:loaded', function (loadedGallery) {
                            loadedGallery = context.find(this.options.mediaGallerySelector).data('gallery');
                            loadedGallery.updateData(imagesToUpdate);
                        }.bind(this));
                    }

                    if (isInitial) {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    } else {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                            selectedOption: this.getProduct(),
                            dataMergeStrategy: this.options.gallerySwitchStrategy
                        });
                    }
                    gallery.first();
                } else if (justAnImage && justAnImage.img) {
                    context.find('.product-image-photo').attr('src', justAnImage.img);
                    Modernizr.on('webp', function(result) {
                        if (result) {
                            // custom replace images with webp images
                            self.convertImgToWebp(images, context, isInProductView, justAnImage);
                        }
                    });
                }
            },
            convertImgToWebp: function (images, context, isInProductView, justAnImage) {
                var gallery = context.find(this.options.mediaGallerySelector).data('gallery');

                if (justAnImage.img.indexOf('.webp') !== -1) {
                    context.find('[type="image/webp"]').attr('srcset', justAnImage.img);
                } else {
                    $.ajax({
                        url: BASE_URL + 'webp/image/convert',
                        data: {
                            images: images,
                            isInProductView: isInProductView
                        },
                        type: 'GET',
                        beforeSend: function () {
                            // add loading
                            if (isInProductView) {
                                // add loading for main .fotorama__active
                                $('.fotorama__stage .fotorama__img').addClass('swatch-option-loading-webp');

                                // add loading for thumb .fotorama__active
                                $('.fotorama__thumb img').addClass('swatch-option-loading-webp');
                            } else {
                                // add loading for main image in category page
                                context.find('.product-image-photo').addClass('swatch-option-loading-webp');
                            }
                        },
                        success: function (response) {
                            if (response.webpUrls.length != 0) {
                                // replace jpg/png image with webp image in category page only
                                if (!isInProductView) {
                                    // remove loading for main image in category page
                                    context.find('.product-image-photo').removeClass('swatch-option-loading-webp');
                                    // load image
                                    context.find('[type="image/webp"]').attr('srcset', response.webpUrls[0]['img']);
                                }

                                // replace jpg/png image with webp image in fotorama images in product page only
                                if (isInProductView) {
                                    // load image
                                    gallery.updateData(response.webpUrls);
                                    // remove loading for main .fotorama__active
                                    $('.fotorama__stage .fotorama__img').removeClass('swatch-option-loading-webp');

                                    // remove loading for thumb .fotorama__active
                                    $('.fotorama__thumb img').removeClass('swatch-option-loading-webp');
                                }
                            }
                        }
                    });
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});