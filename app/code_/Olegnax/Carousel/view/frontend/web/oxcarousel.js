define( [
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'jquery-ui-modules/effect',
    'owl.carousel',
], function ( $ ) {
    'use strict';

    $.widget( 'mage.OxCarousel', {
        options: {
// OxCarousel
            autoScroll: 0,
            autoplayHoverPause: 0,
// Owl.carousel
            rewind: false,
            items: 1,
            slideBy: 1,
            lazyLoad: true,
            responsive: {
                0: {
                    items: 1,
                },
            },
            dotsData: true,
            dotData: true,
            dots: true,
            nav: false,
        },
        _create: function () {
            this.options.autoplayTimeout = parseInt( this.options.autoScroll ) * 1000;
            this.options.autoplay = 0 < this.options.autoplayTimeout;
            this.options.autoplayHoverPause = this.options.autoplayHoverPause && this.options.autoplay;
            this._dots(this);
            var options = this._createOptions();
            this.element.owlCarousel( options );
        },

        _createOptions: function () {
            var _self = this;
            var options = {
                onInitialize: function () {

                },
                onInitialized: function () {
                    _self._resizeSlides(this.$element, this);
                    _self._afterMove(this.$element);
                    //_self.progressDots (this );
                },
                onRefresh: function () {

                },
                onRefreshed: function () {
                    _self._resizeSlides( this.$element, this );
                    _self._afterMove( this.$element );
                },
                onChanged: function () {
                    //_self.progressDots( this );
                    _self._afterMove( this );
                }
            };

            return $.extend( { }, options, this.options );

        },
        _afterMove: function ( $this ) {

        },
        _dots: function ($this) {
            var $wraper = this.element.closest( '.ox-carousel' ).eq( 0 );
            $wraper.on( 'click', 'div.owl-dot',  function () {
                $this.element.trigger( 'to.owl.carousel', [$(this).index(), 300]);
                $( '.owl-dot' ).removeClass( 'active' );
                $(this).addClass( 'active' );
            });
        },
        _resizeSlides: function ( $this, owl ) {

        },

        progressDots: function( $this ) {
            if (!this.options.dots || !this.options.autoplay || !this.options.progress) {
                return;
            }
            var anim  = this.options.autoplayTimeout + 'ms linear 0s 1 normal forwards running progress-horizontal',
                $wrapper = $this.$element.closest( '.ox-banners-slider__container' ).eq( 0 ),
                $dot = $wrapper.find('.owl-dot').find('.progress'),
                $dotActive = $wrapper.find('.owl-dot.active'),
                $dotProgress = $dotActive.find('.progress');
            $dot.css(''); //reset it
            setTimeout(function(){
                $dotProgress.css('animation', anim); //set it back
            });
        },

    } );

    return $.mage.OxCarousel;
} );
