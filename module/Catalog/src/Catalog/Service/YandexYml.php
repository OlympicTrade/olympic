<?php

namespace Catalog\Service;

use Application\Model\Settings;
use Aptero\Service\AbstractService;
use Catalog\Model\Catalog;
use Catalog\Model\Product;
use Delivery\Model\City;

class YandexYml extends AbstractService
{
    public function getYml($uploadOpts)
    {
        $settings = new Settings();

        $rootXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><yml_catalog></yml_catalog>');
        $rootXML->addAttribute('date', date('Y-m-d H:i:s'));

        $shopXML = $rootXML->addChild('shop');
        $shopXML->addChild('name', $settings->get('site_name') . ' СПб');
        $shopXML->addChild('company', 'ООО Аптеро');
        $shopXML->addChild('url', $settings->get('domain'));
        $shopXML->addChild('platform', 'Zend Framework');
        $shopXML->addChild('version', '2');
        $shopXML->addChild('email', 'info@aptero.ru');

        $currenciesXML = $shopXML->addChild('currencies');
        $currencyXML = $currenciesXML->addChild('currency');
        $currencyXML->addAttribute('id', 'RUR');
        $currencyXML->addAttribute('rate', 1);

        $this->setDelivery($shopXML);
        $this->setCatalog($shopXML);
        $this->setProducts($shopXML, $settings, $uploadOpts);

        return $rootXML->asXML();
    }

    public function setCatalog($shopXML)
    {
        $catalogXML = $shopXML->addChild('categories');

        $catalog = Catalog::getEntityCollection();

        foreach($catalog as $category) {
            $categoryXML = $catalogXML->addChild('category', htmlspecialchars($category->get('name')));
            $categoryXML->addAttribute('id', $category->getId());

            if($category->get('parent')) {
                $categoryXML->addAttribute('parentId', $category->get('parent'));
            }
        }

        return $shopXML;
    }

    public function setDelivery($xml, $price = 0)
    {

        $deliveryXML = $xml->addChild('delivery-options');

        $deliveryCost = $price > 3000 ? 0 : 200;

        switch ((new \DateTime)->format('N')) {
            case 7:
                $optionXML = $deliveryXML->addChild('option');
                $optionXML->addAttribute('cost', $deliveryCost);
                $optionXML->addAttribute('days', '2');
                break;
            case 6:
                $optionXML = $deliveryXML->addChild('option');
                $optionXML->addAttribute('cost', $deliveryCost);
                $optionXML->addAttribute('days', '2');
                $optionXML->addAttribute('order-before', '11');

                $optionXML = $deliveryXML->addChild('option');
                $optionXML->addAttribute('cost', $deliveryCost);
                $optionXML->addAttribute('days', '3');
                break;
            default:
                $optionXML = $deliveryXML->addChild('option');
                $optionXML->addAttribute('cost', $deliveryCost);
                $optionXML->addAttribute('days', '1');
                $optionXML->addAttribute('order-before', '15');

                $optionXML = $deliveryXML->addChild('option');
                $optionXML->addAttribute('cost', $deliveryCost);
                $optionXML->addAttribute('days', '2');
                break;
        }

        return $xml;
    }

    public function setProducts(\SimpleXMLElement $shopXML, $settings, $uploadOpts)
    {
        $products = (new Product)->addProperties([
            'size_id'   => [],
            'size'      => [],
            'taste_id'  => [],
            'taste'     => [],
        ])->getCollection();

        $filter = [
            'join'      => ['brands', 'brand-country', 'catalog'],
            'columns'   => ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'subname', 'discount', 'url', 'barcode']
        ];

        if(!$uploadOpts['products']['full']) {
            $filter['minPrice'] = true;
            $select = $this->getProductsService()->getProductsSelect($filter);
        } else {
            $filter['yandexYmlFull'] = true;
            $filter['group'] = 'ps.id';
            $select = $this->getProductsService()->getProductsSelect($filter);
            $select->where
                ->nest()
                    ->greaterThan('ps.count', 0)
                    ->and
                    ->greaterThan('pst.coefficient', 0)
                    ->and
                    ->greaterThan('pss.price', 0)
                ->unnest();
        }

        $products->setSelect($select);
        $offersXML = $shopXML->addChild('offers');

        foreach($products as $product) {
            if(!$price = $product->get('price')) {
                continue;
            }

            $offerXML = $offersXML->addChild('offer');

            $brand = $product->getPlugin('brand');
            $catalog = $product->getPlugin('catalog');
            $attrs = $product->getPlugin('attrs');

            $offerXML->addAttribute('id', $this->getProductId($product));
            $offerXML->addAttribute('type', 'vendor.model');
            $offerXML->addAttribute('available', $product->get('stock') ? 'true' : 'false');

            $offerXML->addAttribute('bid',  5 /*min(3, (int) ($price * 0.03))*/);
            $offerXML->addAttribute('cbid', 5 /*min(3, (int) ($price * 0.03))*/);
            $offerXML->addAttribute('fee',  '100');

            $url = $settings->get('domain') . $product->getUrl('url');
            if(!empty($uploadOpts['utm'])) {
                $url .= (strpos($url, '?') ? '&' : '?') . implode('&', $uploadOpts['utm']);
                $url = str_replace('&', '&amp;', $url);
            }
            $offerXML->addChild('url', $url);

            $offerXML->addChild('price', (int) ($product->get('price')));
            $offerXML->addChild('currencyId', 'RUR');
            $offerXML->addChild('categoryId', $product->get('catalog_id'));

            $offerXML->addChild('country_of_origin', $brand->getPlugin('country')->get('name'));

            //$typeName = $product->getCategoryType()->get('ya_cat_name');
            $typeName = $product->get('name');
            
            $offerXML->addChild('typePrefix', $typeName);
            $offerXML->addChild('vendor', $brand->get('name'));

            $model = '';
            //$model = $product->get('name');

            if($uploadOpts['products']['full']) {
                if ($product->get('size')) {
                    $propName1 = $attrs->get('prop_name_1') ? $attrs->get('prop_name_1') : 'Размер';
                    $offerXML->addChild('param', $product->get('size'))->addAttribute('name', $propName1);
                    $model .= '' . $product->get('size') . '';
                }

                if ($product->get('taste')) {
                    $propName1 = $attrs->get('prop_name_2') ? $attrs->get('prop_name_2') : 'Вкус';
                    $offerXML->addChild('param', $product->get('taste'))->addAttribute('name', $propName1);
                    $model .= ' ' . $product->get('taste');
                }
            }

            @$offerXML->addChild('model', $model);

            if($product->get('discount')) {
                $offerXML->addChild('oldprice', $product->get('price_old'));
            }

            if($catalog->get('barcode')) {
                $offerXML->addChild('barcode', $catalog->get('barcode'));
            }

            /*if($catalog->get('ya_market_id')) {
                $offerXML->addChild('market_category', $catalog->get('ya_market_id'));
            }*/

            if(!empty($product->getPlugin('image')->hasImage())) {
                @$offerXML->addChild('picture', $settings->get('domain') . $product->getPlugin('image')->getImage('hr'));
            }

            if(!$product->get('preview')) {
                $offerXML->addChild('description', $product->get('preview'));
            }

            $rec = [];
            $recProducts = $product->getPlugin('recommended', ['auto' => false]);
            foreach($recProducts as $recProduct) {
                $rec[] = $recProduct->getId();
            }
            if($rec) {
                $offerXML->addChild('rec', implode(',', $rec));
            }

            //Доставка
            $offerXML->addChild('store', 'false');
            $offerXML->addChild('pickup', 'true');
            $offerXML->addChild('delivery', 'true');
            $this->setDelivery($offerXML, $product->get('price'));
        }

        return $shopXML;
    }

    public function getProductId($product)
    {
        return $product->getId() . 'S' . $product->get('size_id') . 'S' . $product->get('taste_id');
    }

    public function getPrice()
    {
        $products = (new Product)->addProperties([
            'size_id'   => [],
            'size'      => [],
            'taste_id'  => [],
            'taste'     => [],
        ])->getCollection();

        $filter = [
            'group' => 'ps.id',
            'yandexYmlFull' => true,
            'join'      => ['brands', 'brand-country', 'catalog'],
            'columns'   => ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'subname', 'discount', 'url', 'barcode']
        ];

        $select = $this->getProductsService()->getProductsSelect($filter);
        $select->where
            ->nest()
                ->greaterThan('ps.count', 0)
                ->and
                ->greaterThan('pst.coefficient', 0)
                ->and
                ->greaterThan('pss.price', 0)
            ->unnest();

        $products->setSelect($select);

        $price = 0;
        foreach($products as $product) {
            $price += $product->get('price') * $product->get('stock');
        }

        echo $price;die();
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}