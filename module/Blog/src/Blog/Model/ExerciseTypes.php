<?php
namespace Blog\Model;

use Aptero\Db\Entity\Entity;

class ExerciseTypes extends Entity
{
    const TYPE_MUSCLES   = 1;
    const TYPE_TYPE      = 2;
    const TYPE_INVENTORY = 3;
    const TYPE_MECHANICS = 4;

    static public $types = [
        self::TYPE_MUSCLES       => 'Мышцы',
        self::TYPE_TYPE          => 'Тип',
        self::TYPE_INVENTORY     => 'Инвентарь',
        self::TYPE_MECHANICS     => 'Механика',
    ];

    public function __construct()
    {
        $this->setTable('blog_exercises_types');

        $this->addProperties([
            'type_id'      => [],
            'name'         => [],
            'url'          => [],
            'header'       => [],
            'title'        => [],
            'description'  => [],
            'sort'         => [],
        ]);

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }

    public function getUrl()
    {
        return '/blog/exercises/' . $this->get('url') . '/';
    }
}