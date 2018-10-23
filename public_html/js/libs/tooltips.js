(function ($) {
    $.fn.tooltip = function (options) {
        var set = $.extend({
            'speed': 100,
            'mode': 'below',
            'space': 70
        }, options);

        return this.each(function () {
            var mouseY = -1;
            $(document).on('mousemove', function (event) {
                mouseY = event.clientY;
            });
            var viewY = $(window).height();
            var tooltip_cont;

            $(this).hover(function () {
                var mode = set.mode;
                var text = $(this).data('tooltip');

                if(!text) {
                    return;
                }

                $(window).on('resize', function () {
                    viewY = $(window).height();
                });

                if (viewY - mouseY < set.space) {
                    mode = 'above';
                } else {
                    mode = set.mode;

                    if ($(this).attr('data-mode')) {
                        mode = $(this).attr('data-mode')
                    }
                }

                tooltip_cont = '.tooltip_container_' + mode;

                var out = $('<div class="tooltip_container_' + mode + '"><div class="tooltip_point_' + mode + '"><div class="tooltip_content">' + text + '</div></div></div>');

                //$(this).after(out);
                out.appendTo($(this));

                var w_t = $(tooltip_cont).outerWidth();
                var w_e = $(this).width();
                var m_l = (w_e / 2) - (w_t / 2);

                $(tooltip_cont).css('margin-left', m_l + 'px');
                $(this).removeAttr('title alt');

                $(tooltip_cont).fadeIn(set.speed);
                }, function () {
                    $(tooltip_cont).fadeOut(set.speed, function () {
                        $(tooltip_cont).remove();
                    });
                }
            );
        });
    };
})(jQuery);
