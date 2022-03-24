var config = {
    paths: {
        "intlTelInput": 'Meetanshi_Mobilelogin/js/intlTelInput',
        "intlTelInputUtils": 'Meetanshi_Mobilelogin/js/utils',
        "internationalTelephoneInput": 'Meetanshi_Mobilelogin/js/internationalTelephoneInput'
    },

    shim: {
        'intlTelInput': {
            'deps':['jquery', 'knockout']
        },
        'internationalTelephoneInput': {
            'deps':['jquery', 'intlTelInput']
        }
    }
};
