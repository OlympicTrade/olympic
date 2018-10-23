<?php
return [
    'controllers' => [
        'invokables' => [
            'Catalog\Controller\Catalog' => 'Catalog\Controller\CatalogController',
            'Catalog\Controller\Orders'  => 'Catalog\Controller\OrdersController',
            'Catalog\Controller\Payment' => 'Catalog\Controller\PaymentController',
            'Catalog\Controller\Parser'  => 'Catalog\Controller\ParserController',
            'Catalog\Controller\Yandex'  => 'Catalog\Controller\YandexController',
            'CatalogAdmin\Controller\Catalog'   => 'CatalogAdmin\Controller\CatalogController',
            'CatalogAdmin\Controller\Products'  => 'CatalogAdmin\Controller\ProductsController',
            'CatalogAdmin\Controller\Brands'    => 'CatalogAdmin\Controller\BrandsController',
            'CatalogAdmin\Controller\Orders'    => 'CatalogAdmin\Controller\OrdersController',
            'CatalogAdmin\Controller\Reviews'   => 'CatalogAdmin\Controller\ReviewsController',
            'CatalogAdmin\Controller\Requests'  => 'CatalogAdmin\Controller\RequestsController',
            'CatalogAdmin\Controller\Supplies'  => 'CatalogAdmin\Controller\SuppliesController',
            'CatalogAdmin\Controller\Stocks'    => 'CatalogAdmin\Controller\StocksController',
            'Catalog\Controller\MobileCatalog'  => 'Catalog\Controller\MobileCatalogController',
            'Catalog\Controller\MobileOrders'   => 'Catalog\Controller\MobileOrdersController',
            'Catalog\Controller\MobilePayment'  => 'Catalog\Controller\MobilePaymentController',
        ],
    ],
    'router' => [
        'routes' => [
            'catalog' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/catalog',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'catalog' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'productGetPrice' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/get-price/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'get-price',
                            ],
                        ],
                    ],
                    'catalogParser' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/parser/:action/',
                            'constraints' => ['url' => '.*'],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Parser',
                                'controller' => 'Catalog\Controller\Parser',
                            ],
                        ],
                    ],
                    'popProducts' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/popular[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'popular-products',
                            ],
                        ],
                    ],
                    'productsList' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/products-list/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'productsList',
                            ],
                        ],
                    ],
                    'getProductInfo' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/get-product-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductInfo',
                            ],
                        ],
                    ],
                    'getProductStock' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/get-product-stock/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductStock',
                            ],
                        ],
                    ],
                    'productAddReview' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/add-review/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'addReview',
                            ],
                        ],
                    ],
                    'productRequestFrom' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/product-request/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'product-request',
                            ],
                        ],
                    ],
                    'recoProduct' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/get-reco-product/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getRecoProduct',
                            ],
                        ],
                    ],
                    'abandonedOrders' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/abandoned-orders/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'abandoned-orders',
                            ],
                        ],
                    ],
                    'productsPopularity' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/products-popularity/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'products-popularity',
                            ],
                        ],
                    ],
                ],
            ],
            'yandexMarket' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/yandex/market',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'yandexMarket' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/yml/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'yml',
                            ],
                        ],
                    ],
                    'yandexApiCart' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/api/cart/',
                            /*'constraints' => [
                                'action' => '.+',
                            ],*/
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'apiCart',
                            ],
                        ],
                    ],
                    'yandexApiOrder' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/api/order/accept/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'apiOrderAccept',
                            ],
                        ],
                    ],
                ],
            ],
            'catalogOrders' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/order',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'cartFrom' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/cart-form/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'cart-form',
                            ],
                        ],
                    ],
                    'productRequestFrom' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/product-request/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'product-request',
                            ],
                        ],
                    ],
                ],
            ],
            'catalogBrands' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/brands',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'brands' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Brands',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'brands',
                            ],
                        ],
                    ],
                    'brandsProducts' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '[/:url]/products/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Brands',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'brandProducts',
                            ],
                        ],
                    ],
                ],
            ],
            'catalogProduct' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/goods/:url[/:tab]/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'product',
                    ],
                ],
            ],
            'mobile' => [
                'type' => 'Hostname',
                'priority' => 600,
                'options' => [
                    'route' => 'm.:domain',
                    'constraints' => ['domain' => '.*',],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'catalog' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'ajaxProducts' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/ajax-products/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'ajax-products',
                            ],
                        ],
                    ],
                    'cart' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/cart/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\MobileOrders',
                                'action'     => 'cart',
                            ],
                        ],
                    ],
                    'cartInfo' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/cart/get-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'cartInfo',
                            ],
                        ],
                    ],
                    'orderInfo' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/get-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'orderInfo',
                            ],
                        ],
                    ],
                    'getProductInfo' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/get-product-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductInfo',
                            ],
                        ],
                    ],
                    'getProductStock' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/get-product-stock/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductStock',
                            ],
                        ],
                    ],
                    'productGetPrice' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/catalog/get-price/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getPrice',
                            ],
                        ],
                    ],
                    'catalogSearch' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/catalog/search/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'search',
                            ],
                        ],
                    ],
                    'product' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/goods/:url[/:tab]/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'product',
                            ],
                        ],
                    ],

                    'order' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'order',
                            ],
                        ],
                    ],
                    'orderStatus' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/order-status/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'orderStatus',
                            ],
                        ],
                    ],
                    'orderPayment' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/payment[/:action]/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\MobilePayment',
                                'action'     => 'payment',
                            ],
                        ],
                    ],
                    'productRequestFrom' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/order/product-request/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'product-request',
                            ],
                        ],
                    ],
                    'productAddReview' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/catalog/add-review/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'add-review',
                            ],
                        ],
                    ],
                ],
            ],

            'compare' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/compare/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'compare',
                    ],
                ],
            ],
            /*'catalog' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog[/:url]/',
                    'constraints' => [
                        'url' => '.*',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'index',
                    ],
                ],
            ],
            'productGetPrice' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/get-price/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'get-price',
                    ],
                ],
            ],
            'catalogParser' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/parser/:action/',
                    'constraints' => ['url' => '.*'],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Parser',
                        'controller' => 'Catalog\Controller\Parser',
                    ],
                ],
            ],
            'popProducts' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/popular[/:url]/',
                    'constraints' => [
                        'url' => '.*',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'popular-products',
                    ],
                ],
            ],

            'productsList' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/products-list/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'productsList',
                    ],
                ],
            ],
            'getProductInfo' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/get-product-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getProductInfo',
                    ],
                ],
            ],
            'getProductStock' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/get-product-stock/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getProductStock',
                    ],
                ],
            ],
            'catalogCartFrom' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/cart-form/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cart-form',
                    ],
                ],
            ],
            'productRequestFrom' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/product-request/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'product-request',
                    ],
                ],
            ],
            'catalogRecoProduct' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/catalog/get-reco-product/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getRecoProduct',
                    ],
                ],
            ],*/
            'googleMerchant' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/google-merchant/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'googleMerchant',
                    ],
                ],
            ],
            'catalogSearch' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/search/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'search',
                    ],
                ],
            ],
            'cart' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/cart/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cart',
                    ],
                ],
            ],
            'cartInfo' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/cart/get-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cartInfo',
                    ],
                ],
            ],
            'orderInfo' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/get-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orderInfo',
                    ],
                ],
            ],
            'order' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'order',
                    ],
                ],
            ],
            'orderStatus' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/order-status/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orderStatus',
                    ],
                ],
            ],
            'orders' => [
                'type'    => 'literal',
                'priority' => 500,
                'options' => [
                    'route'    => '/orders/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orders',
                    ],
                ],
            ],
            'orderCart' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/cart/:id/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orderCart',
                    ],
                ],
            ],
            'orderPayment' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/payment[/:action]/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Payment',
                        'action'     => 'payment',
                    ],
                ],
            ],
            'adminCatalog' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/catalog[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'CatalogAdmin\Controller\Catalog',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProductsReviews' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/reviews[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Reviews',
                        'controller' => 'CatalogAdmin\Controller\Reviews',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProductsRequests' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/requests[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Requests',
                        'controller' => 'CatalogAdmin\Controller\Requests',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProducts' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/products[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'CatalogAdmin\Controller\Products',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminBrands' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/brands[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Brands',
                        'controller' => 'CatalogAdmin\Controller\Brands',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminOrders' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/orders[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'CatalogAdmin\Controller\Orders',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminSupplies' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/supplies[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Supplies',
                        'controller' => 'CatalogAdmin\Controller\Supplies',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminStocks' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/stocks[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Stocks',
                        'controller' => 'CatalogAdmin\Controller\Stocks',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'catalog' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'catalogList' => 'Catalog\View\Helper\CatalogList',
        ],
    ],
];