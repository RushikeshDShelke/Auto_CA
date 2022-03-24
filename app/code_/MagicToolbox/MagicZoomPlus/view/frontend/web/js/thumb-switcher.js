
define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Thumb switcher widget
     */
    $.widget('mage.magicToolboxThumbSwitcher', {

        options: {
            tool: null,
            productId: '',
            switchMethod: 'btnclick',
            isMagicZoom: false,
            mainContainerId: 'mtImageContainer',
            magic360ContainerId: 'mt360Container',
            videoContainerId: 'mtVideoContainer',
            mainContainer: null,
            magic360Container: null,
            videoContainer: null,
            mainThumbRegExp: /(?:\s|^)(?:mz\-thumb|MagicThumb\-swap)(?:\s|$)/,
            magic360ThumbRegExp: /(?:\s|^)m360\-selector(?:\s|$)/,
            videoThumbRegExp: /(?:\s|^)video\-selector(?:\s|$)/,
            thumbs: [],
            needVideoPlayer: false,
            needVimeoJSFramework: false,
            playIfBase: 0,
            showRelated: 0,
            videoAutoRestart: 0,
            prevVideoId: null,
            froogaloop2Loaded: false
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () {

            var options = this.options,
                videoUrl = '',
                selectorsContainer = null,
                aTags = [];

            options.mainContainer = document.getElementById(options.mainContainerId);
            options.magic360Container = document.getElementById(options.magic360ContainerId);
            options.videoContainer = document.getElementById(options.videoContainerId);

            options.isMagicZoom = (options.tool == 'magiczoom' || options.tool == 'magiczoomplus');

            this.options.thumbs = [];
            selectorsContainer = this.element.find('.MagicToolboxSelectorsContainer');
            if (selectorsContainer.length) {
                aTags = Array.prototype.slice.call(
                    selectorsContainer.get(0).getElementsByTagName('a')
                );
            }
            for (var i = 0; i < aTags.length; i++) {
                if (aTags[i].getElementsByTagName('img').length) {
                    options.thumbs.push(aTags[i]);
                    if (options.isMagicZoom && aTags[i].className.match(options.videoThumbRegExp)) {
                        options.needVideoPlayer = true;
                        videoUrl = aTags[i].getAttribute('data-video');
                        if (videoUrl && videoUrl.match(/(?:www\.|player\.)?vimeo\.com/)) {
                            options.needVimeoJSFramework = true;
                        }
                    }
                } else {
                    //console.log('Wrong selector: ', aTags[i]);
                }
            }

            //NOTE: if we have only one base video (without selector)
            if (!options.needVideoPlayer) {
                var initVideo = $(this.options.videoContainer).find('.init-video');
                if (initVideo.length) {
                    options.needVideoPlayer = true;
                    videoUrl = initVideo.attr('data-video');
                    if (videoUrl && videoUrl.match(/(?:www\.|player\.)?vimeo\.com/)) {
                        options.needVimeoJSFramework = true;
                    }
                }
            }

            if (options.needVideoPlayer) {

                //NOTE: load vimeo js framework
                if (options.needVimeoJSFramework) {
                    if (typeof(window.$f) == 'undefined') {
                        var firstScriptTag = document.getElementsByTagName('script')[0],
                            newScriptTag = document.createElement('script');
                        newScriptTag.async = true;
                        newScriptTag.src = 'https://f.vimeocdn.com/js/froogaloop2.min.js';
                        newScriptTag.onload = function() {
                            //NOTE: flag to check if froogaloop2 library is loaded
                            options.froogaloop2Loaded = true;
                        }
                        firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
                    } else {
                        //NOTE: froogaloop2 library is already loaded
                        options.froogaloop2Loaded = true;
                    }
                }

                //NOTE: load player loader
                if (!$.isFunction($.fn.productVideoLoader)) {
                    require(['jquery', 'loadPlayer'], $.proxy(function () {
                        if (this.options.videoContainer) {
                            //NOTE: init base video
                            var initVideo = $(this.options.videoContainer).find('.init-video');
                            if (initVideo.length) {
                                this._initVideo(initVideo.data('video'));
                            }
                        }
                    }, this));
                } else {
                    //NOTE: init base video
                    var initVideo = $(this.options.videoContainer).find('.init-video');
                    if (initVideo.length) {
                        this._initVideo(initVideo.data('video'));
                    }
                }
            }

            if (options.mainContainer && options.magic360Container && options.videoContainer && options.thumbs.length) {
                this._bind();
            }

            //NOTE: start MagicScroll on page load
            var id = 'MagicToolboxSelectors'+options.productId,
                selectorsEl = document.getElementById(id),
                            //NOTE: MagicScroll object exists
                needStart = (typeof(window['MagicScroll']) != 'undefined') &&
                            //NOTE: element with MagicScroll class exists
                            selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/) &&
                            //NOTE: MagicScroll was not stated before
                            !selectorsEl.getAttribute('ms-started');

            if (needStart) {
                selectorsEl.removeAttribute('ms-started');

                //NOTE: fix orientation before start (for left and right templates)
                if (window.matchMedia('(max-width: 767px)').matches) {
                    var mtContainer = document.querySelector('.MagicToolboxContainer'),
                        dataOptions = selectorsEl.getAttribute('data-options') || '';
                    if (mtContainer && mtContainer.className.match(/(?:\s|^)selectorsLeft|selectorsRight(?:\s|$)/)) {
                        selectorsEl.setAttribute(
                            'data-options',
                            dataOptions.replace(/\borientation *\: *vertical\b/, 'orientation: horizontal')
                        );
                    }
                }

                MagicScroll.start(id);
            }
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function () {

            var switchMethod = this.options.switchMethod,
                thumbs = this.options.thumbs;

            if (this.options.isMagicZoom) {
                switchMethod = (switchMethod == 'click' ? 'btnclick' : switchMethod);
            }

            var switchThumbFn = $.proxy(this._switchThumb, this);
            for (var i = 0; i < thumbs.length; i++) {
                $mjs(thumbs[i]).jAddEvent(switchMethod+' tap', switchThumbFn, 1);
            }
        },

        /**
         * Switch thumb
         * @param {jQuery.Event} event
         * @private
         */
        _switchThumb: function(event) {

            var options = this.options,
                thumbs = options.thumbs,
                objThis = event.target || event.srcElement,
                toolMainId,
                isMagic360Thumb,
                isVideoThumb,
                isMagic360Hidden,
                isVideoHidden;

            if (!options || !options.mainContainer) {
                return false;
            }

            if (options.isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            if (objThis.tagName.toLowerCase() == 'img') {
                objThis = objThis.parentNode;
            }

            isMagic360Thumb = objThis.className.match(options.magic360ThumbRegExp);
            isVideoThumb = objThis.className.match(options.videoThumbRegExp);
            isMagic360Hidden = options.magic360Container.style.display == 'none';
            isVideoHidden = options.videoContainer.style.display == 'none';

            if (!isVideoThumb && options.isMagicZoom) {
                if ($(options.videoContainer).find('iframe').length) {
                    $(options.videoContainer).find('.product-video').productVideoLoader('stop');
                }
            }

            if (isMagic360Thumb && isMagic360Hidden) {
                //NOTE: the 360 container was hidden when clicking on the 360 thumbnail
                options.mainContainer.style.display = 'none';
                options.videoContainer.style.display = 'none';
                options.magic360Container.style.display = 'block';
            } else if (isVideoThumb) {
                //NOTE: clicking on the video thumbnail
                if (options.isMagicZoom) {
                    if (isVideoHidden) {
                        //NOTE: the video container was hidden
                        options.mainContainer.style.display = 'none';
                        options.magic360Container.style.display = 'none';
                        options.videoContainer.style.display = 'block';
                    }
                    this._initVideo($(objThis).data('video'));
                } else {
                    //NOTE: display main container for MagicThumb
                    options.videoContainer.style.display = 'none';
                    options.magic360Container.style.display = 'none';
                    options.mainContainer.style.display = 'block';
                }
            } else if (!(isMagic360Thumb || isVideoThumb)) {
                //NOTE: the main container was hidden when clicking on the main thumbnail
                options.videoContainer.style.display = 'none';
                options.magic360Container.style.display = 'none';
                options.mainContainer.style.display = 'block';
                if (options.isMagicZoom) {
                    toolMainId = 'MagicZoom' + (options.tool == 'magiczoom' ? '' : 'Plus') + 'Image-product-' + options.productId;
                    //NOTE: hide image to skip magiczoom(plus) switching effect
                    if (!$mjs(objThis).jHasClass('mz-thumb-selected')) {
                        document.querySelector('#'+toolMainId+' .mz-figure > img').style.visibility = 'hidden';
                    }
                    //NOTE: switch image
                    MagicZoom.switchTo(toolMainId, objThis);
                }
            }

            if (options.isMagicZoom) {
                allowHighlightActiveSelectorOnUpdate = false;
            }
            //NOTE: to highlight magic360 selector when switching thumbnails
            for (var i = 0; i < thumbs.length; i++) {
                $mjs(thumbs[i]).jRemoveClass('active-selector');
            }
            $mjs(objThis).jAddClass('active-selector');

            return false;
        },

        /**
         * Init video
         * @param videoUrl
         * @private
         */
        _initVideo: function(videoUrl) {
            var options = this.options,
                videoContainer = options.videoContainer,
                aTag = document.createElement('a'),
                videoCode = null,
                videoType = 'youtube';

            aTag.href = videoUrl;

            if (aTag.host.match(/youtube\.com|youtu\.be/)) {
                var regExp1 = /\bv=([^&]+)(?:&|$)/,
                    regExp2 = /^\/?(?:embed\/|v\/)?([^\/\?]+)(?:\/|\?|$)/;
                if (aTag.search.match(regExp1)) {
                    videoCode = aTag.search.match(regExp1)[1];
                } else if (aTag.pathname.match(regExp2)) {
                    videoCode = aTag.pathname.match(regExp2)[1];
                }
            } else if (aTag.host.match(/(?:www\.|player\.)?vimeo\.com/)) {
                var regExp3 = /\/(?:channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|album\/[^\/]+\/video\/|video\/|)(\d+)(?:\/|\?|$)/;
                videoType = 'vimeo';
                if (aTag.pathname.match(regExp3)) {
                    videoCode = aTag.pathname.match(regExp3)[1];
                }
            }

            if (videoCode && (options.prevVideoId != videoType+'-'+videoCode)) {
                $(videoContainer).find('.product-video').remove();
                $(videoContainer).append(
                    '<div class="product-video" ' +
                    'data-related="' + options.showRelated + '" ' +
                    'data-loop="' + options.videoAutoRestart + '" ' +
                    'data-type="' + videoType + '" ' +
                    'data-code="' + videoCode + '" ' +
                    'data-width="100%" data-height="100%"></div>'
                );
                if (videoType == 'vimeo') {
                    //NOTE: make sure the froogaloop2 library is loaded
                    var load = function() {
                        if (options.froogaloop2Loaded) {
                            $(videoContainer).find('.product-video').productVideoLoader();
                        } else {
                            setTimeout(load, 200);
                        }
                    };
                    load();
                } else {
                    $(videoContainer).find('.product-video').productVideoLoader();
                }
                options.prevVideoId = videoType+'-'+videoCode;
            }
        },

        /**
         * Get options
         * @public
         */
        getOptions: function () {
            return {
                playIfBase: this.options.playIfBase,
                showRelated: this.options.showRelated,
                videoAutoRestart: this.options.videoAutoRestart,
                tool: this.options.tool,
                switchMethod: this.options.switchMethod,
                productId: this.options.productId
            };
        }
    });

    return $.mage.magicToolboxThumbSwitcher;
});
