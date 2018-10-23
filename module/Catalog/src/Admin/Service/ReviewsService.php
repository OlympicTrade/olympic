<?php
namespace CatalogAdmin\Service;

use Aptero\Service\Admin\TableService;

class ReviewsService extends TableService
{
    public function setFilter($collection, $filters)
    {
        $collection = parent::setFilter($collection, $filters);
        $collection->select()->order('status');

        return $collection;
    }
}