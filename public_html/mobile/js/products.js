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
                    closeClickOutside : true,
                    afterLoad: function(e) {
                        var box = $('.product-popup', e.$refs.slider);
                        initElements(box);
                        productView(box);
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
        var paginator = $('.paginator', productsBox);
        var filtersBox  = $('.filters', box);

        var wCtegory = $('.widget.catalog', filtersBox);
        var wBrands  = $('.widget.brands', filtersBox);

        var upTimer = null;

        initProductsList();
        initProductsFilters();

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

        if(paginator.length) {
            var paginatorTop = paginator.offset().top;
            var $window = $(window);
            $window.on('scroll', function () {
                if(paginatorTop == 0) {
                    return;
                }

                if(($window.scrollTop() + $window.height()) > (paginatorTop - 500)) {
                    loadMoreProducts();
                    paginatorTop = 0;
                }
            });
        }

        function initProductsList() {
            paginator = $('.paginator', productsBox);
            if(paginator.length) {
                paginatorTop = paginator.offset().top;
            }
        }

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
                    initProductsList(products);

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

        function loadMoreProducts() {
            var url = getFiltersUrl();

            url.setParams({page: paginator.data('page')});
            paginator.remove();

            $.ajax({
                url: url.getUrl(),
                success: function (resp) {
                    var products = $(resp.html.products);
                    products.appendTo(productsBox);
                    initProductsList(products);
                }
            });
        }

    }
});