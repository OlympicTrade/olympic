var Products = function(){
    this.products = [];
    this.cookie = 'viewed-products';

    this.init = function(cookie) {
        this.cookie = cookie;
        var jsonProducts = $.cookie(this.cookie);
        this.products = jsonProducts ? $.parseJSON(jsonProducts) : [];
        return this;
    };

    this.clear = function() {
        this.products = [];

        this.save();
    };
    
    this.count = function () {
        return this.products.length
    };

    this.add = function(data) {
        var exists = false;

        data.date = $.now();

        for (var i = 0; i < this.products.length; i++) {
            if(this.products[i].id == data.id) {
                data.count = this.products[i].count + 1;
                this.products.splice(i, 1, data);
                exists = true;
            }
        }

        if(!exists) {
            data.count = 1;
            this.products.push(data);
        }

        this.save();
    };

    this.del = function(id) {
        for (var i = 0; i < this.products.length; i++) {
            if(this.products[i].id == id) {
                this.products.splice(i, 1);
            }
        }
        this.save();

        this.trigger('update');
    };

    this.getProducts = function() {
        return this.products;
    };

    this.save = function() {
        var productsJson = null;

        if(this.products.length) {
            productsJson = JSON.stringify(this.products);
        }

        this.products.sort(function(a, b){
            return (a.date < b.date);
        });

        this.products = this.products.splice(0, 9);
        $.cookie(this.cookie, productsJson, {expires: 365, path: "/"});

        this.trigger('update');
    };

    this.on = function(event, fn) {
        $(this).on(event, fn);
    };

    this.trigger = function(event) {
        $(this).trigger(event);
    };
};
