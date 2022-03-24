/**
 * Olegnax Layered Navigation - Price Slider
 * Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 */
define([
    'jquery',
    'noUiSlider',
    'wNumb',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget',
], function ($, noUiSlider, wNumb) {
    'use strict';

    $.widget('mage.OXPriceSlider', {
        options: {
            //slider
            animate: true,
            min: 0,
            max: 100,
            current: null,
            step: 1,
            //format
            formatMoney: {
                decimals: 2
            },
            //html
            class_slider: ".ox-price-slider",
            class_value: "input[name='filter[price]']",
            class_label_scale: ".ox-price-filter__values",
            class_label: ".ox-price-filter__selected",
            class_from_label: ".ox-slider-from",
            class_to_label: ".ox-slider-to",

        },
        _create: function () {
            this.slider_element = this.element.find(this.options.class_slider);
            this.value_element = this.element.find(this.options.class_value);
            this.label_element = this.element.find(this.options.class_label);
            this.min_label_element = this.label_element.find(this.options.class_from_label);
            this.max_label_element = this.label_element.find(this.options.class_to_label);

            var values = this.value_element.val();
            values = values ? values.split('-').map(parseFloat) : null;

            this.options.min = parseFloat(this.options.min);
            this.options.max = parseFloat(this.options.max);
            this.options.step = parseFloat(this.options.step);
            this.options.current = this.options.current || values || [this.options.min, this.options.max];

            this.element.find('input').hide();

            this.slider_element.show();
            this.wNumb = wNumb(this.options.formatMoney);
            var slider = this.slider_element[0];


            noUiSlider.create(slider, {
                start: this.options.current,
                step: 1,
                connect: true,
                range: {
                    'min': this.options.min,
                    'max': this.options.max
                },
                tooltips: [true, this.wNumb],
                pips: {
                    mode: 'positions',
                    values: [0, 100],
                    density: 4,
                    format: this.wNumb
                }
            });
            this.sliderCreate();
            slider.noUiSlider.on('change.OXPriceSlider', $.proxy(this.sliderChange, this));
        },
        sliderCreate: function () {
            this.change_label(this.options.current);
            this.element.trigger('price_slider_create', this.options.current);
        },
        sliderSlide: function (values) {
            this.change(values, 'price_slider_slide');
        },
        sliderChange: function (values) {
            this.change(values, 'price_slider_change');
        },
        change: function (values, event) {
            this.change_value(values, 'price_slider_change' === event);
            this.change_label(values);
            if ('string' == typeof event && event) {
                this.element.trigger(event, values);
            }
        },
        change_value: function (values, run) {
            values = values.join('-');
            this.value_element.val(values);
            if (run) {
                this.value_element.trigger('change');
            }
        },

        change_label: function (values) {
            this.min_label_element.html(this.wNumb.to(values[0]));
            this.max_label_element.html(this.wNumb.to(values[1]));
            this.label_element.show();
        }

    });

    return $.mage.OXPriceSlider;

});