<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class Price extends Entity
{
    public function __construct()
    {
        $this->setTable('products_price');

        $this->addProperties([
            'depend'      => [],
            'name'        => [],
            'price'       => [],
            'weight'      => [],
        ]);
    }
}