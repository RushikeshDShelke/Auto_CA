require( [ "jquery" ], function ( $ ) {
    "use strict";
    $.fn.selectimage = function ( so ) {
        var action = '',
            s,
            sd = {
                class: {
                    wraper: 'select-image-wrap',
                    list: 'select-image-items',
                    item: 'select-image-item',
                    image: 'select-image-image',
                    element: 'select-image-element',
                    active: 'select-image-selected',
                }
            }
        if ( 'string' === typeof so ) {
            action = so;
        } else {
            s = $.extend( true, { }, sd, so );
        }
        return $( this ).each( function ( ) {
            var $this = $( this ),
                $parent = $this.parent( );
            if ( !$this.is( 'select' ) && $this.children( ).length ) {
                return;
            }
            var $wraper = $parent.find( '.' + s.class.wraper ),
                generate_images = function ( $parentlist ) {
                    $this.children( ).each( function ( ) {
                        var $item = $( this );
                        if ( $item.is( 'optgroup' ) ) {
                            var item_value = $item.attr( 'label' ),
                                $li = $parentlist.find( '.' + s.class.item + '.' + s.class.item + '-optgroup[data-value="' + item_value + '"]' ),
                                $submenu;
                            if ( !$li.length ) {
                                $li = $( '<li>' ).addClass( s.class.item ).addClass( s.class.item + '-optgroup' ).attr( 'data-value', item_value ).html( $img );
                                $parentlist.append( $li );
                            }
                            if ( !$li.children( 'span' ) ) {
                                $li.prepend( $( '<span>' ).text( $item ) )
                            }
                            $submenu = $li.children( 'ul.' + s.class.list );
                            if ( !$submenu.length ) {
                                $submenu = $( '<ul>' ).addClass( s.class.list );
                                $li.append( $submenu );
                            }
                            generate_images.call( $item, $submenu );
                        } else if ( $item.is( 'option' ) ) {
                            var item_value = $item.attr( 'value' );
                            if ( !$parentlist.find( '.' + s.class.item + '.' + s.class.item + '-option[data-value="' + item_value + '"]' ).length ) {
                                var item_url = $item.text(),
                                    $img = $( '<img>' ).attr( {
                                    src: item_url,
                                    alt: item_value,
                                    class: s.class.image,
                                } );
                                var $li = $( '<li>' ).addClass( s.class.item ).addClass( s.class.item + '-option' ).attr( 'data-value', item_value ).html( $img );
                                $parentlist.append( $li );
                            }
                        }
                    } );
                };
            if ( 'select' === action ) {

                return;
            }
            if ( !$wraper.length ) {
                $wraper = $( '<ul>' ).addClass( s.class.wraper ).addClass( s.class.list );
                $parent.prepend( $wraper );
            }
            generate_images.call( $this, $wraper );
            $wraper.on( 'click', '.' + s.class.item + '-option', function ( e ) {
                e.preventDefault();
                var value = $( this ).data( 'value' );
                if ( $this.find( 'option[value="' + value + '"]' ).length ) {
                    $this.val( value ).trigger( 'change' );
                }
            } );
            $this.on( 'change', function () {
                var value = $( this ).val(),
                    $item_image = $wraper.find( '.' + s.class.item + '-option[data-value="' + value + '"]' ),
                    $items_active = $wraper.find( '.' + s.class.item + '.' + s.class.active ).not( $item_image );
                $items_active.removeClass( s.class.active );
                $item_image.addClass( s.class.active );
            } );

            $this.trigger( 'change' ).addClass( s.class.element ).hide( );
        } );
    };
    $(function(){
        $( '.select-image' ).selectimage();
    });
} );