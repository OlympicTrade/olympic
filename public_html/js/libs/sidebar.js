(function ($) {
    $.fn.sidebar = function (opts) {
        opts = $.extend({
            sidebar: this,
            margin: 20,
            nav: $('#nav')
        }, opts);

        var Sidebar = function () {
            this.container = null;
            this.sidebar = null;
            this.slider = null;
            this.nav = null;
            this.navH = 0;
            this.margin = 20;
            this.sidebarTop = 0;
            this.botLine = 0;
            this.sliderH = 0;
            this.windowH = 0;
            this.oldScrollTop = 0;

            this.update = function() {
                this.sidebar.css({
                    height: this.container.innerHeight() - parseInt(this.container.css('padding-top')) - parseInt(this.container.css('padding-bottom'))
                });

                this.slider.css({
                    top: 0,
                    left: 0,
                    position: 'relative',
                    width: this.sidebar.innerWidth(),
                    overflow: 'hidden'
                });

                this.sidebarTop = this.sidebar.offset().top;
                this.navH    = this.nav.outerHeight();
                this.windowH = $(window).height();
                this.sliderH = this.slider.innerHeight();
                this.botLine = this.sidebar.innerHeight() - this.slider.innerHeight();

                $(window).trigger('scroll');
            };

            this.init = function(opts) {
                this.margin = opts.margin;
                this.nav = opts.nav;
                this.sidebar = opts.sidebar;
                this.container = opts.sidebar.parent();
                this.slider = opts.sidebar.children();

                this.update();

                var obj = this;
                var top;

                $(window).on('scroll', function() {
                    var newScroll = $(this).scrollTop();
                    var sizeRevert = (obj.windowH - obj.navH - obj.margin) > obj.sliderH;

                    if(!sizeRevert) {
                        if(newScroll < obj.oldScrollTop) {
                            if(newScroll > (obj.sidebarTop + top - obj.margin)) {
                                obj.oldScrollTop = newScroll;
                                return;
                            }
                            top = newScroll - (obj.sidebarTop - obj.navH - obj.margin);
                        } else {
                            //console.log((obj.sidebarTop + top + obj.sliderH - obj.windowH + obj.margin));
                            if(newScroll < (obj.sidebarTop + top + obj.sliderH - obj.windowH + obj.margin)) {
                                obj.oldScrollTop = newScroll;
                                return;
                            }
                            //console.log(newScroll - (obj.sidebarTop + obj.sliderH - obj.windowH + obj.margin));
                            top = newScroll - (obj.sidebarTop + obj.sliderH - obj.windowH + obj.margin);
                        }
                    } else {
                        if(newScroll < obj.oldScrollTop) {
                            if(newScroll > (obj.sidebarTop + top + obj.sliderH - obj.windowH + obj.margin)) {
                                obj.oldScrollTop = newScroll;
                                return;
                            }
                            top = newScroll - (obj.sidebarTop + obj.sliderH - obj.windowH + obj.margin);
                        } else {
                            if(newScroll < (obj.sidebarTop + top - obj.navH - obj.margin)) {
                                obj.oldScrollTop = newScroll;
                                return;
                            }
                            top = newScroll - (obj.sidebarTop - obj.navH - obj.margin);
                        }
                    }

                    top = Math.min(top, obj.botLine);
                    top = Math.max(top, 0);

                    obj.slider.css({top: top});
                    obj.oldScrollTop = newScroll;
                }).trigger('scroll');
            };
        };

        var sidebar = new Sidebar();
        sidebar.init(opts);

        return sidebar;
    };
})(jQuery);
