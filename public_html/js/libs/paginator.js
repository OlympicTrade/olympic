(function ($) {
    $.fn.paginator = function (opts) {
        opts = $.extend({
            list: this
        }, opts);

        var Paginator = function () {
            this.timer = null;
            this.url = null;
            this.urlParams = null;
            this.list = null;

            /*this.data = function(opts) {
                console.log('paginator update');
                this.url = opts.url;
                this.urlParams = opts.urlParams;
            };*/

            this.init = function(opts) {
                var obj = this;
                obj.list = opts.list;
                this.url = opts.url;
                this.urlParams = opts.urlParams;

                $(window).on('scroll', function () {
                    clearTimeout(obj.timer);
                    obj.timer = setTimeout(function() {
                        obj.load();
                    }, 250);
                }).trigger('scroll');
            };

            this.load = function() {
                var obj = this;

                var paginator = $('.paginator', obj.list);
                if(!paginator.length) {
                    return;
                }

                var url = $.aptero.url();
                url.setPath(obj.url);
                url.setParams(obj.urlParams);
                url.setParams({page: paginator.data('page')});

                var loadLine = $(window).scrollTop() + ($(window).height()) + 200;
                paginator.remove();

                $.ajax({
                    url: url.getUrl(),
                    success: function (resp) {
                        var html = $(resp.html.items);
                        html.appendTo(obj.list);
                        paginator = $('.paginator', obj.list);
                        obj.trigger('update');

                        if(paginator.length && paginator && loadLine >= paginator.offset().top) {
                            obj.load();
                        }
                    }
                });
            };

            this.on = function(event, fn) {
                $(this).on(event, fn);
            };

            this.trigger = function(event) {
                $(this).trigger(event);
            };
        };

        var paginator = new Paginator();
        paginator.init(opts);

        return paginator;
    };
})(jQuery);