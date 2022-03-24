define( [
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'jquery-ui-modules/effect',
    'owl.carousel',
], function ( $ ) {
    'use strict';

    var animation_text_space = 0;

    $.widget( 'mage.OXBrandsSlider', {
        options: {
// OxBannerSlider
            autoScroll: 0,
            slideWidth: 0,
            slideHeight: 0,
// Owl.carousel
            rewind: false,
            items: 1,
            slideBy: 1,
            responsive: {
                0: {
                    items: 1,
                },
            },
            dots: false,
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
                onInitialized: function () {
                    _self._resizeSlides( this.$element, this );
                },
                onRefreshed: function () {
                    _self._resizeSlides( this.$element, this );
                },
                onChanged: function () {
                    var carouselId = this.$element.attr( 'id' );
                    var $wraper = this.$element.closest( '.ox-brand-slider__container' ).eq( 0 );
                    var current = this.current();
                    var $nav = $wraper.find( '#' + carouselId + '_nav' ).toggle( this.settings.items < this.items().length );
                    var disable_nav_min = current === this.minimum();
                    var disable_nav_max = current === this.maximum();
                    if ( this.settings.loop ) {
                        disable_nav_min = disable_nav_max = false;
                    }
                    $nav.find( '.ox-owl-next' ).toggleClass( 'disabled', disable_nav_max );
                    $nav.find( '.ox-owl-prev' ).toggleClass( 'disabled', disable_nav_min );
                }
            };

            return $.extend( { }, options, this.options );

        },

        _resizeSlides: function ( $this, owl ) {
            var carousel = owl.$element;
            var newWidth = $( owl._items[0] ).width(),
                newHeight = Math.round( newWidth * this.options.slideHeight / this.options.slideWidth ),
                ratio = $( owl._items[0] ).width() / this.options.slideWidth;

            $( '.ox-brand__slide .ox-brand-slider__image', carousel ).css( {
                'transform': 'scale(' + ratio + ')',
                '-ms-transform': 'scale(' + ratio + ')',
                '-webkit-transform': 'scale(' + ratio + ')'
            } );

            $( carousel ).animate( { height: newHeight + 'px' }, 1000, 'easeOutExpo' );
        },
        arrows: function () {
            var $wraper = this.element.closest( '.ox-brand-slider__container' ).eq( 0 );
            $wraper.on( 'click', '.ox-owl-next', $.proxy( function () {
                this.element.trigger( 'next.owl.carousel' );
            }, this ) );
            $wraper.on( 'click', '.ox-owl-prev', $.proxy( function () {
                this.element.trigger( 'prev.owl.carousel' );
            }, this ) );
        }
    } );

    return $.mage.OXBrandsSlider;
} );
