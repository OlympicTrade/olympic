<?php
namespace BlogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class Blog extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('blog');

        $this->addProperties(array(
            'parent'      => [],
            'name'        => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'text'        => [],
        ));

        $this->addPlugin('types', function($model) {
            $props = new BlogTypes();
            return $props->getCollection()->getPlugin()->setParentId($model->getId());
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_images');
            $image->setFolder('blog');
            $image->addResolutions([
                'a' => [
                    'width'   => 120,
                    'height'  => 120,
                    'opacity' => true,
                ],
                'hr' => [
                    'width'   => 1000,
                    'height'  => 800,
                    'opacity' => true,
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