
define(['jquery'], function ($) {
    'use strict';

    var mixin = {

        options: {
            mtConfig: {
                enabled: false,
                simpleProductId: null,
                useOriginalGallery: true,
                standaloneMode: false,
                currentProductId: null,
                galleryData: [],
                tools: {},
                thumbSwitcherOptions: {},
                mtContainerSelector: 'div.MagicToolboxContainer'
            }
        },

        /**
         * @private
         */
        _create: function () {

            this._super();

            var spConfig = this.options.jsonConfig;

            if (typeof(spConfig.magictoolbox) != 'undefined' && typeof(spConfig.productId) != 'undefined') {
                this.options.mtConfig.enabled = true;
                this.options.mtConfig.currentProductId = spConfig.productId;
                this.options.mtConfig.useOriginalGallery = spConfig.magictoolbox.useOriginalGallery;
                if (typeof(spConfig.magictoolbox.standaloneMode) != 'undefined') {
                    this.options.mtConfig.standaloneMode = spConfig.magictoolbox.standaloneMode;
                }
                this.options.mtConfig.galleryData = spConfig.magictoolbox.galleryData;
                this.options.mtConfig.tools = {
                    'Magic360': {
                        'idTemplate': '{tool}-{page}-{id}',
                        'objName': 'Magic360',
                        'undefined': true
                    },
                    'MagicSlideshow': {
                        'idTemplate': '{tool}-{page}-{id}',
                        'objName': 'MagicSlideshow',
                        'undefined': true
                    },
                    'MagicScroll': {
                        'idTemplate': '{tool}-{page}-{id}',
                        'objName': 'MagicScroll',
                        'undefined': true
                    },
                    'MagicZoomPlus': {
                        'idTemplate': '{tool}Image-{page}-{id}',
                        'objName': 'MagicZoom',
                        'undefined': true
                    },
                    'MagicZoom': {
                        'idTemplate': '{tool}Image-{page}-{id}',
                        'objName': 'MagicZoom',
                        'undefined': true
                    },
                    'MagicThumb': {
                        'idTemplate': '{tool}Image-{page}-{id}',
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
            }
        },

        /**
         * @private
         */
        _initThumbSwitcherOptions: function () {
            var container = $(this.options.mtConfig.mtContainerSelector);
            if (container.length && container.magicToolboxThumbSwitcher) {
                //NOTE: get thumb switcher options
                this.options.mtConfig.thumbSwitcherOptions = container.magicToolboxThumbSwitcher('getOptions');
            }
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @param {String|undefined} eventName
         * @private
         */
        _loadMedia: function (eventName) {
            var productId = null;
            if (!this.options.useAjax) {
                productId = this.getProduct();
                if (typeof(productId) == 'undefined') {
                    productId = null;
                }
            }

            this.options.mtConfig.simpleProductId = productId;

            this._super(eventName);
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @param {Boolean} isInProductView
         * @private
         */
        _ProductMediaCallback: function ($this, response, isInProductView) {
            //NOTE: init thumb switcher options
            if (!this.options.mtConfig.useOriginalGallery && !Object.keys(this.options.mtConfig.thumbSwitcherOptions).length) {
                this._initThumbSwitcherOptions();
            }

            if (response.variantProductId) {
                this.options.mtConfig.simpleProductId = response.variantProductId;
            } else {
                this.options.mtConfig.simpleProductId = null;
            }

            this._super($this, response, isInProductView);
        },

        /**
         * Set images types
         * @param {Array} images
         */
        _setImageType: function (images) {
            if (!this.options.mtConfig.enabled) {
                return this._super(images);
            }

            if (images.length) {
                images.map(function (img) {
                    if (!img.type) {
                        img.type = 'image';
                    }
                });
            }

            return images;
        },

        /**
         * Start update base image process based on event name
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         * @param {String|undefined} eventName
         */
        updateBaseImage: function (images, context, isInProductView, eventName) {
            if (typeof(this.processUpdateBaseImage) != 'undefined') {
                var gallery = context.find(this.options.mediaGallerySelector).data('gallery');

                if (eventName === undefined) {
                    this.updateBaseImageMagic(this.processUpdateBaseImage, images, context, isInProductView, gallery);
                } else {
                    context.find(this.options.mediaGallerySelector).on('gallery:loaded', function (loadedGallery) {
                        loadedGallery = context.find(this.options.mediaGallerySelector).data('gallery');
                        this.updateBaseImageMagic(this.processUpdateBaseImage, images, context, isInProductView, loadedGallery);
                    }.bind(this));
                }
                return;
            }
            this.updateBaseImageMagic(this._super, images, context, isInProductView, null);
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Function} parentMethod
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         * @param {Object} gallery
         */
        updateBaseImageMagic: function (parentMethod, images, context, isInProductView, gallery) {
            if (!this.options.mtConfig.enabled) {
                parentMethod.call(this, images, context, isInProductView, gallery);
                return;
            }

            var spConfig = this.options.jsonConfig,
                galleryData = [],
                tools = {};

            if (this.options.mtConfig.useOriginalGallery) {
                if (this.options.mtConfig.standaloneMode && isInProductView) {
                    this.updateSpin(isInProductView);
                }
                images = spConfig.images[this.options.mtConfig.simpleProductId];
                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                parentMethod.call(this, images, context, isInProductView, gallery);
                return;
            }

            var productId = spConfig.productId;
            if (this.options.mtConfig.simpleProductId) {
                productId = this.options.mtConfig.simpleProductId;
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

            var ids = {},
                id,
                uniqueId,
                newId,
                newUniqueId,
                page = isInProductView ? 'product' : 'category';

            //NOTE: stop tools
            for (var tool in tools) {
                if (tools[tool].undefined) {
                    continue;
                }

                id = tools[tool].idTemplate.replace('{page}', page).replace('{tool}', tool);

                if (spConfig.productId == this.options.mtConfig.currentProductId) {
                    uniqueId = id.replace('{id}', this.options.mtConfig.currentProductId);
                } else {
                    uniqueId = id.replace('{id}', spConfig.productId+'-'+this.options.mtConfig.currentProductId);
                }

                newId = id.replace('{id}', productId);
                newUniqueId = productId == spConfig.productId ? newId : id.replace('{id}', spConfig.productId+'-'+productId);

                id = id.replace('{id}', this.options.mtConfig.currentProductId);

                id = isInProductView ? id : uniqueId;

                ids[tool] = {
                    'id': id,
                    'newId': newId,
                    'uniqueId': uniqueId,
                    'newUniqueId': newUniqueId,
                };

                if (document.getElementById(id)) {
                    window[tools[tool].objName].stop(id);
                }
            }

            if (isInProductView) {
                //NOTE: stop MagiScroll on selectors
                var selectorsElId = 'MagicToolboxSelectors'+this.options.mtConfig.currentProductId,
                    selectorsEl = document.getElementById(selectorsElId);
                if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                    MagicScroll.stop(selectorsElId);
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
                    if (typeof(ids['MagicZoomPlus']) != 'undefined') {
                        tool = 'MagicZoomPlus';
                        toolMainNode = galleryDataNode.find('a.MagicZoom');
                        toolLinkAttrName = 'data-zoom-id';
                    } else if (typeof(ids['MagicZoom']) != 'undefined') {
                        tool = 'MagicZoom';
                        toolMainNode = galleryDataNode.find('a.MagicZoom');
                        toolLinkAttrName = 'data-zoom-id';
                    } else if (typeof(ids['MagicThumb']) != 'undefined') {
                        tool = 'MagicThumb';
                        toolMainNode = galleryDataNode.find('a.MagicThumb');
                        toolLinkAttrName = 'data-thumb-id';
                    } else if (typeof(ids['MagicSlideshow']) != 'undefined') {
                        tool = 'MagicSlideshow';
                        //NOTE: main product slides
                        mpSlides = mpGalleryDataNode.find('.MagicSlideshow').children();
                    } else if (typeof(ids['MagicScroll']) != 'undefined') {
                        tool = 'MagicScroll';
                        //NOTE: main product slides
                        mpSlides = mpGalleryDataNode.find('.MagicScroll').children();
                    }

                    //NOTE: main product selectors
                    mpSelectors = mpGalleryDataNode.find('#MagicToolboxSelectors' + spConfig.productId + ' a');

                    if (mpSelectors.length) {
                        //NOTE: when there are no images in the gallery (only video or spin)
                        if (!toolMainNode.length) {
                            galleryDataNode.find('#mtImageContainer').html(mpGalleryDataNode.find('#mtImageContainer').html());
                            toolMainNode = galleryDataNode.find('a.' + tools[tool].objName);
                            toolMainNode.attr('id', ids[tool].newId);
                        }

                        mpSelectors.filter('[' + toolLinkAttrName + ']').attr(toolLinkAttrName, ids[tool].newId);

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
                selectorsElId = 'MagicToolboxSelectors'+productId;
                selectorsEl = document.getElementById(selectorsElId);
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

                    MagicScroll.start(selectorsElId);
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
            } else {
                //NOTE: replace gallery
                var galleryHtml = galleryData[productId];
                for (var tool in ids) {
                    galleryHtml = galleryHtml.replace('id="'+ids[tool].newId+'"', 'id="'+ids[tool].newUniqueId+'"');
                }
                context.find('div.MagicToolboxContainer').replaceWith(galleryHtml);
            }

            //NOTE: update current product id
            this.options.mtConfig.currentProductId = productId;

            //NOTE: start tools
            for (var tool in ids) {
                id = isInProductView ? ids[tool].newId : ids[tool].newUniqueId;
                if (document.getElementById(id)) {
                    window[tools[tool].objName].start(id);
                }
            }

        },

        /**
         * Update 360 spin
         * @param {Boolean} isInProductView
         */
        updateSpin: function (isInProductView) {
            var spConfig = this.options.jsonConfig,
                productId = spConfig.productId,
                galleryData = this.options.mtConfig.galleryData,
                tools = this.options.mtConfig.tools;

            if (this.options.mtConfig.simpleProductId) {
                productId = this.options.mtConfig.simpleProductId;
            }

            //NOTE: associated product has no spin
            if (!galleryData[productId].length) {
                productId = spConfig.productId;
            }

            //NOTE: there is no need to update spin
            if (this.options.mtConfig.currentProductId == productId) {
                return;
            }

            var id,
                uniqueId,
                newId,
                newUniqueId,
                page = isInProductView ? 'product' : 'category';

            //NOTE: stop tool
            if (tools['Magic360'].undefined) {
                return;
            }
            id = tools['Magic360'].idTemplate.replace('{page}', page).replace('{tool}', 'Magic360');
            if (spConfig.productId == this.options.mtConfig.currentProductId) {
                uniqueId = id.replace('{id}', this.options.mtConfig.currentProductId);
            } else {
                uniqueId = id.replace('{id}', spConfig.productId+'-'+this.options.mtConfig.currentProductId);
            }
            newId = id.replace('{id}', productId);
            newUniqueId = (productId == spConfig.productId ? newId : id.replace('{id}', spConfig.productId+'-'+productId));
            id = id.replace('{id}', this.options.mtConfig.currentProductId);
            id = isInProductView ? id : uniqueId;
            if (document.getElementById(id)) {
                window[tools['Magic360'].objName].stop(id);
            }

            if (isInProductView) {
                //NOTE: replace gallery
                $(this.options.mtConfig.mtContainerSelector).replaceWith(galleryData[productId]);
            }

            //NOTE: update current product id
            this.options.mtConfig.currentProductId = productId;

            //NOTE: start tool
            id = isInProductView ? newId : newUniqueId;
            if (document.getElementById(id)) {
                window[tools['Magic360'].objName].start(id);
            }
        }
    };

    return mixin;
});
