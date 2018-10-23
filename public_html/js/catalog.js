var compareProducts = new Products().init('compare-list');

$(function() {
    cartRender();
    productView($('.product-view'));
    compareList();
    cartView();
    orderForm();
});

function orderForm() {
    $('.order-popup').on('click', function () {
        $.fancybox.open({
            src: '/order/',
            type: 'ajax',
            opts : {
                closeClickOutside : false,
                closeBtn: false,
                closeTpl: '',
                afterLoad: function(e) {
                    initElements(box);
                }
            }
        });
        return false;
    });
}

function compareList() {
    compareProducts.on('update', function () {
        var count = compareProducts.count();
        var countBox = $('.compare-count');
        if(count > 9) {
            countBox.text('9+').fadeIn(200);
        } else if(count > 0) {
            countBox.text(count).fadeIn(200);
        } else {
            countBox.empty().fadeOut(200);
        }
    });

    compareProducts.trigger('update');

    $('body').on('click', '.compare-add', function () {
        var el = $(this);

        if(el.hasClass('active')) {
            return true;
        }

        compareProducts.add({
            id:  el.data('pid'),
            cid: el.data('cid')
        });

        $('span', el).text('Добавлен к сравнению');
        el.addClass('active');

        return false;
    });

    $('body').on('click', '.compare-del', function () {
        compareProducts.del($(this).data('id'));

        var table = $('.compare-table');
        var index = $(this).closest('th').index() + 1;

        $('th:nth-child(' + index + '), td:nth-child(' + index + ')', table).remove();
    });
}

function productView(box, options) {
    if(!options) options = {};

    var tabs = $('.product-tabs');

    tabs.tabs({
        historyMode: true,
        disablePushState: false
    });

    tabs.on('click', 'a.readmore', function() {
        var tab = $(this).data('tab');
        if(!tab) {
            return true;
        }

        $('.tabs-header .tab-' + tab, tabs).trigger('click');
        return false;
    });

    tabs.on('change', '.composition-trigger select', function () {
        var weight = parseFloat($(this).val());
        var price = parseInt($('option[value="' + $(this).val() + '"]', $(this)).data('price'));

        $('.composition-list .val.nbr', tabs).each(function () {
            var el = $(this);
            var pWeight = parseFloat(el.data('val'));

            if(!pWeight) {
                $('.p', el).empty();
                return;
            }

            var pUnits  = el.data('units');
            $('.w', el).text(Math.round(pWeight * weight) + ' ' + pUnits);

            var fPrice;
            if(price && (fPrice = Math.round((price / weight) * (100 / pWeight))) < 1200) {
                $('.p', el).text('(' + fPrice + ' руб. за 100 ' + pUnits + ')');
            } else {
                $('.p', el).empty();
            }
        });
    });

    var product = $('.product', box);
    var pic = $('.images .pic', product);
    var img = $('img', pic);
    var thumbs = $('.thumb', product);

    var zoomOpts = {
        zoomWindowFadeIn: 200,
        zoomWindowFadeOut: 200,
        zoomWindowWidth:368,
        zoomWindowHeight:441,
        cursor: 'move',
        easing : true,
        zoomType : 'inner',
        zIndex: options.zoomZindex ? options.zoomZindex : '80'
    };

    thumbs.on('click', function () {
        var el = $(this);
        var img = $('img', pic);

        el.addClass('active').siblings().removeClass('active');
        pic.data('size', el.data('size'))
            .data('taste', el.data('taste'))
            .removeClass('hide');

        img.attr('src', el.attr('href'))
            .data('zoom-image', el.data('zoom'));

        img.removeData('elevateZoom');
        $('.zoomContainer').remove();
        img.elevateZoom(zoomOpts);

        return false;
    });

    toCartForm(product, {
        priceUpdate: function (resp) {
            $('.price span', product).text($.aptero.price(resp.price));
            $('.price-old span', product).text($.aptero.price(resp.price_old));

            var countEl = $('.js-count', product);

            if (parseInt(resp.stock)) {
                countEl.attr('max', resp.stock).val(Math.min(resp.stock, countEl.val())).trigger('update');
                $('.stock-i', product).css({display: 'block'});
                $('.stock-o', product).css({display: 'none'});
            } else {
                countEl.attr('max', 999).trigger('update');
                $('.stock-i', product).css({display: 'none'});
                $('.stock-o', product).css({display: 'block'});
            }
        },
        toCart: function () {
            $('.cart-add', product).addClass('in-cart').removeClass('cart-add').find('span').text('Оформить заказ');
        },
        typeChange: function (size_id, taste_id) {
            $('.in-cart', product).addClass('cart-add').removeClass('in-cart').find('span').text('В корзину');

            thumbs.each(function () {
                var thumb = $(this);
                var th_size_id = thumb.data('size');
                var th_taste_id = thumb.data('taste');

                if((th_size_id && th_size_id != size_id) || (th_taste_id && th_taste_id != taste_id)) {
                    thumb.addClass('hide');
                } else {
                    thumb.removeClass('hide');
                }
            });

            if((pic.data('taste') && pic.data('taste') != taste_id) || (pic.data('size') && pic.data('size') != size_id)) {
                pic.addClass('hide');
            }

            if(pic.hasClass('hide')) {
                $('.thumb:not(.hide)', product).eq(0).trigger('click');
            }
        }
    });
}

function toCartForm(box, options) {
    if(!box.length) { return; }

    var typeBox = $('.type-box', box);
    var sizeSelect  = $('[name="size_id"]', typeBox);
    var tasteSelect = $('[name="taste_id"]', typeBox);
    var countSelect = $('[name="count"]', typeBox);

    function updatePrice() {
        var data = $.aptero.serializeArray(typeBox);

        $.ajax({
            url: '/catalog/get-price/',
            method: 'post',
            data: data,
            success: options.priceUpdate
        });

        $('.js-cart-add', box).removeClass('cart-in');
    }

    sizeSelect.on('change', function() {
        $.ajax({
            url: '/catalog/get-product-stock/',
            method: 'post',
            data: $.aptero.serializeArray(typeBox),
            success: function(resp) {
                $('option', tasteSelect).each(function() {
                    var el = $(this);
                    var id = el.attr('value');

                    if(parseInt(resp[sizeSelect.val()].taste[id])) {
                        el.addClass('green').removeClass('red');
                    } else {
                        el.addClass('red').removeClass('green');
                    }
                });

                if($('option[value="' + tasteSelect.val() + '"]', tasteSelect).hasClass('red') && $('option.green', tasteSelect).length) {
                    tasteSelect.val($('option.green', tasteSelect).attr('value')).trigger('change');
                } else {
                    options.typeChange(sizeSelect.val(), tasteSelect.val());
                    updatePrice();
                }
            }
        });

    });

    tasteSelect.on('change', function() {
        options.typeChange(sizeSelect.val(), tasteSelect.val());
        updatePrice();
    });

    countSelect.on('change', function() {
        updatePrice();
    });

    sizeSelect.trigger('change');

    $('.js-request-add', box).on('click', function() {
        $.fancybox.open({
            src: '/order/product-request/',
            type: 'ajax',
            opts: {
                ajax: {
                    settings: {
                        data: $.aptero.serializeArray(typeBox)
                    }
                }
            }
        });
    });

    $('.js-cart-add', box).on('click', function() {
        var btn = $(this);

        if(btn.hasClass('cart-in')) {
            return true;
        }

        $.cart.add($.aptero.serializeArray(typeBox));
        btn.addClass('cart-in');
        options.toCart();
        return false;
    });
}

function cartView() {
    var box = $('.cart-list');
    if (!box.length) { return; }

    box.on('change', '.cart-count', function() {
        var el = $(this);

        var data = el.closest('.product').data();
        data.count = parseInt(el.val());

        if(data.count > 0) {
            $.cart.add(data, {count: 'replace'});
        }
    });

    box.on('click', '.cart-del', function() {
        var product = $(this).closest('.product');
        product.fadeOut(200);
        $.cart.del(product.data());
    });

    $.cart.on('update', function () {
        $.ajax({
            url: '/delivery/delivery-notice/',
            method: 'post',
            success: function (resp) {
                $('.delivery-notice', box).replaceWith(resp.html);
            }
        });
    });
}

function cartRender() {
    $.cart.on('render', function () {
        for (var i in $.cart.cart) {
            var product = $.cart.cart[i];

            var productEl = $('.product' +
                '[data-product_id="' + product.product_id + '"]' +
                '[data-size_id="' + product.size_id + '"]' +
                '[data-taste_id="' + product.taste_id + '"]', '.cart-list');

            $('.sum span', productEl).text($.aptero.price(product.price * product.count));
        }

        //var navCart = $('.item.cart', '#nav');
        var orderForm = $('.order-form');

        if ($.cart.count) {
            $('.cart-count').text($.cart.count > 9 ? '9+' : $.cart.count).fadeIn(200);
            $('.cart-price').text($.aptero.price($.cart.sum));

            if (orderForm.length) {
                var deliveryPrice = 0;

                switch ($('[name="attrs-delivery"]', orderForm).val()) {
                    case 'courier':
                        deliveryPrice = $.cart.delivery.courier;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-ruble-sign"></i>' : 'бесплатно');
                        break;
                    case 'pickup':
                        deliveryPrice = $.cart.delivery.pickup;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-ruble-sign"></i>' : 'бесплатно');
                        break;
                    default:
                        $('.cart-delivery').html('не выбрана');
                        break;
                }

                $('.cart-full-price', orderForm).text($.aptero.price($.cart.sum + parseInt(deliveryPrice)));

                if($.cart.sum < 400) {
                    $('.cart-error').css({display: 'block'});
                    $('.order-btn').css({display: 'none'});
                } else {
                    $('.cart-error').css({display: 'none'});
                    $('.order-btn').css({display: 'block'});
                }
            }
        } else {
            $('.cart-count').fadeOut(200);
            $('.cart-list').html('<div class="empty-list">Ваша корзина пуста</div>');
        }
    });
}