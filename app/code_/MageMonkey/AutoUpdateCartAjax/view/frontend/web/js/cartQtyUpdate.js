define([
	    'jquery',
	    'Magento_Checkout/js/action/get-totals',
	    'Magento_Customer/js/customer-data'
], function ($, getTotalsAction, customerData) {
	 
	    $(document).ready(function(){
		    	
		    $('.increaseQty').click(function() {               
			    var maxQty = $(this).attr('data-item-salable-qty');
			    var requestedVal = $(this).parent().find("input").val();
			    if(requestedVal > maxQty){
				    alert("Requested Qty is not available");
				    var newVal = parseFloat(requestedVal) - 1;
				    $(this).parent().find("input").val(newVal);
				    return false;
			    }
			    var form = $('form#form-validate');                                                                                                                                     $.ajax({                                                                                                                                                                                        url: form.attr('action'),                                                                                                                                               data: form.serialize(),                                                                                                                                                 //showLoader: true,
		    							success: function (res) {
										var parsedResponse = $.parseHTML(res);
										var result = $(parsedResponse).find("#form-validate");
										var sections = ['cart'];
										$("#form-validate").replaceWith(result);
										/* Minicart reloading */
										customerData.reload(sections, true);
										/* Totals summary reloading */
										var deferred = $.Deferred();
										getTotalsAction([], deferred);
										location.reload();
									},
			    						error: function (xhr, status, error) {
										var responseText = jQuery.parseJSON(xhr.responseText);
										var err = eval("(" + xhr.responseText + ")");
										console.log(err.Message);
									}                                                                                                                                           });                                                                                                                                             });
		    
		    $('.decreaseQty').click(function() {
		    			var form = $('form#form-validate');
					$.ajax({
						url: form.attr('action'),
						data: form.serialize(),
						//showLoader: true,
						success: function (res) {
							var parsedResponse = $.parseHTML(res);
							var result = $(parsedResponse).find("#form-validate");
							var sections = ['cart'];
							$("#form-validate").replaceWith(result);
							/* Minicart reloading */
							customerData.reload(sections, true);
							/* Totals summary reloading */
							var deferred = $.Deferred();
							getTotalsAction([], deferred);
							location.reload();
						},
						error: function (xhr, status, error) {
							var err = eval("(" + xhr.responseText + ")");
							console.log(err.Message);
							alert(err.Message);
						}
					});
		    });

		            /*$(document).on('change', 'input[name$="[qty]"]', function(){
				                var form = $('form#form-validate');
				                $.ajax({
							                url: form.attr('action'),
							                data: form.serialize(),
							                showLoader: true,
							                success: function (res) {
										                    var parsedResponse = $.parseHTML(res);
										                    var result = $(parsedResponse).find("#form-validate");
										                    var sections = ['cart'];
										 
										                    $("#form-validate").replaceWith(result);
										 
										                    customerData.reload(sections, true);
										 
										                    var deferred = $.Deferred();
										                    getTotalsAction([], deferred);
										                },
							                error: function (xhr, status, error) {
										                    var err = eval("(" + xhr.responseText + ")");
										                    console.log(err.Message);
										                }
							            });
				            }); */
		        });
});
