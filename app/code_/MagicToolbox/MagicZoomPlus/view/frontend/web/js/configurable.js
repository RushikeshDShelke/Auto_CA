/*jshint browser:true jquery:true*/

define([
    'jquery',
    'mage/template',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.configurable', $.mage.configurable, {

        options: {
            mtConfig: {
                enabled: false,
                useOriginalGallery: true,
                currentProductId: null,
                galleryData: [],
                tools: {},
                thumbSwitcherOptions: {},
                mtContainerSelector: 'div.MagicToolboxContainer'
            }
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {

            this._super();

            if (typeof(this.options.spConfig.magictoolbox) == 'undefined' || typeof(this.options.spConfig.productId) == 'undefined') {
                return;
            }

            this.options.mtConfig.enabled = true;
            this.options.mtConfig.currentProductId = this.options.spConfig.productId;
            this.options.mtConfig.useOriginalGallery = this.options.spConfig.magictoolbox.useOriginalGallery;
            this.options.mtConfig.galleryData = this.options.spConfig.magictoolbox.galleryData;
            this.options.mtConfig.tools = {
                'Magic360': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'Magic360',
                    'undefined': true
                },
                'MagicSlideshow': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'MagicSlideshow',
                    'undefined': true
                },
                'MagicScroll': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'MagicScroll',
                    'undefined': true
                },
                'MagicZoomPlus': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicZoom',
                    'undefined': true
                },
                'MagicZoom': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicZoom',
                    'undefined': true
                },
                'MagicThumb': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicThumb',
                    'undefined': true
                }
            };
            for (var tool in this.options.mtConfig.tools) {
                this.options.mtConfig.tools[tool].undefined = (typeof(window[tool]) == 'undefined');
            }
            if (!this.options.mtConfig.tools['MagicZoom'].undefined) {
                var suffix = MagicZoom.version.indexOf('Plus') > -1 ? 'Plus' : '';
                this.options.mtConfig.tools['MagicZoom'].undefined = true;
                this.options.mtConfig.tools['MagicZoomPlus'].undefined = true;
                this.options.mtConfig.tools['MagicZoom' + suffix].undefined = false;
            }

            //NOTE: get thumb switcher options
            var container = $(this.options.mtConfig.mtContainerSelector);
            if (container.length && container.magicToolboxThumbSwitcher) {
                this.options.mtConfig.thumbSwitcherOptions = container.magicToolboxThumbSwitcher('getOptions');
            }
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         * @private
         */
        _changeProductImage: function () {
            if (!this.options.mtConfig.enabled || this.options.mtConfig.useOriginalGallery) {
                this._super();
                return;
            }

            var spConfig = this.options.spConfig,
                productId = spConfig.productId,
                galleryData = [],
                tools = {};

            if (typeof(this.simpleProduct) != 'undefined') {
                productId = this.simpleProduct;
            }

            galleryData = this.options.mtConfig.galleryData;

            //NOTE: associated product has no images
            if (!galleryData[productId].length) {
                productId = spConfig.productId;
            }

            //NOTE: there is no need to change gallery
            if (this.options.mtConfig.currentProductId == productId) {
                return;
            }

            tools = this.options.mtConfig.tools;

            //NOTE: stop tools
            for (var tool in tools) {
                if (tools[tool].undefined) {
                    continue;
                }
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].stop(id);
                }
            }

            //NOTE: stop MagiScroll on selectors
            var id = 'MagicToolboxSelectors'+this.options.mtConfig.currentProductId,
                selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                MagicScroll.stop(id);
            }

            //NOTE: replace gallery
            if (this.options.gallerySwitchStrategy === 'prepend' && productId != spConfig.productId) {
                var tool = null,
                    galleryDataNode = document.createElement('div'),
                    toolMainNode = null,
                    toolLinkAttrName = null,
                    mpGalleryDataNode = document.createElement('div'),
                    mpSelectors = null,
                    mpSpinSelector = null,
                    mpSlides = null;

                //NOTE: selected product gallery
                galleryDataNode = $(galleryDataNode).html(galleryData[productId]);

                //NOTE: main product gallery
                mpGalleryDataNode = $(mpGalleryDataNode).html(galleryData[spConfig.productId]);

                //NOTE: determine main tool
                if (galleryData[productId].indexOf('MagicZoomPlus') > -1 || galleryData[spConfig.productId].indexOf('MagicZoomPlus') > -1) {
                    tool = 'MagicZoomPlus';
                    toolMainNode = galleryDataNode.find('a.MagicZoom');
                    toolLinkAttrName = 'data-zoom-id';
                } else if (galleryData[productId].indexOf('MagicZoom') > -1 || galleryData[spConfig.productId].indexOf('MagicZoom') > -1) {
                    tool = 'MagicZoom';
                    toolMainNode = galleryDataNode.find('a.MagicZoom');
                    toolLinkAttrName = 'data-zoom-id';
                } else if (galleryData[productId].indexOf('MagicThumb') > -1 || galleryData[spConfig.productId].indexOf('MagicThumb') > -1) {
                    tool = 'MagicThumb';
                    toolMainNode = galleryDataNode.find('a.MagicThumb');
                    toolLinkAttrName = 'data-thumb-id';
                } else if (galleryData[productId].indexOf('MagicSlideshow') > -1 || galleryData[spConfig.productId].indexOf('MagicSlideshow') > -1) {
                    tool = 'MagicSlideshow';
                    //NOTE: main product slides
                    mpSlides = mpGalleryDataNode.find('.MagicSlideshow').children();
                } else if (galleryData[productId].indexOf('MagicScroll') > -1 || galleryData[spConfig.productId].indexOf('MagicScroll') > -1) {
                    tool = 'MagicScroll';
                    //NOTE: main product slides
                    mpSlides = mpGalleryDataNode.find('.MagicScroll').children();
                }

                mpSelectors = mpGalleryDataNode.find('#MagicToolboxSelectors' + spConfig.productId + ' a');

                if (mpSelectors.length) {
                    var newId = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', productId);

                    //NOTE: when there are no images in the gallery (only video or spin)
                    if (!toolMainNode.length) {
                        galleryDataNode.find('#mtImageContainer').html(mpGalleryDataNode.find('#mtImageContainer').html());
                        toolMainNode = galleryDataNode.find('a.' + tools[tool].objName);
                        toolMainNode.attr('id', newId);
                    }

                    mpSelectors.filter('[' + toolLinkAttrName + ']').attr(toolLinkAttrName, newId);

                    mpSelectors.removeClass('active-selector');

                    var mpSpinSelector = mpSelectors.filter('.m360-selector'),
                        spinSelector = null;
                    //NOTE: if we have main product spin
                    if (mpSpinSelector.length) {
                        //NOTE: don't add it with others
                        mpSelectors = mpSelectors.filter(':not(.m360-selector)');

                        spinSelector = galleryDataNode.find('#MagicToolboxSelectors' + productId + ' a.m360-selector');
                        //NOTE: if we don't have selected product spin
                        if (!spinSelector.length) {
                            //NOTE: append spin selector
                            galleryDataNode.find('#MagicToolboxSelectors' + productId).prepend(mpSpinSelector);
                            //NOTE: append spin
                            var spinContainer = mpGalleryDataNode.find('#mt360Container').css('display', 'none'),
                                spin = spinContainer.find('.Magic360'),
                                spinId = spin.attr('id');

                            spinId = spinId.replace(/\-\d+$/, '-'+productId);
                            //NOTE: fix spin id
                            spin.attr('id', spinId);

                            //NOTE: add spin
                            galleryDataNode.find('#mt360Container').replaceWith(spinContainer);
                        }
                    }

                    galleryDataNode.find('.MagicToolboxSelectorsContainer').removeClass('hidden-container');
                    galleryDataNode.find('#MagicToolboxSelectors' + productId).append(mpSelectors);
                }

                if (mpSlides && mpSlides.length) {
                    galleryDataNode.find('.' + tool).append(mpSlides);
                }

                $(this.options.mtConfig.mtContainerSelector).replaceWith(galleryDataNode.html());
            } else {
                $(this.options.mtConfig.mtContainerSelector).replaceWith(galleryData[productId]);
            }

            //NOTE: start MagiScroll on selectors
            id = 'MagicToolboxSelectors'+productId;
            selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                //NOTE: to do not start MagicScroll with thumb switcher
                selectorsEl.setAttribute('ms-started', true);

                //NOTE: fix orientation before start (for left and right templates)
                if (window.matchMedia('(max-width: 767px)').matches) {
                    var mtContainer = document.querySelector('.MagicToolboxContainer'),
                        dataOptions = selectorsEl.getAttribute('data-options') || '';
                    if (mtContainer && mtContainer.className.match(/(?:\s|^)selectorsLeft|selectorsRight(?:\s|$)/)) {
                        selectorsEl.setAttribute(
                            'data-options',
                            dataOptions.replace(/\borientation *\: *vertical\b/, 'orientation:horizontal')
                        );
                    }
                }

                MagicScroll.start(id);
            }

            //NOTE: initialize thumb switcher widget
            var container = $(this.options.mtConfig.mtContainerSelector);
            if (container.length) {
                this.options.mtConfig.thumbSwitcherOptions.productId = productId;
                if (container.magicToolboxThumbSwitcher) {
                    container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                } else {
                    //NOTE: require thumb switcher widget
                    /*
                    require(["magicToolboxThumbSwitcher"], function ($) {
                        container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                    });
                    */
                }
            }

            //NOTE: update current product id
            this.options.mtConfig.currentProductId = productId;

            //NOTE: start tools
            for (var tool in tools) {
                if (tools[tool].undefined) {
                    continue;
                }
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].start(id);
                }
            }
        }
    });

    return $.mage.configurable;
});
