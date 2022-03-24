define( [
    'jquery',
    'OXmodal'
], function ( $ ) {
    "use strict";

    var oxquickview = {
        displayContent: function ( prodUrl ) {
            if ( !prodUrl.length ) {
                return false;
            }

            var a = $( '#ox_quickview_wrapper' );
            if ( !a.length ) {
                a = $( '<div>' ).attr( 'id', 'ox_quickview_wrapper' );
                $( 'body' ).append( a );
                a.OXmodal( {
                    defaultModalClass: 'ox-dialog ox-smallmodal',
                    type: 'modal',
                    overlayClass: "ox-slideout-shadow",
                    closeButtonTrigger: '.ox-overlay-close-btn',
                    "htmlClass": "ox-fixed",
                } );
            }
            a.find( '.ox_quickview-preloader' ).show();
            a.find( 'iframe' ).remove();
            a.append( $( '<iframe>' ).attr( {
                src: prodUrl,
                frameborder: 0,
                allowfullscreen: 0,
                style: 'opacity: 0;width: 100%;height: 100%;',
            } ) );
            a.data( 'mageOXmodal' ).open();
            a.parent().on( 'click', function ( e ) {
                a.data( 'mageOXmodal' ).close();
            } );
        },
        close:function () {
            $( '#ox_quickview_wrapper' ).data( 'mageOXmodal' ).close();
        }
    };

    window.oxquickview = oxquickview;
    return oxquickview;
} );