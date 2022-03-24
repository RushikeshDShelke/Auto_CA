require([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    
    /*Social share open/close */
    var socialShare = $('.social-share__action');
    if (socialShare.length) {
        socialShare.on('click', function (e) {
            $('.social-share__content').addClass('opened');
            e.stopPropagation();
        });
        $('body').on('click', function () {
            $('.social-share__content').removeClass('opened');
        });
    }
    /* social share new window size */
    $('body').on('click', '.ox-social-button', function (e) {
        var newWind = window.open($(this).attr('href'), $(this).attr('title'), "width=420,height=320,resizable=yes,scrollbars=yes,status=yes");
        if (newWind) {
            newWind.focus();
            e.preventDefault();
        }
    });
    /* product gallery custom cursor */
    if ($('body').hasClass('custom-gallery-cursor')) {
        $('body').addClass('custom-gallery-cursor-loaded');
        let ns = '.OXA2Cursor',
        media_block = '.product.media .gallery-placeholder';
        $('body')
        .on('mouseenter' + ns, media_block, async function () {
            $(this).unbind('mouseenter' + ns);
            if (!$('#ox-zoom-cursor').length) {
                $('<div id="ox-zoom-cursor"><span></span></div>').appendTo($(this));
            }
        }).on('mousemove' + ns, media_block, function (e) {
            var parentOffset = this.getBoundingClientRect();
            var relX = e.pageX - parentOffset.left;
            var relY = e.pageY - (window.scrollY + parentOffset.top);
            $('#ox-zoom-cursor').css({left: relX, top: relY});
        });
    }

});
