<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Aptero\String\Search;
use Catalog\Model\Brand;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class BrandsService extends AbstractService
{
    public function getBrandForSearch($query)
    {
        $brand = new Brand();
        $brand->select()
            ->columns(['id', 'name', 'url'])
            ->where(['name' => $query]);

        return $brand;
    }

    public function getBrandByUrl($subUrl)
    {
        $brand = new Brand();
        $brand->select()->where(['url' => $subUrl]);

        return $brand->load();
    }

    public function getProductsBrands($filters = [])
    {
        $brands = Brand::getEntityCollection();
        $brands->setSelect($this->getBrandsSelect($filters));

        return $brands;
    }

    public function getBrands($filters = [])
    {
        $brand = new Brand();

        if($filters['count']) {
            $brand->addProperty('count');
        }

        $brands = $brand->getCollection();

        $brands->setSelect($this->getBrandsSelect($filters));
        return $brands;
    }

    public function getBrand($filters)
    {
        $brand = new Brand();
        $brand->setSelect($this->getBrandsSelect($filters));
        return $brand;
    }

    public function getBrandsSelect($filters = [])
    {
        $filters = array_merge([
            'join'      => []
        ], $filters);

        if($filters['columns']) {
            $columns = $filters['columns'];
        } else {
            $columns = ['id', 'name', 'url'];
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'brands'])
            ->where(['status' => 1]);

        if($filters['catalog'] || $filters['count']) {
            $select->join(['p' => 'products'], 't.id = p.brand_id', []);
        }

        if($filters['catalog']) {
            $select->where(['p.catalog_id' => $filters['catalog']]);
        }

        if (in_array('image', $filters['join'])) {
            $select
                ->join(['bi' => 'brands_images'], 't.id = bi.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left');
        }

        if($filters['count']) {
            $columns['count'] =  new Expression('COUNT(*)');
            $select
                ->group('t.id')
                ->order('count DESC')
                ->where->greaterThanOrEqualTo('count', 1);
        }

        if($filters['url']) {
            $select->where(['url' =>$filters['url']]);
        }

        if($filters['query']) {
            $queries = Search::prepareQuery($filters['query']);
            $where = new Where();
            foreach($queries as $query) {
                $where->or->like('t.name', '%' . $query . '%');
            }
            $select->where($where);
        }

        $select->columns($columns);

        $select->group('t.id');

        return $select;
    }
}