<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Size extends Entity
{
    public function __construct()
    {
        $this->setTable('products_size');

        $this->addProperties([
            'depend'      => [],
            'name'        => [],
            'size'        => [],
            'price'       => [],
            'weight'      => [],
        ]);

        $this->getEventManager()->attach([Entity::EVENT_PRE_DELETE], function ($event) {
            $model = $event->getTarget();

            $stock = new Stock();
            $stock->select()->where(['size_id' => $model->getId()]);
            $stock->remove();

            return true;
        });
    }
}