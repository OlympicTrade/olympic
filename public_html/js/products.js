$(function() {
    initProductsList($('.products-list'));
    initMainProductList();

    function initProductsList(box) {
        if(!box.length) { return; }

        $('.products-popup', box).on('click', function () {
            $.fancybox.open({
                src: $(this).attr('href'),
                type: 'ajax',
                opts : {
                    afterLoad: function(e, slide) {
                        slide.$slide.on('click', function(e) {
                            if($(e.target).hasClass('fancybox-slide')) {
                                $.fancybox.close()
                            }
                        });

                        var box = $('.product-popup', e.$refs.slider);
                        initElements(box);
                        productView(box, {zoomIndex: 2000});
                    }
                }
            });
            return false;
        });
    }

    function initMainProductList() {
        var box = $('.products-list-main');
        if(!box.length) { return; }

        var productsBox = $('.products-list', box);
        var filtersBox  = $('.filters', box);
        var paginator = $('.paginator', productsBox);
        var wCtegory = $('.widget.catalog', filtersBox);
        var wBrands  = $('.widget.brands', filtersBox);

        var upTimer = null;

        initProductsFilters();
        initSidebarScroll();

        $('.readmore', wBrands).on('click', function () {
            var box = $('.h-box', wBrands);
            var btn = $(this);

            if(box.is(':visible')) {
                box.slideUp(200);
                btn.text('показать все');
            } else {
                box.slideDown(200);
                btn.text('скрыть');
            }
        });

        $('.update-box', filtersBox).on('change', 'input, textarea, select', function () {
            clearTimeout(upTimer);
            upTimer = setTimeout(function () {
                updateProducts({filtersUpdate: false});
            }, 200);
        });

        $('a', wCtegory).on('click', function () {
            $(this).toggleClass('active').parent().siblings().find('a').removeClass('active');
            updateProducts({filtersUpdate: true});
            return false;
        });

        function initProductsFilters(box) {
            box = box || filtersBox;

            $('.price-slider', box).ionRangeSlider({
                type: 'double',
                hide_from_to: false
            });

            wCtegory = $('.widget.catalog', box);
            wBrands  = $('.widget.brands', box);
        }

        function getFiltersUrl() {
            var active = $('.active', wCtegory);
            var url = $.aptero.url();
            
            url.setPath(active.length ? active.attr('href') : $('input.update-url', filtersBox).val());
            url.setParams($.aptero.serializeArray(filtersBox));

            return url;
        }

        function updateProducts(options) {
            productsBox.fadeOut(200, function () {
                setTimeout(function () {
                    if(!productsBox.is(':visible')) {
                        $.aptero.loadingHtml(productsBox);
                        productsBox.css({display: 'block'});
                    }
                });
            });

            var url = getFiltersUrl();

            $.ajax({
                url: url.getUrl(),
                success: function (resp) {
                    var products = $(resp.html.products);
                    productsBox.html(products);
                    loadMoreProducts();

                    $('html, body').scrollTop(productsBox.offset().top - $('#nav').outerHeight() - 20);

                    if(productsBox.is(':visible')) {
                        productsBox.css({display: 'none'})
                    }

                    productsBox.fadeIn(200);

                    if(options.filtersUpdate) {
                        var upBox = $('.update-box', filtersBox);
                        upBox.html(resp.html.filters);
                        initElements(filtersBox);
                        initProductsFilters();
                    }

                    History.replaceState({}, resp.meta.title, url.getUrl());
                }
            });
        }

        var loadingTimer = null;
        $(window).on('scroll', function () {
            clearTimeout(loadingTimer);
            loadingTimer = setTimeout(function() {
                loadMoreProducts();
            }, 250);
        });
        loadMoreProducts();

        function loadMoreProducts() {
            paginator = $('.paginator', productsBox);
            if(!paginator.length) {
                return;
            }

            var url = getFiltersUrl();
            url.setParams({page: paginator.data('page')});
            var loadLine = $(window).scrollTop() + ($(window).height()) + 700;
            paginator.remove();
            paginator = $('.paginator', productsBox);

            $.ajax({
                url: url.getUrl(),
                success: function (resp) {
                    var products = $(resp.html.products);
                    products.appendTo(productsBox);
                    paginator = $('.paginator', productsBox);

                    if(paginator.length && paginator && loadLine >= paginator.offset().top) {
                        loadMoreProducts();
                    }
                }
            });
        }

        function initSidebarScroll() {
            var sidebar = $('.sidebar');

            if(!sidebar.length) {
                return;
            }

            var sidebarTop = 0;
            var container = box;
            var navH = $('#nav').outerHeight();
            var sidebarH  = sidebar.outerHeight();
            var windowH   = $(window).height();
            var oldScroll = 0;
            var sizeSh = sidebarH > windowH + navH;
            var topLine = container.offset().top - navH - 20;

            $('.content', box).css({minHeight: sidebarH});

            $(window).on('resize', function () {
                windowH   = $(window).height();
                navH = $('#nav').outerHeight();
                sizeSh = sidebarH > windowH + navH;
                topLine = container.offset().top - navH - 20;
            });

            $(window).on('scroll', function () {
                var scroll = $(this).scrollTop();
                var botLine = topLine + container.innerHeight();
                var shift = sidebarH - windowH + 50;

                if(sizeSh) {
                    if (scroll > oldScroll) {
                        if (scroll < topLine + sidebarTop + shift) {
                            oldScroll = scroll;
                            return;
                        }
                    } else {
                        if (scroll > topLine + sidebarTop) {
                            oldScroll = scroll;
                            return;
                        }
                        shift = 0;
                    }
                } else {
                    shift = 0;
                }

                if(topLine < scroll) {
                    if(botLine < (scroll + sidebarH)) {
                        sidebarTop = Math.max(0, container.innerHeight() - sidebarH);
                    } else {
                        sidebarTop = scroll - topLine - shift;
                    }
                } else {
                    sidebarTop = 0;
                }

                sidebar.css({top: sidebarTop});
                oldScroll = scroll;
            });
        }
    }
});