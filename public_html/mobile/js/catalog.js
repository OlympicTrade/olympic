$(function() {
    productView($('.product-view'));
    catalogList();
    cartView();
    cartRender();
    userModel();
    orderForm();
});

function catalogList() {
    $('.catalog-tabs').tabs();
}

function orderForm() {
    $('.order-popup').on('click', function () {
        $.fancybox.open({
            src: '/order/',
            type: 'ajax',
            opts : {
                margin: [0, 0],
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

$(function() {
    $('.pr-link').on('click', function() {
        var link = $(this);
        var prEl = link.hasClass('product') ? link : link.closest('.product');

        $.ajax({
            url: '/catalog/get-product-info/',
            data: {
                product_id: prEl.data('id')
            },
            success: function (prInfo) {
                ga('ec:addProduct', {
                    id:         prInfo.id,
                    name:       prInfo.name,
                    category:   prInfo.catalog,
                    brand:      prInfo.brand,
                    variant:    prInfo.variant
                });

                var clickOpts = {};

                if(prEl.data('list')) { clickOpts.list = prEl.data('list') }

                ga('ec:setAction', 'click', clickOpts);

                ga('send', 'event', 'pr-link', 'click', 'EC', {
                    hitCallback: function() {
                        location.href = link.attr('href');
                    }
                });
            },
            dataType: 'json',
            method: 'post'
        });

        return false;
    });
});

function productView(box) {
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

    var product = box;
    var pic = $('.images .pic', product);
    var img = $('img', pic);
    var thumbs = $('.thumb', product);

    thumbs.on('click', function () {
        var el = $(this);
        var img = $('img', pic);

        el.addClass('active').siblings().removeClass('active');
        pic.data('size', el.data('size'))
            .data('taste', el.data('taste'))
            .removeClass('hide');

        img.attr('src', el.attr('href'))
            .data('zoom-image', el.data('zoom'));

        return false;
    });

    toCartForm(product, {
        priceUpdate: function (resp) {
            $('.price span', product).text($.aptero.price(resp.price));
            $('.price_old span', product).text($.aptero.price(resp.price_old));

            var countEl = $('.js-count', product);

            if (parseInt(resp.stock)) {
                countEl.attr('max', resp.stock).val(Math.min(resp.stock, countEl.val())).trigger('update');
                $('.in-stock', product).css({display: 'block'});
                $('.not-in-stock', product).css({display: 'none'});
            } else {
                countEl.attr('max', 999).trigger('update');
                $('.in-stock', product).css({display: 'none'});
                $('.not-in-stock', product).css({display: 'block'});
            }
        },
        toCart: function () {
            $('.to-cart', product).addClass('in-cart').removeClass('to-cart').text('В корзине');
        },
        typeChange: function (size_id, taste_id) {
            $('.in-cart', product).addClass('to-cart').removeClass('in-cart').text('В корзину');

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

    $('.js-product-request', box).on('click', function() {
        $.fancybox.open({
            src: '/order/product-request/',
            type: 'ajax',
            opts: {
                margin: [0, 0],
                ajax: {
                    type: "post",
                    settings: {
                        data: $.aptero.serializeArray(typeBox)
                    }
                }
            }
        });
    });

    $('.js-cart-add', box).on('click', function() {
        var btn = $(this);

        if(btn.hasClass('in-cart')) {
            return true;
        }

        $.cart.add($.aptero.serializeArray(typeBox));
        btn.addClass('in-cart');
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

        var navCart = $('.cart', '#header');
        var orderForm = $('.order-form');

        if ($.cart.count) {
            $('.desc', navCart).html($.aptero.price($.cart.sum) + ' <i class="fa fa-rub"></i>');
            $('.counter', navCart).text($.cart.count).fadeIn(200);
            $('.cart-price').text($.aptero.price($.cart.sum));

            if (orderForm.length) {
                var deliveryPrice = 0;

                switch ($('[name="attrs-delivery"]', orderForm).val()) {
                    case 'courier':
                        deliveryPrice = $.cart.delivery.courier;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    case 'pickup':
                        deliveryPrice = $.cart.delivery.pickup;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    default:
                        $('.cart-delivery').html('не выбрана');
                        break;
                }

                $('.cart-sum-price', orderForm).text($.aptero.price($.cart.sum + parseInt(deliveryPrice)));

                $('.cart-error').css({display: 'none'});
                $('.order-btn').css({display: 'block'});
            }
        } else {
            $('.desc', navCart).text('Пока пуста');
            $('.counter', navCart).fadeOut(200);
            $('.cart-list').html('<div class="empty">Ваша корзина пуста</div>');
        }
    });
}

function userModel() {
    $('.profile-tabs').tabs();
}