<?php
namespace CatalogAdmin\Model;

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
    }
}