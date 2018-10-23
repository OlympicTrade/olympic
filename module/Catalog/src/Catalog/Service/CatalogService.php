<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Aptero\String\Numbers;
use Aptero\String\Search;
use Aptero\String\Translit;
use Catalog\Model\Brand;
use Catalog\Model\Catalog;
use Catalog\Model\CatalogTypes;
use Catalog\Model\Product;
use Catalog\Model\Products;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class CatalogService extends AbstractService
{
    public function getCatalogIds(Catalog $category)
    {
        $ids = array($category->getId());

        $children = $category->getChildren();

        if($children->count()) {
            foreach($children as $child) {
                $ids = array_merge($ids, $this->getCatalogIds($child));
            }
        }

        return $ids;
    }
    
    public function getTypeByUrl($categoryId, $subUrlTag)
    {
        $type = new CatalogTypes();
        $type->select()
            ->where([
                'depend' => $categoryId,
                'url'    => $subUrlTag,
            ]);
        return $type->load();
    }

    public function getAutoComplete($query)
    {
        $result = [];
        $maxRows = 4;

        //Каталог
        $catalog = $this->getCatalog(['query' => $query]);
        if(count($catalog)) {
            $i = 0;
            foreach ($catalog as $category) {
                $i++;
                if($i > 6) {
                    break;
                }
                $result[] = [
                    'label' => $category['name'],
                    'url' => $category->getUrl(),
                    'type' => 'category',
                    'hide'  => ($i > $maxRows ? true : false)
                ];
            }

            $result[] = ['type' => 'hr'];
        }

        //Типы
        $catalogTypes = CatalogTypes::getEntityCollection();
        $queries = Search::prepareQuery($query);
        $where = '';
        foreach($queries as $tQuery) {
            $where .= ($where ? ') OR (' : '((') . 't.name LIKE "' . ('%' . $tQuery . '%') . '"';
        }
        $where .= '))';
        $catalogTypes->select()
            ->columns(['id', 'name', 'url', 'depend'])
            ->join(['c' => 'catalog'], 'c.id = t.depend', ['catalog-id' => 'id', 'catalog-url_path' => 'url_path'])
            ->order('t.sort')
            ->where($where);

        if(count($catalogTypes)) {
            $i = 0;
            foreach ($catalogTypes as $type) {
                $i++;
                if($i > 6) {
                    break;
                }
                $result[] = [
                    'label' => $type['name'],
                    'url'   => $type->getUrl(),
                    'type'  => 'category',
                    'hide'  => ($i > $maxRows ? true : false)
                ];
            }

            $result[] =['type' => 'hr'];
        }

        //Товары
        $products = $this->getServiceManager()->get('Catalog\Service\ProductsService')->getProducts(
            [
                'limit' => 4,
                'query' => $query,
                'join'  => ['reviews'],
                'minPrice'  => true
            ],
           ['reviews', 'stars']);

        if(count($products)) {
            //$result[] = array('label' => 'Товары', 'type' => 'title');
            foreach($products as $product) {
                $result[] = [
                    'type'     => 'product',
                    'label'    => $product->get('name'),
                    'id'       => $product->get('id'),
                    'url'      => $product->getUrl(),
                    'price'    => $product->get('price'),
                    'stars'    => $product->get('stars'),
                    'reviews'  => ($product->get('reviews') ? $product->get('reviews') . ' ' . Numbers::declension($product->get('reviews'), array('отзыв', 'отзыва', 'отзывов')) : 'Нет отзывов'),
                    'img'      => $product->getPlugin('image')->getImage('s'),
                ];
            }
            $result[] = ['type' => 'clear'];
        }

        /*if($result) {
            $result[] = array('type' => 'show-all');
        }*/

        return $result;
    }

    public function getCategoryCrumbs($category)
    {
        $crumbs = array();
        $parent = $category;

        do {
            $crumbs[] = array(
                'name'  => $parent->get('name'),
                'url'   => $parent->getUrl(),
            );
        } while ($parent = $parent->getParent());

        return array_reverse($crumbs);
    }

    public function getCategoryByName($categoryName)
    {
        $category = new Catalog();
        $category->select()
            ->columns(array('id', 'name', 'url_path'))
            ->where(array('name' => $categoryName));

        return $category;
    }

    public function getCatalog($filter = [])
    {
        $catalog = Catalog::getEntityCollection();
        $catalog->setSelect($this->getCatalogSelect($filter));

        return $catalog;
    }

    public function getCategory($filter = [])
    {
        $catalog = new Catalog();
        $catalog->setSelect($this->getCatalogSelect($filter));

        return $catalog;
    }

    public function getCatalogSelect($filter = [])
    {
        $select = $this->getSql()->select()
            ->from(['t' => 'catalog'])
            ->columns(['id', 'title', 'description', 'parent', 'name', 'short_name', 'url_path', 'text']);

        $select->where(['active' => 1]);

        /*if($filter['url']) {
            $select->select($filter['url']);
        }

        if($filter['id']) {
            $select->setParentId($filter['id']);
        }*/

        if(isset($filter['join'])) {
            if (in_array('image', $filter['join'])) {
                $select
                    ->join(['ci' => 'catalog_images'], 't.id = ci.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left');
            }
        }

        if($filter['url']) {
            $select->where(['url_path' => $filter['url']]);
        }

        if(!empty($filter['limit'])) {
            $select->limit($filter['limit']);
        }

        if($filter['query']) {
            $queries = Search::prepareQuery($filter['query']);

            $where = '';
            foreach($queries as $query) {
                $where .= ($where ? ') OR (' : '((') . 't.name LIKE "' . ('%' . $query . '%') . '"';
            }
            $where .= '))';
            $select->where($where);
        }

        return $select;
    }
}