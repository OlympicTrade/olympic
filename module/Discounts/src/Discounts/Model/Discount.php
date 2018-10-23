<?php
namespace Discounts\Model;

use Aptero\Db\Entity\Entity;
use Catalog\Model\Product;
use Zend\Db\Sql\Expression;

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
            'color'       => ['default' => '000000'],
            'background'  => ['default' => 'ffffff'],
            'border'      => ['default' => '000000'],
            'shape'       => ['default' => 'square'],
            'date_from'   => [],
            'date_to'     => [],
        ]);

        $this->addPlugin('products', function($model) {
            $catalog = Product::getEntityCollection();
            $catalog->getPrototype()->addProperty('discount_new', array('virtual' => true));
            $catalog->select()
                ->join(array('dp' => 'discounts_products') , new Expression('dp.product_id = t.id AND dp.depend = ' . $model->getId()), array('discount_new' => 'discount'));

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('discounts_images');
            $image->setFolder('discounts');
            $image->addResolutions([
                'm' => [
                    'width'  => 795,
                    'height' => 695,
                    'opacity'=> true
                ],
            ]);

            return $image;
        });
    }
}