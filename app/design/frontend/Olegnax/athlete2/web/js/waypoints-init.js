require(['jquery', 'domReady!', 'js/waypoints'], function ($) {
    $('.ox-animate:not(.animated)').waypoint({
        handler: function () {
            var $this = $(this.element),
                delay = $this.data('animdelay') || 1;
            if ($this.hasClass('animated')) {
                return;
            }
            if (this.hasOwnProperty('element')) {
                $this = $(this.element);
            } else {
                $this = $(this);
            }
            setTimeout(function () {
                $this.addClass('animated');
            }, delay);
        },
        offset: '80%'
    });
});