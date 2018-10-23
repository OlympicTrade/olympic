<?php
namespace DiscountsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Discount extends Entity
{
    public function __construct()
    {
        $this->setTable('discounts');

        $this->addProperties([
            'name'        => [],
            'row_1'       => [],
            'row_2'       => [],
            'row_3'       => [],
            'discount'    => [],
            'color'       => ['default' => '#000000'],
            'background'  => ['default' => '#ffffff'],
            'border'      => ['default' => '#000000'],
            'shape'       => ['default' => 'square'],
            'date_from'   => [],
            'date_to'     => [],
        ]);

        $this->addPlugin('products', function($model) {
            $item = new Entity();
            $item->setTable('discounts_products');
            $item->addProperties([
                'depend'     => [],
                'product_id' => [],
                'discount'   => [],
            ]);
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('discounts_images');
            $image->setFolder('discounts');
            $image->addResolutions([
                'a' => [
                    'width'  => 162,
                    'height' => 162,
                    'opacity'=> true
                ],
                'hr' => [
                    'width'  => 1000,
                    'height' => 800,
                    'opacity'=> true
                ]
            ]);

            return $image;
        });
    }
}