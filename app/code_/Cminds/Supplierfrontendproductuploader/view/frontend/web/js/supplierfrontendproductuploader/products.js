require([
    'jquery'
], function ($) {
    $(document).ready(function () {
        $('#product_create_form').submit(function () {
            if ($('.category_checkbox').is(':checked')) {
                $('#categories-validate-message').hide();
                return true;
            } else {
                $('#categories-validate-message').show();
                $('#categories-validate-message').html('Please select category.');
                return false;
            }
        });
    });
});