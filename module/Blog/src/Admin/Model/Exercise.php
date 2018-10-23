<?php
namespace BlogAdmin\Model;

use Aptero\Db\Entity\Entity;
use BlogAdmin\Model\Plugin\ExercisesImages;

class Exercise extends Entity
{
    const LEVEL_BEGINNER     = 1;
    const LEVEL_INTERMEDIATE = 2;
    const LEVEL_ADVANCED     = 3;

    static public $levels = [
        self::LEVEL_BEGINNER     => 'Новичек',
        self::LEVEL_INTERMEDIATE => 'Средний',
        self::LEVEL_ADVANCED     => 'Продвинутый',
    ];

    public function __construct()
    {
        $this->setTable('blog_exercises');

        $this->addProperties(array(
            'name'         => [],
            'url'          => [],
            'level'        => [],
            'rating'       => [],
            'text'         => [],
            'video_male'   => [],
            'video_female' => [],
            'title_male'   => [],
            'title_female' => [],
            'description_male'    => [],
            'description_female'  => [],
        ));

        $this->addPlugin('types', function($model) {
            $props = new Entity();
            $props->setTable('blog_exercises_types_vals');
            $props->addProperties([
                'depend'     => [],
                'type_id'    => [],
            ]);

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('recommended', function($model) {
            $props = new Entity();
            $props->setTable('blog_exercises_reco');
            $props->addProperties([
                'depend'      => [],
                'exercise_id' => [],
            ]);

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_exercises_image');
            $image->setFolder('exercises');
            $image->addResolutions([
                'a' => [
                    'width'  => 120,
                    'height' => 120,
                    'crop'   => true
                ],
                'hr' => [
                    'width'   => 1000,
                    'height'  => 900,
                    'crop'   => true
                ],
            ]);

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new ExercisesImages();
            $image->setTable('blog_exercises_images');
            $image->setFolder('exercises_gallery');
            $image->addResolutions([
                'a' => [
                    'width'  => 162,
                    'height' => 162,
                ],
                'hr' => [
                    'width'  => 1000,
                    'height' => 800,
                ]
            ]);

            $image->select()->order('sort');

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