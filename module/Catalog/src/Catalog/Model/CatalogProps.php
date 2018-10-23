<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class CatalogProps extends Entity
{
    public function __construct()
    {
        $this->setTable('catalog_props');

        $this->addProperties([
            'depend'  => [],
            'name'    => [],
            'sort'    => [],
        ]);

        $this->addPlugin('rows', function($model, $options) {
            $props = new Entity();
            $props->setTable('products_props_vals');
            $props->addProperties([
                'depend'     => [],
                'prop_id'    => [],
                'key'        => [],
                'val'        => [],
                'units'      => [],
                'multiplier' => [],
                'compare'    => [],
            ]);

            $catalog = $props->getCollection();
            $catalog->select()
                ->where([
                    'prop_id'   => $model->getId(),
                    'depend'    => $options['product_id'],
                ]);

            return $catalog;
        });
    }
}