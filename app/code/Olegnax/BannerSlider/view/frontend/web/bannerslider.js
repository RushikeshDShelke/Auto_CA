define( [
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'jquery-ui-modules/effect',
    'owl.carousel',
], function ( $ ) {
    'use strict';

    var animation_text_space = 0;

    $.widget( 'mage.OxBannerSlider', {
        options: {
// OxBannerSlider
            autoScroll: 0,
            slideWidth: 0,
            slideHeight: 0,
            customNav: true,
// Owl.carousel
            dots: false,
            rewind: false,
            items: 1,
            slideBy: 1,
            lazyLoad: true,
            responsive: {
                0: {
                    items: 1,
                },
            },
            nav: false,
        },
        _create: function () {
            this.options.responsiveBaseElement = this.element.parent();
            this.options.autoplayTimeout = parseInt( this.options.autoScroll ) * 1000;
            this.options.autoplay = 0 < this.options.autoplayTimeout;
            this.options.autoplayHoverPause = this.options.autoplay;

            var options = this._createOptions();
            this.element.owlCarousel( options );
            this.arrows();
        },

        _createOptions: function () {
            var _self = this;
            var options = {
                onInitialize: function () {
                    var $carousel = this.$element,
                        $items = $carousel.find( '.ox-banner__slide' ),
                        $text_container = $carousel.find( '.ox-banner__text-container' ),
                        $text = $text_container.find( '.text' ),
                        $link = $text_container.find( '.link' );
                    this.$element.data( 'textSize', parseInt( $text.css( 'font-size' ), 10 ) );
                    this.$element.data( 'textLineHeight', parseInt( $text.css( 'line-height' ), 10 ) );
                    this.$element.data( 'linkSize', parseInt( $link.css( 'font-size' ), 12 ) );

                    $text_container.show();
                    $text.wrap( '<div class="animation-wrapper animation-text" />' );
                    $link.wrap( '<div class="animation-wrapper animation-link" />' );
                    $carousel.find( '.ox-banner__text-container br' ).hide();

                    $carousel.find( '.ox-banner__text-container.center' ).each( function () {
                        $( this ).attr( 'data-margin', $( this ).css( 'margin' ) );
                    } );

                    _self._initBannerText( $carousel );

                    //banner hover
                    $items.hover(
                        function () {
                            $( '.ox-banner__text-container .animation-wrapper', this ).each( function ( i ) {
                                $( this )
                                    .delay( 64 * ( i ) )
                                    .queue( function ( next ) {
                                        $( this ).addClass( 'animate-me' );
                                        next();
                                    } );
                            } );
                        },
                        function () {
                            $( '.ox-banner__text-container .animation-wrapper', this ).each( function ( i ) {
                                $( this )
                                    .delay( 64 * i )
                                    .queue( function ( next ) {
                                        $( this ).removeClass( 'animate-me' );
                                        next();
                                    } );
                            } );
                        }
                    );
                },
                onInitialized: function () {
                    this.$element.closest( '.ox-banners-slider__container' ).eq( 0 ).css('height', '');;
                    _self._resizeSlides(this.$element, this);
                    _self.__initBannerText(this.$element);
                    _self._afterMove(this.$element);

                },
                onRefresh: function () {
                    this.$element.find( '.ox-banner__text-container .animation-wrapper' ).css( 'width', '0px' );
                },
                onRefreshed: function () {
                    _self._resizeSlides( this.$element, this );
                    _self.__initBannerText( this.$element );
                    _self._afterMove( this.$element );
                },
                onChanged: function () {
                    var carouselId = this.$element.attr( 'id' );
                    var $wraper = this.$element.closest( '.ox-banners-slider__container' ).eq( 0 );
                    var current = this.current();
                    var $nav = $wraper.find( '#' + carouselId + '_nav' ).toggle( this.settings.items < this.items().length );
                    var disable_nav_min = current === this.minimum();
                    var disable_nav_max = current === this.maximum();
                    if ( this.settings.loop ) {
                        disable_nav_min = disable_nav_max = false;
                    }
                    $nav.find( '.ox-owl-next' ).toggleClass( 'disabled', disable_nav_max );
                    $nav.find( '.ox-owl-prev' ).toggleClass( 'disabled', disable_nav_min );
                    _self._afterMove( this );
                }
            };

            return $.extend( { }, options, this.options );

        },
        __initBannerText: function ( $this ) {
            var _self = this;
            setTimeout( function () {
                _self._initBannerText( $this );
            }, 200 );
        },
        _initBannerText: function ( $this ) {
            var carouselHeight = $this.find( '.ox-banner__slide .ox-banner__link-wrapper' ).height();

            $( '.animation-wrapper', $this ).removeAttr( 'style' ).attr( { 'data-width': '', 'data-height': '' } );
            $( '.ox-banner__text-container .text, .ox-banner__text-container .link', $this ).each( function () {
                var w = $( this ).outerWidth( !0 ) + animation_text_space,
                    h = $( this ).outerHeight();

                $( this ).parent()
                    .attr( 'data-width', w )
                    .attr( 'data-height', h )
                    .width( 0 )
                    .height( h );
            } );
            $( '.ox-banner__text-container.center', $this ).each( function () {
                $( this )
                    .css( 'margin', $( this ).attr( 'data-margin' ) )
                    .css( 'margin-top', parseInt( ( carouselHeight - $( this ).height() ) / 2 ) + 'px' );
            } );
        },
        _afterMove: function ( $this ) {
            var animationDelay = this.options.animationDelay;
            var animationTimeout = this.options.animationTimeout;
            setTimeout( function () {
                $.each( $this._items, function ( index, item ) {
                    var text_animation = $( '.ox-banner__text-container .animation-wrapper', item );
                    if ( item.hasClass( 'active' ) ) {
                        text_animation.each( function ( i ) {
                            $( this ).delay( animationTimeout * i ).animate( { width: $( this ).attr( 'data-width' ) }, animationDelay, 'easeOutExpo' );
                        } );
                    } else {
                        text_animation.css( 'width', 0 );
                    }

                } );
            }, 400 );
        },

        _resizeSlides: function ( $this, owl ) {
            var carousel = owl.$element;
            var newWidth = $( owl._items[0] ).width(),
                newHeight = Math.round( newWidth * this.options.slideHeight / this.options.slideWidth ),
                ratio = $( owl._items[0] ).width() / this.options.slideWidth,
                newTextSize = Math.ceil( $( carousel ).data( 'textSize' ) * ratio ),
                newTextLineHeight = Math.ceil( $( carousel ).data( 'textLineHeight' ) * ratio ) + 1,
                newLinkSize = Math.ceil( $( carousel ).data( 'linkSize' ) * ratio );

            $( '.ox-banner__slide .ox-banner__link-wrapper', carousel ).width( newWidth ).height( newHeight );
            $( '.ox-banner__slide .ox-banner-slider__image', carousel ).css( {
                'transform': 'scale(' + ratio + ')',
                '-ms-transform': 'scale(' + ratio + ')',
                '-webkit-transform': 'scale(' + ratio + ')'
            } );

            $( '.ox-banner__slide .ox-banner__text-container .text', carousel ).css( 'font-size', newTextSize + 'px' );
            $( '.ox-banner__slide .ox-banner__text-container .text', carousel ).css( 'line-height', newTextLineHeight + 'px' );
            $( '.ox-banner__slide .ox-banner__text-container .link', carousel ).css( 'font-size', newLinkSize + 'px' );

            $( carousel ).animate( { height: newHeight + 'px' }, 1000, 'easeOutExpo' );
        },
        arrows: function () {
            if(this.options.customNav){
                var $wraper = this.element.closest( '.ox-banners-slider__container' ).eq( 0 );
                $wraper.on( 'click', '.ox-owl-next', $.proxy( function () {
                    this.element.trigger( 'next.owl.carousel' );
                }, this ) );
                $wraper.on( 'click', '.ox-owl-prev', $.proxy( function () {
                    this.element.trigger( 'prev.owl.carousel' );
                }, this ) );
            }
        },
    } );

    return $.mage.OxBannerSlider;
} );
