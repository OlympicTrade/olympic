<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class ProductsProps extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('products_props');

        $this->addProperties([
            'depend'  => [],
            'name'    => [],
            'sort'    => [],

        ]);

        /*$this->addPlugin('rows', function($model) {
            $props = new Entity();
            $props->setTable('products_props_rows');
            $props->addProperties(array(
                'depend'     => [],
                'key'        => [],
                'val'        => [],
                'units'      => [],
                'multiplier' => [],
            ));

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });*/
    }
}