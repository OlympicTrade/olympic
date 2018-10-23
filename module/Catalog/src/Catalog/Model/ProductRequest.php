<?php
namespace Catalog\Model;

use Application\Model\Module;
use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;

class ProductRequest extends Entity
{
    public function __construct()
    {
        $this->setTable('orders_request');

        $this->addProperties([
            'product_id'  => [],
            'size_id'     => [],
            'taste_id'    => [],
            'contact'     => [],
            'time_create' => [],
        ]);
    }
}