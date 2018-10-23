<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use CatalogAdmin\Model\Plugin\ProductImages;
use ManagerAdmin\Model\Task;

class Products extends Entity
{
    const GUNIT_WEIGHT    = 1;
    const GUNIT_PIECES    = 2;
    const GUNIT_TABLETS   = 3;
    const GUNIT_CAPSULES  = 4;
    const GUNIT_NONE      = 5;
    
    static public $gUnits = [
        self::GUNIT_WEIGHT   => 'Россыпь',
        self::GUNIT_PIECES   => 'Штуки',
        self::GUNIT_TABLETS  => 'Таблетки',
        self::GUNIT_CAPSULES => 'Капсулы',
        self::GUNIT_NONE     => 'Без деления',
    ];

    public function __construct()
    {
        $this->setTable('products');

        $this->addProperties([
            'parent'      => [],
            'name'        => [],
            'subname'     => [],
            'preview'     => [],
            'url'         => [],
            'catalog_id'  => [],
            'brand_id'    => [],
            'type_id'     => [],
            'text'        => [],
            'discount'    => [],
            'units'       => [],
            'title'       => [],
            'description' => [],
            'barcode'     => [],

            'color'       => [],
            'price'       => [],
            'price_opt'   => [],
            'vendor'      => [],
            'count'       => [],
            'event'       => [],
            'visible'     => [],
            'time_update' => [],
            'sort'        => [],
            'popularity'  => [],
            'sync_id'     => [],

            'price_base'  => ['virtual' => true],
            'coefficient' => ['virtual' => true],
        ]);

        $this->addPropertyFilterOut('price', function($model) {
            @$price = (int) $model->get('price_base') * (int) $model->get('coefficient');
            @$price -= $price * ($model->get('discount') / 100);

            return ceil($price / 10) * 10;
        });

        $this->addPlugin('recommended', function($model) {
            $item = new Entity();
            $item->setTable('products_recommended');
            $item->addProperties(array(
                'depend'      => [],
                'product_id' => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('composition', function($model) {
            $item = new Entity();
            $item->setTable('products_composition');
            $item->addProperties(array(
                'depend'    => [],
                'key'       => [],
                'val'       => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('tags', function($model) {
            $item = new Entity();
            $item->setTable('products_tags');
            $item->addProperties(array(
                'depend'    => [],
                'name'      => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('types', function($model) {
            $item = new Entity();
            $item->setTable('products_types');
            $item->addProperties(array(
                'depend'    => [],
                'type_id'   => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('articles', function($model) {
            $item = new Entity();
            $item->setTable('products_articles');
            $item->addProperties([
                'depend'      => [],
                'article_id' => [],
            ]);
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('size', function($model) {
            $props = new Size();

            $catalog = $props->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('taste', function($model) {
            $props = new Taste();

            $catalog = $props->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('props', function($model) {
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

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('products_attrs');

            return $properties;
        });

        $this->addPlugin('catalog', function($model) {
            $catalog = new \CatalogAdmin\Model\Catalog();
            $catalog->setId($model->get('catalog_id'));

            return $catalog;
        }, ['independent' => true]);

        $this->addPlugin('type', function($model) {
            $catalog = new \CatalogAdmin\Model\CatalogTypes();
            $catalog->setId($model->get('type_id'));
            $catalog->select()
                ->where(['depend' => $model->get('catalog_id')]);

            return $catalog;
        }, ['independent' => true]);

        $this->addPlugin('brand', function($model) {
            $catalog = new \CatalogAdmin\Model\Brands();
            $catalog->setId($model->get('brand_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_images');
            $image->setFolder('products');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new ProductImages();
            $image->setTable('products_gallery');
            $image->setFolder('products_gallery');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            $image->select()->order('sort');

            return $image;
        });

        $this->addPlugin('certificate', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_certificates');
            $file->setFolder('products_files');

            return $file;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->getPlugin('brand')->get('name') . ' ' . $model->get('name')));
            }

            return true;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_POST_INSERT), function ($event) {
            $model = $event->getTarget();

            (new Task())->setVariables([
                'task_id'       => Task::TYPE_PRODUCT_NEW,
                'item_id'       => $model->getId(),
                'name'          => 'Добавление товара',
                'duration'      => 20,
            ])->save();

            return true;
        });

        /*$this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->getPlugin('brand')->get('name') . ' ' . $model->get('name')));
            }

            return true;
        });*/

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_DELETE), function ($event) {
            $model = $event->getTarget();

            $stock = new Stock();
            $stock->select()->where(array('product_id' => $model->getId()));
            $stock->remove();

            return true;
        });
    }
}