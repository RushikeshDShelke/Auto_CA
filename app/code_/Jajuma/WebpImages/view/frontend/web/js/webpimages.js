require([
    'jquery',
    'Jajuma_WebpImages/js/lib/modernizr-webp'
],function($){
    $(document).ready(function() {
        Modernizr.on('webp', function(result) {
            if (!result) {
                $('body').addClass('no-webp');
            }
        });
    })
})
