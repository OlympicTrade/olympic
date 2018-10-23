<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use Blog\Model\Article;
use CatalogAdmin\Model\Plugin\ProductImages;
use Zend\Db\Sql\Predicate\Expression as PredicateExpression;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Product extends Entity
{
    const GUNIT_WEIGHT    = 1;
    const GUNIT_PIECES    = 2;
    const GUNIT_TABLETS   = 3;
    const GUNIT_CAPSULES  = 4;
    const GUNIT_NONE      = 5;

    public function __construct()
    {
        $this->setTable('products');

        $this->addProperties(array(
            'parent'            => [],
            'name'              => [],
            'subname'           => [],
            'preview'           => [],
            'url'               => [],
            'units'             => [],
            'ingredients'       => [],
            'catalog_id'        => [],
            'brand_id'          => [],
            'type_id'           => [],
            'text'              => [],
            'discount'          => [],
            'video'             => [],
            'title'             => [],
            'description'       => [],
            'barcode'           => [],

            'price'             => ['virtual' => true],
            'price_old'         => ['virtual' => true],
            'price_base'        => ['virtual' => true],
            'weight'            => ['virtual' => true],
            'stars'             => ['virtual' => true],
            'reviews'           => ['virtual' => true],
            'coefficient'       => ['virtual' => true],
            'count'             => ['virtual' => true],
            'stock'             => ['virtual' => true],
            'event'             => [],
            'time_update'       => [],
            'sort'              => [],
            'popularity'        => [],
        ));

        $this->addPropertyFilterOut('price', function($model) {
            //return $model->get('price_base') * $model->get('coefficient') * (1 - ($model->get('discount') / 100));
            @$price = $model->get('price_base') * $model->get('coefficient');
            //@$price += min(50, $price * 0.05);
            $price -= $price * ($model->get('discount') / 100);

            return round($price / 10) * 10;
        });

        $this->addPropertyFilterOut('price_old', function($model) {
            @$price = $model->get('price_base') * $model->get('coefficient');
            //@$price += min(50, $price * 0.05);
            return ceil($price / 10) * 10;
        });

        $this->addPlugin('articles', function($model) {
            $catalog = Article::getEntityCollection();
            $catalog->select()
                ->join(array('pa' => 'products_articles') , new Expression('pa.article_id = t.id AND pa.depend = ' . $model->getId()), []);

            return $catalog;
        });

        $this->addPlugin('props', function($model, $options = []) {
            $catalogProps = CatalogProps::getEntityCollection();
            $catalogProps->select()
                ->where(['depend' => $model->get('catalog_id')]);

            if(!empty($options['name'])) {
                $catalogProps->select()
                    ->where(['name' => $options['name']]);
            }

            return $catalogProps;
        });

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('products_attrs');

            return $properties;
        });

        $this->addPlugin('recommended', function($model, $options = ['auto' => true]) {
            $products = Product::getEntityCollection();

            $stSelect = $this->getSql()->select()
                ->from(['ps2' => 'products_stock'])
                ->columns(['stock' => new Expression('IF(MAX(ps2.count) >= 1,1,0)')])
                ->where([
                    't.id' => new Expression('ps2.product_id'),
                ]);

            $siSelect = $this->getSql()->select()
                ->from(['ps' => 'products_size'])
                ->columns(['price' => new Expression('MIN(ps.price)')])
                ->where([
                    't.id' => new Expression('ps.depend'),
                ]);

            $rSelect = $this->getSql()->select()
                ->from(['pt' => 'products_taste'])
                ->columns(['coefficient' => new Expression('MIN(pt.coefficient)')])
                ->where([
                    't.id' => new Expression('pt.depend'),
                ]);

            $select = clone $products->select()
                ->columns(['id', 'name', 'brand_id', 'discount', 'url', 'stock' => $stSelect, 'coefficient'  => $rSelect, 'price_base'   => $siSelect])
                ->join(['pb' => 'brands'], 't.brand_id = pb.id', ['brand-name' => 'name', 'brand-url' => 'url', 'brand-id' => 'id'])

                ->join(['prv' => 'products_reviews'],
                    new Expression('t.id = prv.product_id AND prv.status = ' . Reviews::STATUS_VERIFIED), [
                        'stars'   => new Expression('AVG(prv.stars)'),
                    ], 'left')
                ->limit(3)
                ->group('t.id');

            $products->select()
                ->join(['pr' => 'products_recommended'] , new Expression('pr.product_id = t.id AND pr.depend = ' . $model->getId()), [])
                ->order(new Expression('RAND()'));

            if($options['auto'] && !$products->count()) {
                $select
                    ->order('stock DESC')
                    ->order(new Expression('RAND()'))
                    ->where
                        ->notEqualTo('t.id', $model->getId())
                        ->equalTo('catalog_id', $model->get('catalog_id'));

                $products->setSelect($select);
            }

            return $products;
        });

        $this->addPlugin('size', function($model) {
            $props = new Size();
            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('features', function($model) {
            $props = new Entity();
            $props->setTable('products_features');
            $props->addProperties(array(
                'depend'  => [],
                'name'    => [],
            ));

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('taste', function($model, $options) {
            $props = new Taste();

            $sSelect = $model->getSql()->select()
                ->from(array('ps' => 'products_stock'))
                ->columns(array('stock' => new Expression('IF(MAX(ps.count) >= 1,1,0)')))
                ->where(array(
                    new PredicateExpression('t.id = ps.taste_id'),
                    'ps.product_id' => $model->getId(),
                ));

            if($options['size_id']) {
                $sSelect->where(array(
                    'ps.size_id' => $options['size_id']
                ));
            }

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());
            $catalog->select()
                ->columns(array('id', 'name', 'coefficient', 'stock' => $sSelect))
                ->order('stock DESC')
                ->order('coefficient ASC')
                ->group('t.id');

            return $catalog;
        });

        $this->addPlugin('catalog', function($model) {
            $catalog = new Catalog();
            $catalog->setId($model->get('catalog_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('brand', function($model) {
            $brand = new Brand();
            $brand->setId($model->get('brand_id'));
            $brand->select()
                ->join(['c' => 'countries'], 't.country_id = c.id', ['country-id' => 'id', 'country-name' => 'name'], 'left');
            
            return $brand;
        }, array('independent' => true));

        /*$this->addPlugin('reviews', function($model, $options) {
            $reviews = Reviews::getEntityCollection();
            $reviews->select()
                ->where([
                    'product_id' => $model->getId(),
                    'status'     => Reviews::STATUS_VERIFIED,
                ])
                ->order('time_create DESC');

            if(!empty($options['limit'])) {
                $reviews->select()->limit($options['limit']);
            }

            return $reviews;
        });*/

        $this->addPlugin('related', function($product) {
            $products = EntityFactory::collection(new Product());

            $prodId = $product->get('parent') ? $product->get('parent') : $product->getId();

            $adapter = $products->getDbAdapter();
            $sql = new Sql($adapter);

            $select = $sql->select()->from(array('t' => 'products'), array('url', 'color'));
            $select->where
                ->equalTo('id', $prodId)
                ->or
                ->equalTo('parent', $prodId);

            $products->setSelect($select);

            return $products;
        });

        $this->addPlugin('certificate', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_certificates');
            $file->setFolder('products_files');

            return $file;
        });

        $this->addPlugin('instruction', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_instructions');
            $file->setFolder('products_files');

            return $file;
        });

        $this->addPlugin('image', function($model) {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_images');
            $image->setFolder('products');
            $image->addResolutions([
                's' => [
                    'width'  => 260,
                    'height' => 312,
                    'crop'   => true,
                ],
                'm' => [
                    'width'  => 416,
                    'height' => 500,
                    'crop'   => true,
                ],
                'hr' => [
                    'width'  => 1000,
                    'height' => 1000,
                    'crop'   => true,
                ]
            ]);

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new ProductImages();
            $image->setTable('products_gallery');
            $image->setFolder('products_gallery');
            $image->select()->order('sort');
            $image->addResolutions([
                's' => [
                    'width'  => 75,
                    'height' => 90,
                    'crop'   => true,
                ],
                'm' => [
                    'width'  => 500,
                    'height' => 600,
                    'crop'   => true,
                ],
                'hr' => [
                    'width'  => 2083,
                    'height' => 2500,
                    'crop'   => true,
                ]
            ]);

            return $image;
        });
    }

    public function getReviews($options = [])
    {
        $reviews = Reviews::getEntityCollection();
        $reviews->select()
            ->where([
                'product_id' => $this->getId(),
                'status'     => Reviews::STATUS_VERIFIED,
            ])
            ->order('time_create DESC');

        if(!empty($options['limit'])) {
            $reviews->select()->limit($options['limit']);
        }

        return $reviews;
    }

    public function getProps($options = [])
    {
        $catalogProps = CatalogProps::getEntityCollection();
        $catalogProps->select()
            ->where(['depend' => $this->get('catalog_id')]);

        if(!empty($options['name'])) {
            $catalogProps->select()
                ->where(['name' => $options['name']]);
        }

        return $catalogProps;
    }

    public function getCategoryType()
    {
        $cType = new CatalogTypes();
        $cType->setId($this->get('type_id'));

        return $cType;
    }

    public function getFullName()
    {
        return $this->getPlugin('brand')->get('name') . ' ' . $this->get('name');
    }

    public function getUrl()
    {
        $url = '/goods/' . $this->get('url') . '/';

        if($this->get('size_id') && $this->get('taste_id')) {
            $url .= '?variation=' . $this->get('size_id') . '-' . $this->get('taste_id');
        }

        return $url;
    }

    static protected $compareIds = null;
    public function inCompare()
    {
        if(self::$compareIds === null) {
            self::$compareIds = [];
            if($cookie = json_decode($_COOKIE['compare-list'])) {
                foreach ($cookie as $row) {
                    self::$compareIds[] = $row->id;
                }
            }
        }

        return in_array($this->getId(), self::$compareIds);
    }
}