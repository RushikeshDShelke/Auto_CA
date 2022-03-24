define( [
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'OXmodal'
], function ( $ ) {
    'use strict';

    $.widget( 'mage.OXmodalWishlist', $.mage.OXmodal, {
        timerUH: null,
        /**
         * Extend default functionality to close the dropdown
         * with custom delay on mouse out and also to close when clicking outside
         */
        open: function ( extra_class ) {
            this._super( extra_class );
            this.updateHeigh();
            if (this._isOpening || this._isOpen) {
                if (this.timerUH) {
                    clearTimeout(this.timerUH);
                }
                var _self = this;
                this.timerUH = setTimeout( function () {
                    _self.updateHeigh()
                }, 300 );
            }
        },
        close: function (forse) {
            this._super( forse );
            if (this.timerUH) {
                clearTimeout(this.timerUH);
            }
        },
        updateHeigh: function () {
            var delta = this.uiDialog.offset().top - $( window ).scrollTop() + this.uiDialog.outerHeight() - $( window ).height(),
                $menu = $( '.product-items', this.element );
            if ( 0 < delta ) {
                $menu.height( $menu.height() - delta );
            }
        }
    } );

    return $.mage.OXmodalWishlist;
} );
