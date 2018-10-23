<?php
namespace ApplicationAdmin\Model;

use ApplicationAdmin\Model\Plugin\ContentImages;
use Aptero\Db\Entity\Entity;

class Content extends Entity
{

    public function __construct()
    {
        $this->setTable('content');

        $this->addProperties([
            'depend'   => [],
            'module'   => [],
            'text'     => [],
            'sort'     => [],
        ]);

        $this->addPlugin('images', function() {
            $image = new ContentImages();
            $image->setTable('content_gallery');
            $image->setFolder('content');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 200,
                    'height' => 160,
                    'crop'   => true
                ),
                'hr' => array(
                    'width'  => 1920,
                    'height' => 1150,
                )
            ));

            return $image;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if($model->get('sort')) {
                return true;
            }

            $content = new Content();
            $content->select()->where(array(
                'depend'    => $model->get('depend'),
                'module'    => $model->get('module'),
            ))->order('sort DESC');

            if($content->load()) {
                $model->set('sort', $content->get('sort') + 5);
            } else {
                $model->set('sort', 5);
            }

            return true;
        });
    }
}