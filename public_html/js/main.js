$(function() {
    initPopups();
    initElements($('body'));
    initNav();
    initCatalogSidebar();
    initDatepicker();
    initAutocomplete();
    initMetric();
    initTooltips();

    setTimeout(function () {
        $('.main-banner .slide').addClass('open');
    }, 300);

});

function initTooltips() {
    $('.tooltip').tooltip({
        'speed': 300,
        'mode': 'above',
        'space': 70
    });
}

function initMetric() {
    var url = $.aptero.url();
    url.init();

    $.ajax({
        url: '/metrics/init/',
        method: 'post',
        data: {
            query: url.getParams(),
            source: 'desktop'
        }
    });
}

function initCatalogSidebar() {
    var widget = $('.widget.brands');
    var box = $('.h-box', widget);
    var read = $('.readmore', widget);

    read.on('click', function () {
        if(box.is(':visible')) {
            box.slideUp(200);
            read.text('показать все');
        } else {
            box.slideDown(200);
            read.text('скрыть');
        }
    });

    var block = $('.text-block');
    box = $('.text', block);
    read = $('.readmore', block);

    read.on('click', function () {
        if(box.hasClass('open')) {
            box.removeClass('open');
        } else {
            box.addClass('open');
        }
    });
}

function initElements(box) {
    $('.select-group', box).each(function () {
        var group = $(this);
        var vals = $('span', group);
        var input = $('input', group);

        var setActive = function (val) {
            if(input.val() == val) { return; }

            input.val(val).trigger('change');
            $('span[data-value="' + val + '"]', group)
                .addClass('selected')
                .siblings()
                    .removeClass('selected');
        };

        vals.on('click', function () {
            setActive($(this).data('value'));
        });

        var initVal = input.val() ? input.val() : vals.eq(0).data('value');
        input.val('');
        setActive(initVal);
    });

    $('input[type="checkbox"]', box).each(function() {
        var checkbox = $(this);
        var label = checkbox.closest('label');

        label.addClass('checkbox');

        if(checkbox.is(":checked")) {
            label.addClass('checked');
        }

        label.on('click', function() {
            if(checkbox.is(":checked")) {
                label.addClass('checked');
            } else {
                label.removeClass('checked');
            }
        });
    });

    $('input[type="radio"]', box).each(function() {
        var radio = $(this);
        var label = radio.closest('label');

        label.addClass('radio');
        label.attr('data-name', radio.attr('name'));

        if(radio.is(":checked")) {
            label.addClass('checked');
        }

        label.on('click', function() {
            $('label[data-name="' + label.data('name') + '"]')
                .removeClass('checked');
            label.addClass('checked');
        })
    });

    $('.std-counter', box).each(function() {
        var el = $(this);
        var input = $('input', el);
        var incr = $('.incr', el);
        var decr = $('.decr', el);

        var maxNotice = incr.attr('title');
        function checkMinMax() {
            if(parseInt(input.val()) >= parseInt(input.attr('max'))) {
                incr.attr('title', maxNotice);
                incr.addClass('incr-max');
            } else {
                incr.attr('title', '');
                incr.removeClass('incr-max');
            }

            if(parseInt(input.val()) <= parseInt(input.attr('min'))) {
                decr.addClass('decr-min');
            } else {
                decr.removeClass('decr-min');
            }
        }

        checkMinMax();

        incr.on('click', function() {
            var count = parseInt(input.val()) + 1;
            var max = input.attr('max') ? parseInt(input.attr('max')) : 999;
            if(count > max) {
                return false;
            }

            input.val(count);
            checkMinMax();
        });

        decr.on('click', function() {
            var count = parseInt(input.val()) - 1;
            var min = input.attr('min') ? parseInt(input.attr('min')) : 1;
            if(count < min) {
                return false;
            }

            input.val(count);
            checkMinMax();
        });

        input.on('update', function() {
            checkMinMax();
        });

        var timer = null;
        $('.incr, .decr', el).on('click', function () {
            if(timer) clearTimeout(timer);
            timer = setTimeout(function() {
                input.trigger('change');
            }, 150);
        });
    });

    $('.element', box).each(function () {
        $('input, textarea, select', $(this)).on('focus', function () {
            $(this).closest('.element').addClass('focus');
        }).on('focusout', function () {
            $(this).closest('.element').removeClass('focus');
        }).on('keyup', function () {
            var element = $(this).closest('.element');
            if($(this).val()) {
                element.addClass('not-empty');
            } else {
                element.removeClass('not-empty');
            }
        }).trigger('keyup');
    });
}

function initPopups() {
    $('body').on('click', '.popup', function() {
        var el = $(this);

        $.fancybox.open({
            src: el.attr('href'),
            type: 'ajax',
            opts: {
                ajax: {
                    settings: {
                        data: el.data()
                    }
                },
                afterLoad: function(e, slide) {
                    slide.$slide.on('click', function(e) {
                        if($(e.target).hasClass('fancybox-slide')) {
                            $.fancybox.close()
                        }
                    });

                    initElements(e.$refs.slider);
                }
            }
        });

        return false;
    });

    /*$('body').on('click', '.popup-img', function() {
        var el = $(this);

        $.fancybox.open({
        });

        return false;
    });*/
}

function initNav() {
    var box = $('.nav-box');
    var nav = $('#nav');
    var navLine = 0;

    //$('.catalog ul', nav).css({minHeight: 312});

    $(window).on('resize', function() {
        navLine = box.offset().top + nav.innerHeight();
    }).trigger('resize');

    $(window).on('scroll', function() {
        if(box.offset().top < $(this).scrollTop()) {
            if(!nav.hasClass('fixed')) {
                nav.addClass('fixed');
            }
        } else {
            nav.removeClass('fixed');
        }
    }).trigger('scroll');

    /*$.ajax({
        url: '/catalog/popular/',
        success: function (resp) {
            $('.products', nav).html(resp);
        }
    });

    $('.menu > li', nav).hover(function () {
        var li = $(this);
        $('.box', li).fadeIn(100);
    }, function () {
        var li = $(this);
        $('.box', li).fadeOut(100);
    });*/
}

function initDatepicker() {
    $.config.datepicker = {
        clearText: 'Очистить',
        clearStatus: '',
        closeText: 'Закрыть',
        closeStatus: '',
        prevText: '',
        prevStatus: '',
        nextText: '',
        nextStatus: '',
        currentText: 'Сегодня',
        currentStatus: '',
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь', 'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн', 'Июл','Авг','Сен','Окт','Ноя','Дек'],
        monthStatus: '',
        yearStatus: '',
        weekHeader: 'Не',
        weekStatus: '',
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        dayStatus: 'DD',
        dateStatus: 'D, M d',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        initStatus: '',
        isRTL: false,
        minDate: 1,
        maxDate: 30,
    };

    $('.datepicker').datepicker($.config.datepicker);
}

function initAutocomplete() {
    var input = $('.search .query');
    var url   = '/catalog/search/';

    function stars(stars) {
        var html =
            '<div class="stars">';

        for($i = 0; $i <= 4; $i++) {
            $starFilling = stars - $i;

            if($starFilling >= 0.6) {
                $class = ' class="full"';
            } else if ($starFilling >= 0.1) {
                $class = ' class="half"';
            } else {
                $class = '';
            }

            html += '<div' + $class + '></div> ';
        }

        html +=
            '</div>';

        return html;
    }

    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu("option", "items", ".ac-item");
            $('.add-to-cart').menu("option", "disabled", true);
        },
        _renderItem: function(ul, item) {
            var li = $('<li></li>');
            li.addClass('ac-item');

            switch(item.type) {
                case 'title':
                    //li.addClass('ac-title').removeClass('ac-item').text(item.label);
                    break;
                case 'hr':
                    li.addClass('ac-hr').removeClass('ac-item').text(item.label);
                    break;
                case 'clear':
                    li.addClass('ac-clear').removeClass('ac-item').text(item.label);
                    break;
                case 'show-all':
                    li.addClass('ac-show-all').removeClass('ac-item');
                    li.append(
                        '<span>Показать еще</span>'
                    );
                    li.on('click', function() {
                        location.href = url + '?query=' + input.val();
                    });
                    break;
                case 'category':
                    li.addClass('ac-category');
                    li.append('<a href="' + item.url + '">' + item.label + '</a>');
                    break;
                case 'brand':
                    li.addClass('ac-brand');
                    li.append('<a href="' + item.url + '">' + item.label + '</a>');
                    break;
                case 'product':
                    li.addClass('ac-product');
                    li.append(
                        '<div class="pr-box">' +
                        '<div>' +
                        '<a href="' + item.url + '" class="pic"><img src="' + item.img + '"></a>' +
                        '<div class="info">' +
                        '<a href="' + item.url + '" class="title">' + item.label + '</a>' +
                        '<div>' +
                        stars(item.stars) +
                        '<span class="reviews">' + item.reviews + '</span>' +
                        '</div>' +
                        '<span class="price"><span>от</span> ' + $.aptero.price(item.price) + ' <i class="fa fa-ruble-sign"></i></span> ' +
                            /*'<div class="order-box">' +
                             '<a href="/order/cart-form/?pid=' + item.id + '" class="btn s add-to-cart popup">В корзину</a>' +
                             '</div>' +*/
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                    break;
                default:
                    li.append('<a href="#">' + item.label + '</a>')
            }

            if(item.hide) {
                li.addClass('hide').removeClass('ac-item');;
            }

            return li.appendTo(ul)
        }
    });

    var pos = {my: "left top", at: "left bottom"};

    input.catcomplete({
        position: pos,
        source: function(request, response) {
            $.ajax({
                url: url,
                type: "get",
                dataType: "json",
                data: {
                    query: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            if(ui.item.url) {
                location.href = ui.item.url;
            }
        },
        open: function(event, ui) {
            $('.order-box .js-to-cart', '.ac-product').on('click', function(e) {
                var el = $(this);

                cart.add({
                    id:    el.data('id'),
                    count: 1
                });

                e.stopPropagation();
                return false;
            });
        },
        lookup           : 'res',
        maxHeight        : 300,
        width            : 630,
        zIndex           : 9999,
        deferRequestBy   : 300,
        params           : {limit: 10},
    });
}

var mapsArr = [];

function setPickupMap(options) {
    var map = null;

    $.each(mapsArr, function (key, val) {
        if(val.id == options.id) {
            val.map.destroy();
            mapsArr.splice(key, 1);
        }
    });

    $.getScript(libs.libYandexMaps, function() {
        var data = options.pointsData ? options.pointsData : {};
        var url = options.url ? options.url : '/delivery/points-map-data/';

        $.ajax({
            url: url,
            method: 'post',
            data: data,
            success: function (resp) {
                options = $.extend(options, {
                    center: resp.center,
                    points: resp.points,
                });

                initMap(options);
            }
        });
    });

    var initMap = function (options) {
        if(!options.center.lat || !options.center.lon) {
            return;
        }

        ymaps.ready(function() {
            map = new ymaps.Map(options.id, {
                center: [options.center.lat, options.center.lon],
                controls: [],
                zoom: (options.zoom ? options.zoom : 11)
            });

            var clusterer = new ymaps.Clusterer({
                preset: 'twirl#invertedBlueClusterIcons',
                clusterDisableClickZoom: false,
            });

            var markers = [];
            options.points.forEach(function(point) {
                var marker = new ymaps.Placemark([point.lat, point.lon], {
                    balloonContent: point.desc

                }, {
                    preset: "islands#blackHomeIcon",
                });

                markers.push(marker);
                map.geoObjects.add(marker);
            });

            //clusterer.add(markers);
            //map.geoObjects.add(clusterer);
            //map.geoObjects.add(markers);

            if(options.scrollZoom !== undefined && options.scrollZoom == false) {
                map.behaviors.disable('scrollZoom');
            }

            map.controls.add('zoomControl', { top: 10, left: 5 });

            if(options.onInit) {
                options.onInit();
            }

            mapsArr.push({
                id: options.id,
                map: map
            });
        });
    };

    return map;
}

function initSidebar(sidebar) {
    if($.inArray('asdas', ['qwe', 'as1as', '123']) == 1) {
        console.log('asd');
    }

    var container = sidebar.parent();
    var slider = sidebar.children();

    slider.css({
        top: 0,
        left: 0,
        position: 'relative',
        width: sidebar.innerWidth(),
        overflow: 'hidden'
    });

    sidebar.css({height: container.innerHeight() - parseInt(container.css('padding-top')) - parseInt(container.css('padding-bottom'))});

    var navH = $('#nav').innerHeight();
    var sidebarMargin = 20;
    var sidebarTop = sidebar.offset().top/* - navH*/;
    var botLine = sidebar.innerHeight() - slider.innerHeight();
    var sliderH = slider.innerHeight();
    var windowH = $(window).height();
    var oldScrollTop = 0;

    $(window).on('resize', function () {
        windowH = $(window).height();
        navH = $('#nav').outerHeight();
    });

    var top;
    $(window).on('scroll', function() {
        var newScroll = $(this).scrollTop();

        var sizeRevert = (windowH - navH - sidebarMargin) > sliderH;

        if(!sizeRevert) {
            if(newScroll < oldScrollTop) {
                if(newScroll > (sidebarTop + top - sidebarMargin)) {
                    oldScrollTop = newScroll;
                    return;
                }
                top = newScroll - (sidebarTop - navH - sidebarMargin);
            } else {
                if(newScroll < (sidebarTop + top + sliderH - windowH + sidebarMargin)) {
                    oldScrollTop = newScroll;
                    return;
                }
                top = newScroll - (sidebarTop + sliderH - windowH + sidebarMargin);
            }
        } else {
            if(newScroll < oldScrollTop) {
                if(newScroll > (sidebarTop + top + sliderH - windowH + sidebarMargin)) {
                    oldScrollTop = newScroll;
                    return;
                }
                top = newScroll - (sidebarTop + sliderH - windowH + sidebarMargin);
            } else {
                if(newScroll < (sidebarTop + top - navH - sidebarMargin)) {
                    oldScrollTop = newScroll;
                    return;
                }
                top = newScroll - (sidebarTop - navH - sidebarMargin);
            }
        }

        top = Math.min(top, botLine);
        top = Math.max(top, 0);

        slider.css({top: top});
        oldScrollTop = newScroll;
    }).trigger('scroll');
}