var Cart = function(){
    this.cart = [];
    this.price = 0;
    this.count = 0;
    this.sum   = 0;
    this.cookie = 'cart';

    this.init = function() {
        this.on('update', function() {
            this.save();
            this.sync();
        });

        var jsonCart = $.cookie(this.cookie);
        this.cart = jsonCart ? $.parseJSON(jsonCart) : [];
        this.trigger('update');
    };

    this.clear = function() {
        this.cart = [];
        this.trigger('update');
    };

    this.compare = function(data1, data2) {
        return data1.product_id == data2.product_id &&
               data1.taste_id   == data2.taste_id &&
               data1.size_id    == data2.size_id;
    };

    this.getProductInfo = function(data, callback) {
        $.ajax({
            url: '/catalog/get-product-info/',
            data: data,
            success: callback,
            dataType: 'json',
            method: 'post'
        });
    };

    this.add = function(data, options) {
        options = $.extend({
            count   : 'increase'
        }, options);

        var exists = false;

        for (var i = 0; i < this.cart.length; i++) {
            if(this.compare(this.cart[i], data)) {
                if(options.count == 'increase') {
                    data.count = parseInt(data.count) + parseInt(this.cart[i].count);
                }

                this.cart.splice(i, 1, data);
                exists = true;
            }
        }

        if(!exists) {
            this.cart.push(data);

            /*this.getProductInfo(data, function (resp) {
                window.dataYandex.push({ecommerce: {
                    add: { products: resp }
                }});

                ga('ec:addProduct', resp);
                ga('ec:setAction', 'add');
            });*/
        }

        /*getYandexCounter().reachGoal('cart_add');
        ga('send', 'event', 'cart', 'add');*/

        this.trigger('update');
    };

    this.del = function(data) {
        for (var i in this.cart) {
            if(this.compare(this.cart[i], data)) {
                this.cart.splice(i, 1);

                this.getProductInfo(data, function (resp) {
                    window.dataYandex.push({ecommerce: {
                        remove: { products: resp }
                    }});

                    getYandexCounter().reachGoal('cart_remove');
                });
                break;
            }
        }

        this.trigger('update');
    };

    this.order = function(order) {
        this.getProductInfo({products: this.cart}, function (resp) {
            window.dataYandex.push({purchase: {
                actionField: {
                    id:      order.id,
                    revenue: order.price,
                },
                products: {
                    products: resp
                }
            }});
        });

        getYandexCounter().reachGoal('order_complete');
    };

    this.getCart = function() {
        return this.cart;
    };

    this.save = function() {
        var cartJson = null;

        if(this.cart.length) {
            cartJson = JSON.stringify(this.cart);
        }

        $.cookie(this.cookie, cartJson, {expires: 365, path: "/"});
    };

    this.sync = function() {
        var cart = this;

        $.ajax({
            url: '/cart/get-info/',
            success: function(serverCart){
                cart.cart = [];
                for (var i in serverCart.cart) {
                    var product = serverCart.cart[i];
                    cart.cart.push(product)
                }

                cart.price = serverCart.price;
                cart.count = parseInt(serverCart.count);
                cart.sum   = serverCart.price;
                //cart.delivery   = serverCart.delivery;
                cart.save();
                cart.trigger('render');
            },
            dataType: 'json'
        });
    };

    this.on = function(event, fn) {
        $(this).on(event, fn);
    };

    this.trigger = function(event) {
        $(this).trigger(event);
    };
};

$.cart = new Cart();
$.cart.init();