var config = {
    paths: {
        revsliderAdmin:         'Nwdthemes_Revslider/admin/assets/js/admin.min',
        revsliderEditor:        'Nwdthemes_Revslider/admin/assets/js/editor.min',
        help:                   'Nwdthemes_Revslider/admin/assets/js/modules/help',
        overview:               'Nwdthemes_Revslider/admin/assets/js/modules/overview',
        tooltip:                'Nwdthemes_Revslider/admin/assets/js/modules/tooltip',
        clipboard:              'Nwdthemes_Revslider/admin/assets/js/plugins/clipboard.min',
        codemirror:             'Nwdthemes_Revslider/admin/assets/js/plugins/codemirror',
        pennerEasing:           'Nwdthemes_Revslider/admin/assets/js/plugins/penner-easing',
        select2RS:              'Nwdthemes_Revslider/admin/assets/js/plugins/select2RS.full.min',
        wavesurfer:             'Nwdthemes_Revslider/admin/assets/js/plugins/wavesurfer',
        colorPicker:            'Nwdthemes_Revslider/framework/js/color-picker.min',
        galleryBrowser:         'Nwdthemes_Revslider/framework/js/browser',
        iris:                   'Nwdthemes_Revslider/framework/js/iris.min',
        jQueryUI:               'Nwdthemes_Revslider/framework/js/jquery-ui.min',
        loading:                'Nwdthemes_Revslider/framework/js/loading',
        wpUtil:                 'Nwdthemes_Revslider/framework/js/wp-util.min',
        'jquery/file-uploader': 'jquery/fileUploader/jquery.fileupload-fp',
        prototype:              'legacy-build.min'
    },
    shim: {
        galleryBrowser: {
            deps: ['Magento_Variable/variables']
        },
        colorPicker: {
            deps: ['jQueryUI', 'iris']
        },
        iris: {
            deps: ['jQueryUI']
        },
        jQueryUI: {
            deps: ['jquery']
        },
        loading: {
            deps: ['jquery', 'revolutionTools'],
            exports: 'showWaitAMinute'
        },
        wpUtil: {
            deps: ['jquery', 'underscore']
        }
    }
};