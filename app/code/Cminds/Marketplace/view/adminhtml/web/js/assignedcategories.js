require(['jquery'], function($){
    $(".categories_checkbox").change(function() {
        console.log($(this).val());
    })
});