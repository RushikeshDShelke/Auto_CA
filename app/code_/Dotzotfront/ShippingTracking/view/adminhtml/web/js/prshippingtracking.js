/**
 * Dotzot Shipping
 */

require([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($, __) {
    'use strict';

    window.prTrackingTestConnection = function(actionUrl, serviceId, ids) {
        var button = $('#' + serviceId),
            resultContainerId = serviceId + '_result',
            resultText = '',
            resultStyle = '',
            resultClass = '';

        var idsData = ids.split(',');
        var postData = [];

        idsData.forEach(function(el){
            postData.push($('#' + el).val());
        });

        $.ajax({
            showLoader: true,
            url: actionUrl,
            data: {data: postData},
            type: "POST",
            dataType: 'JSON'
        }).done(function(response) {
            response = JSON.parse(response);
            console.log(response["result"]);
            if (response["result"] === false) {
                resultText = __('Connection Error!') + " " + response["error"];
                resultStyle = 'color: red;';
                resultClass = 'message-error error';
            } else if (response["result"] === true) {
                resultText = __('Connection Successful!');
                resultStyle = 'color: green;';
                resultClass = 'message-success success';
            }
        }).fail(function() {
            resultText = __('Connection Error!');
            resultStyle = 'color: red;';
            resultClass = 'message-error error';
        }).always(function() {
            var resultBlock = $('#' + resultContainerId);
            if (resultBlock.length) {
                resultBlock.remove();
            }

            button.after(
                '<div id="' + resultContainerId + '" class="message '
                + resultClass
                + '" style="background: none;'
                + resultStyle
                + '">'
                + resultText
                + '</div>'
            );
        });
    };
});