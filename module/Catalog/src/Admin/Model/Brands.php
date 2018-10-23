<?php
namespace CatalogAdmin\Model;

use ApplicationAdmin\Model\Country;
use Aptero\Db\Entity\Entity;

class Brands extends Entity
{
    public function __construct()
    {
        $this->setTable('brands');

        $this->addProperties([
            'country_id'  => [],
            'name'        => [],
            'url'         => [],
            'text'        => [],
            'html'        => [],
            'title'       => [],
            'description' => [],
        ]);

        $this->addPlugin('country', function($model) {
            $catalog = new Country();
            $catalog->setId($model->get('country_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('brands_images');
            $image->setFolder('brands');
            $image->addResolutions([
                'a' =>[
                    'width'  => 162,
                    'height' => 162,
                ],
                'hr' => [
                    'width'  => 1000,
                    'height' => 800,
                ]
            ]);

            return $image;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }
}