<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use CatalogAdmin\Model\Plugin\ProductProps;
use Catalog\Model\Catalog;
use Aptero\Db\Plugin\Attributes;
use Zend\Db\Sql\Sql;

class Reviews extends Entity
{
    const SOURCE_MYRPOTEIN = 'myprotein';

    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

    public function __construct()
    {
        $this->setTable('products_reviews');

        $this->addProperties(array(
            'product_id'    => array(),
            'user_id'       => array(),
            'stars'         => array(),
            'name'          => array(),
            'review'        => array(),
            'answer'        => array(),
            'email'         => array(),
            'status'        => array(),
            'source'        => array(),
            'time_create'   => array(),
        ));
    }
}