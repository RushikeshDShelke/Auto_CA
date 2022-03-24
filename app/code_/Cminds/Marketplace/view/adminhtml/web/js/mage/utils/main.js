define(function (require) {
    'use strict';
    var utils = {},
        _ = require('underscore');

    var defaultAttributes = {
        method: 'post',
        enctype: 'multipart/form-data'
    };

    var id;
    return _.extend(
        utils,
        require('mage/utils/arrays'),
        require('mage/utils/compare'),
        require('mage/utils/misc'),
        require('mage/utils/objects'),
        require('mage/utils/strings'),
        require('mage/utils/template'),

        {
            submit: function (options, attrs) {
                var form = document.createElement('form'),
                    data = this.serialize(options.data),
                    attributes = _.extend({}, defaultAttributes, attrs || {});

                if (!attributes.action) {
                    attributes.action = options.url;
                }

                data['form_key'] = window.FORM_KEY;

                _.each(attributes, function (value, name) {
                    form.setAttribute(name, value);
                });

                data = _.map(
                    data,
                    function (value, name) {
                        if (name.substring(0,18) === 'tablerate_csv_file') {
                            if (value) {
                                id = parseInt(name.substring(19));
                            }
                            return '';
                        }

                        return '<input type="hidden" ' +
                            'name="' + _.escape(name) + '" ' +
                            'value="' + _.escape(value) + '"' +
                            ' />';
                    }
                ).join('');

                var elementId = 'tablerate_csv_file_' + id;
                var fileUploadField = document.getElementById(elementId);

                form.append(fileUploadField);
                form.insertAdjacentHTML('afterbegin', data);
                document.body.appendChild(form);

                form.submit();
            }
        }
    );
});