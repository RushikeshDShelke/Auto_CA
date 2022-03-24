define(['jquery', 'OxQuickview', 'OXmodal'], function ($, quickview) {
    "use strict";
    $('#ox_quickview_wrapper').OXmodal({
        defaultModalClass: 'ox-dialog ox-smallmodal',
        type: 'modal',
        overlayClass: "ox-slideout-shadow",
        closeButtonTrigger: '.ox-overlay-close-btn',
        "htmlClass": "ox-fixed",
    });
    $(function () {
        $(document.body).on('click.OxQuickview', '.ox-quickview-button', function (event) {
            event.preventDefault();
            var prodUrl = $(this).attr('data-quickview-url');
            if (prodUrl) {
                quickview.displayContent(prodUrl);
            }
        }).on('closeOxQuickview', function () {
            quickview.close();
        });
    });
});