define([
    'jquery',
], function ($) {
    'use strict';
    $.getValues = function (query) {
        var query = query || '',
            vars = query.split('&'),
            result = {},
            getName = function (name) {
                var regex;

                if ((regex = /^(.+?)\[(.*?)$/).test(name)) {
                    name = name.match(regex);
                    name[2] = name[2].replace(/\]/g, '').split('[');
                    name[2].unshift(name[1]);
                    name = name[2].map(function (v) {
                        return '' === v ? 0 : v;
                    });
                } else {
                    name = [name];
                }

                return name;
            },
            setValue = function (form, names, value) {
                var name = names.shift();

                if (names.length) {
                    var _form = form.hasOwnProperty(name) ? form[name] : {};
                    form[name] = setValue(_form, names, value);
                } else {
                    if (0 === name) {
                        for (; form.hasOwnProperty(name); name++) {
                        }
                    }
                    form[name] = value;
                }

                return form
            };
        for (var i = 0; i < vars.length; i++)
            if (vars[i].length) {
                var pair = vars[i].split('=').map(decodeURIComponent);
                result = setValue(result, getName(pair[0]), pair[1]);
            }

        return $.extend(true, {}, result);

    };
    $.getURLValues = function (href) {
        var href = href || window.location.href,
            url = new URL(href);
        return $.getValues(url.search.replace(/^\?/, ''));
    };

    $.fn.getFormValues = function () {
        return $.getValues($(this).serialize());
    };

    return $.fn.getFormValues;
});