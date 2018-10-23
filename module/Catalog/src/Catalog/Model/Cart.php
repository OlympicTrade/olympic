<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class Cart extends Entity
{
    public function __construct()
    {
        $this->setTable('orders_cart');

        $this->addProperties([
            'order_id'    => [],
            'product_id'  => [],
            'size_id'     => [],
            'taste_id'    => [],
            'count'       => [], //Сколько реально доступно со склада
            'order_count' => [], //Сколько товаров заказал клиент
            'price'       => [],
        ]);

        $this->addPlugin('product', function($model) {
            $product = new Product();
            $product->addProperty('taste');
            $product->addProperty('size');

            $product->setId($model->get('product_id'));
            $product->select()
                ->join(array('pp' => 'products_size'), 't.id = pp.depend', array('price_base' => 'price', 'size' => 'name'))
                ->join(array('pt' => 'products_taste'), 't.id = pt.depend', array('coefficient', 'taste' => 'name'))
                ->where(array(
                    'pp.id' => $model->get('size_id'),
                    'pt.id' => $model->get('taste_id'),
                ));
            return $product;
        }, array('independent' => true));

        $this->addPlugin('size', function($model) {
            $price = new Size();
            $price->select()->where(array('id' => $model->get('size_id')));

            return $price;
        }, array('independent' => true));

        $this->addPlugin('taste', function($model) {
            $taste = new Taste();
            $taste->select()->where(array('id' => $model->get('taste_id')));

            return $taste;
        }, array('independent' => true));
    }
}