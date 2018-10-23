<?php
namespace Blog\Model;

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

        $this->addPlugin('types', function($model, $options) {
            $types = ExerciseTypes::getEntityCollection();
            $types->setTable('blog_exercises_types');
            $types->select()
                ->join(['tv' => 'blog_exercises_types_vals'], 'tv.type_id = t.id', [])
                ->where(['tv.depend', $model->getId()]);

            if(!empty($options['type_id'])) {
                $types->select()->where(['t.type_id' => $options['type_id']]);
            }

            return $types;
        });

        $this->addPlugin('recommended', function($model) {
            $catalog = Exercise::getEntityCollection();
            $catalog->select()
                ->join(['er' => 'blog_exercises_reco'], 'er.exercise_id = t.id', [])
                ->where(['depend' => $model->getId()]);

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_exercises_image');
            $image->setFolder('exercises');
            $image->addResolutions([
                'm' => [
                    'width'  => 320,
                    'height' => 320,
                    'crop'   => true
                ],
                'hr' => [
                    'width'  => 1000,
                    'height' => 900,
                ],
            ]);

            return $image;
        });

        $this->addPlugin('images', function($model, $options = []) {
            $image = new ExercisesImages();
            $image->setTable('blog_exercises_images');
            $image->setFolder('exercises_gallery');
            $image->addResolutions([
                'p_s' => [
                    'width'  => 77,
                    'height' => 77,
                    'crop'   => true
                ],
                'p_m' => [
                    'width'  => 420,
                    'height' => 280,
                    'crop'   => true
                ],
                'a_m' => [
                    'width'  => 320,
                    'height' => 700,
                ],
            ]);

            $image->select()->order('sort');

            if(!empty($options['sex'])) {
                $image->select()->where(['sex' => $options['sex']]);
            }


            if(!empty($options['type'])) {
                $image->select()->where(['type' => $options['type']]);
            }

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

    public function getUrl($male = true)
    {
        return '/blog/exercises/' . $this->get('url') . '/' . ($male ? '' : 'female/');
    }
}