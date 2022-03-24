define(
    ["jquery", 'mage/template', "Magento_Ui/js/modal/modal", 'mage/translate'],
    function ($) {
        return function (config) {
            successMessage = $('#peakhour-connection-success-msg');
            errorMessage = $('#peakhour-connection-error-msg');

            $('#peakhour_test_connection_button').on('click', function () {
                successMessage.text();
                successMessage.hide();
                errorMessage.text();
                errorMessage.hide();
                
                $.ajax({
                    type: "POST",
                    url: config.testUrl,
                    showLoader: true,
                    data: {
                        'api_key': $('#system_full_page_cache_peakhour_peakhour_api_key').val(),
                        'domain': $('#system_full_page_cache_peakhour_peakhour_domain').val()
                    },
                    cache: false,
                    success: function (response) {
                        console.dir(response);
                        if (response[0].success == false) {
                            return errorMessage.text(response[0].error).show();
                        } else {
                            return successMessage.text("success!").show();
                        }
                    },
                    error: function (msg) {
                        return errorMessage.text('Failed to test.').show();
                    }
                });
            });
        };
    }
);
