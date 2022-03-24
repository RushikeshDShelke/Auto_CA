define(['jquery','mage/utils/wrapper','Jajuma_WebpImages/js/lib/modernizr-webp'], function ($, wrapper) {
    'use strict';

    return function (initialize) {
        return wrapper.wrap(initialize, function (initialize, config, element) {
            Modernizr.on('webp', function(result) {
                if (result) {
                    $.each(config.data, function (key, value) {
                        value.full = value.full_webp;
                        value.thumb = value.thumb_webp;
                        value.img = value.img_webp;
                        config.data[key] = value;
                    });
                }
                initialize(config, element);
            });
        });
    };
});