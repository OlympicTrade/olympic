<?php

namespace Catalog;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'adminSupplyCart'       => 'CatalogAdmin\View\Helper\SupplyCart',
                'adminCartList'         => 'CatalogAdmin\View\Helper\CartList',
                'AdminOrderDelivery'    => 'CatalogAdmin\View\Helper\OrderDelivery',
                'ProductItem'           => 'Catalog\View\Helper\ProductItem',
                'ProductsList'          => 'Catalog\View\Helper\ProductsList',
                'CatalogMenu'           => 'Catalog\View\Helper\CatalogMenu',
                'CartList'              => 'Catalog\View\Helper\CartList',
                'CartTypeBoxSelect'     => 'Catalog\View\Helper\CartTypeBoxSelect',
                'OrderInfo'             => 'Catalog\View\Helper\OrderInfo',
                'OrdersList'            => 'Catalog\View\Helper\OrdersList',
                'OrderCartList'         => 'Catalog\View\Helper\OrderCartList',
                'ProductsShortList'     => 'Catalog\View\Helper\ProductsShortList',
                'ProductTabs'           => 'Catalog\View\Helper\ProductTabs',
                'ProductText'           => 'Catalog\View\Helper\ProductText',
                'MobileProductsShortList' => 'Catalog\View\Helper\MobileProductsShortList',
                'MobileProductsList'    => 'Catalog\View\Helper\MobileProductsList',
                'MobileProductTabs'     => 'Catalog\View\Helper\MobileProductTabs',
                'MobileCatalogMenu'     => 'Catalog\View\Helper\MobileCatalogMenu',
                'MobileCatalogList'     => 'Catalog\View\Helper\MobileCatalogList',
                'CatalogWidgets'        => 'Catalog\View\Helper\CatalogWidgets',
                'CompareList'           => 'Catalog\View\Helper\CompareList',
                'CatalogListShort'      => 'Catalog\View\Helper\CatalogListShort',
            ),
            'factories' => array(
                'cartWidget' => function ($sm) {
                    $catalog = $sm->getServiceLocator()->get('Catalog\Service\CartService')->getCartInfo();
                    return new \Catalog\View\Helper\cartWidget($catalog);
                },
                'catalogWidget' => function ($sm) {
                    $catalog = $sm->getServiceLocator()->get('Catalog\Model\Catalog')->getCollection()->setParentId(0);
                    return new \Catalog\View\Helper\CatalogWidget($catalog);
                },
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Catalog\Service\CatalogService'  => 'Catalog\Service\CatalogService',
                'Catalog\Service\ProductsService' => 'Catalog\Service\ProductsService',
                'Catalog\Service\CartService'     => 'Catalog\Service\CartService',
                'Catalog\Service\OrdersService'   => 'Catalog\Service\OrdersService',
                'Catalog\Service\PaymentService'  => 'Catalog\Service\PaymentService',
                'Catalog\Service\SuppliesService' => 'Catalog\Service\SuppliesService',
                'Catalog\Service\BrandsService'   => 'Catalog\Service\BrandsService',
                'Catalog\Service\GoogleMerchant'  => 'Catalog\Service\GoogleMerchant',
                'Catalog\Service\SyncService'     => 'Catalog\Service\SyncService',
                'Catalog\Service\SystemService'   => 'Catalog\Service\SystemService',
                'Catalog\Service\YandexMarket'    => 'Catalog\Service\YandexMarket',
                'Catalog\Service\YandexYml'       => 'Catalog\Service\YandexYml',

                'CatalogAdmin\Model\Orders'       => 'CatalogAdmin\Model\Orders',
                'CatalogAdmin\Model\Products'     => 'CatalogAdmin\Model\Products',
                'CatalogAdmin\Model\Size'         => 'CatalogAdmin\Model\Size',
                'CatalogAdmin\Model\Taste'        => 'CatalogAdmin\Model\Taste',
                'CatalogAdmin\Model\Catalog'      => 'CatalogAdmin\Model\Catalog',
                'CatalogAdmin\Service\OrdersService'   => 'CatalogAdmin\Service\OrdersService',
                'CatalogAdmin\Service\BrandsService'   => 'CatalogAdmin\Service\BrandsService',
                'CatalogAdmin\Service\CatalogService'  => 'CatalogAdmin\Service\CatalogService',
                'CatalogAdmin\Service\ProductsService' => 'CatalogAdmin\Service\ProductsService',
                'CatalogAdmin\Service\ReviewsService'  => 'CatalogAdmin\Service\ReviewsService',
                'CatalogAdmin\Service\SuppliesService' => 'CatalogAdmin\Service\SuppliesService',
                'CatalogAdmin\Service\RequestsService' => 'CatalogAdmin\Service\RequestsService',
                'CatalogAdmin\Service\StocksService'   => 'CatalogAdmin\Service\StocksService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Admin',
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}