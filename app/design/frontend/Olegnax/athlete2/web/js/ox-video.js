require(["jquery"], function ($) {
    "use strict";    
        $(function () {
            let ns = ".OXA2Video",
                s_hover = ".product-item";
            $("body")
                .on("mouseenter" + ns, s_hover, async function () {
                    let $wrapper = $(this).find(".ox-product-hover-image-container");
                    let video = $wrapper.find("video").get(0);
                    if (video) {
                        try {
                            await video.play();
                        } catch (err) {
                            console.error(err);
                        }
                    }
                })
                .on("mouseleave" + ns, s_hover, function () {
                    let $wrapper = $(this).find(".ox-product-hover-image-container");
                    let video = $wrapper.find("video").get(0);
                    if (video && video.currentTime > 0 && !video.paused && !video.ended && video.readyState > 2) {
                        video.pause();
                    }
                });
        });
});