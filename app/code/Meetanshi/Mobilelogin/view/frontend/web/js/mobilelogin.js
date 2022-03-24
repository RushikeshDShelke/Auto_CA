function phonenumber(inputtxt) {
    return true;
}
require(['jquery','mage/url', 'jquery/ui'], function($,url) {
    jQuery(function () {
        jQuery('.mobile-sendotp-popup').on('keydown', '#mobilenumber', function (e) {
            -1 !== jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) || (/65|67|86|88/.test(e.keyCode) && (e.ctrlKey === true || e.metaKey === true)) && (!0 === e.ctrlKey || !0 === e.metaKey) || 35 <= e.keyCode && 40 >= e.keyCode || (e.shiftKey || 48 > e.keyCode || 57 < e.keyCode) && (96 > e.keyCode || 105 < e.keyCode) && e.preventDefault()
        });
    });
    jQuery(".addmobile").on('click', function () {
        jQuery('.mobile-sendotp-popup').modal('openModal');
    });
    var otptype = jQuery('#otptype').val();
    jQuery('#update_mobile').on('click',function () {
        jQuery('.mobilenumber-update').show();
    });

});

function sendOTP(resend) {
    var mobilenumber = jQuery('#mobilenumber').val();
    var otptype = jQuery('#otptype').val();
    var id = jQuery(this).attr('id');
    var baseUrl = jQuery("#baseUrl").val();
    var oldmobile = 0;

    if (mobilenumber == "") {
        jQuery('.mobilelogin-error').text('Field cannot be empty');
        jQuery('.mobilelogin-error').css("color", "red");
        jQuery('.mobilelogin-error').show().delay(5000).fadeOut();
    }
    else {

        if (otptype == "update") {
            oldmobile = jQuery('#old_mobilenumber').val();
        }
        if (phonenumber(mobilenumber)) {
            jQuery.ajax({
                url: baseUrl + 'mobilelogin/otp/send',
                type: 'POST',
                data: {
                    mobilenumber: mobilenumber,
                    otptype: otptype,
                    resendotp: resend,
                    oldmobile: 0
                },
                showLoader: true,
                success: function (response) {
                    if (response.succeess === "true") {
                        jQuery('.mobile-sendotp-popup').modal('closeModal');
                        jQuery('.mobile-otpverify-popup').modal('openModal');
                        jQuery('.mobilelogin-popup-message').text("Sent Otp Succeessfully");
                        jQuery('.mobilelogin-popup-message').css("color", "green");
                        jQuery('.mobilelogin-popup-message').show().delay(5000).fadeOut();
                    }
                    else {
                        jQuery('.mobilelogin-popup-message').text(response.errormsg + "");
                        jQuery('.mobilelogin-popup-message').css("color", "red");
                        jQuery('.mobilelogin-popup-message').show().delay(5000).fadeOut();

                    }
                },
                error: function (data) {
                    //    location.reload();
                }
            });
        }
        else {
            jQuery('.mobilelogin-error').show().delay(5000).fadeOut();
        }
    }
}

function otpVerify() {
    var baseUrl = jQuery("#baseUrl").val();
    var mobilenumber = jQuery('#mobilenumber').val();
    var otpcode = jQuery('#otp_input').val();
    var isCheckout = jQuery('#ischeckout').val();
    var oldmobile;
    var otptype = jQuery('#otptype').val();
    if (otptype == "update") {
        oldmobile = jQuery('#oldmobile').val();
    }
    if (otpcode == "") {
        jQuery('.mobilelogin-error').text('Field cannot be empty');
        jQuery('.mobilelogin-error').css("color", "red");
        jQuery('.mobilelogin-error').show().delay(5000).fadeOut();
    }
    else {
        jQuery.ajax({
            url: baseUrl + 'mobilelogin/otp/verify',
            type: 'POST',
            data: {
                mobilenumber: mobilenumber,
                otptype: otptype,
                otpcode: otpcode,
                oldmobile: oldmobile
            },
            showLoader: true,
            success: function (response) {
                if (response.succeess === "true") {
                    if (otptype === "register") {
                        jQuery('.mobile-otpverify-popup').modal('closeModal');
                        jQuery('#mobile_number').val(mobilenumber);
                        jQuery('#mobile_number').prop('readonly', true);
                        jQuery('.mobile-register').show();
                    }

                    if (otptype === "forgot") {
                        jQuery('.mobile-otpverify-popup').modal('closeModal');
                        jQuery('#mobile-resetpasswd-popup').modal('openModal');
                    }
                    if (otptype === "login") {
                        jQuery('.mobile-otpverify-popup').modal('closeModal');
                        var chkout = jQuery('.mobile-otpverify-popup .checkoutlogin').val();
                        if (response.customurl !== "" && chkout=="checkout") {
                            window.location.href = baseUrl+ 'checkout/index/index';
                        }
                        else {
                            window.location.href = response.customurl;
                        }
                    }
                    if (otptype === "update") {
                        window.location.reload();
                    }
                }
                else {
                    jQuery('.mobilelogin-popup-message').html(response.errormsg);
                    jQuery('.mobilelogin-popup-message').css("color", "red");
                    jQuery('.mobilelogin-popup-message').show().delay(5000).fadeOut();
                }
            }
        });
    }
}
function changepassword(){
    var baseUrl = jQuery("#baseUrl").val();
    var mobilenumber = jQuery('#mobilenumber').val();
    var passwd = jQuery('#mobpassword').val();
    var repasswd = jQuery('#mobrepassword').val();
    if (passwd !== repasswd) {
        jQuery('.mobilelogin-popup-message').text('Password Mismatch');
        jQuery('.mobilelogin-popup-message').css("color", "red");
        jQuery('.mobilelogin-popup-message').show().delay(5000).fadeOut();
    }
    else {
        jQuery.ajax({
            url: baseUrl + "mobilelogin/otp/resetpassword",
            type: 'POST',
            async: false,
            data: {
                mobilenumber: mobilenumber,
                password: passwd
            },
            showLoader: true,
            success: function (response) {
                if (response.customurl !== "") {
                    window.location.href = response.customurl;
                }
                else {
                    jQuery('.mobilelogin-popup-message').text('Password not change');
                    jQuery('.mobilelogin-popup-message').css("color", "red");
                    jQuery('.mobilelogin-popup-message').show().delay(5000).fadeOut();
                }
            }
        });
    }
}
