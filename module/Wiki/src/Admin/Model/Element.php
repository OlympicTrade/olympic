<?php
namespace WikiAdmin\Model;

use Aptero\Db\Entity\Entity;

class Element extends Entity
{
    const ELEMENT_BASE      = 0;
    const ELEMENT_PROTEIN   = 1;
    const ELEMENT_FAT       = 2;
    const ELEMENT_CARBO     = 3;
    const ELEMENT_VITAMINS  = 4;
    const ELEMENT_MINERALS  = 5;

    static public $elementsNames = [
        self::ELEMENT_BASE      => 'База',
        self::ELEMENT_PROTEIN   => 'Белки',
        self::ELEMENT_FAT       => 'Жиры',
        self::ELEMENT_CARBO     => 'Углеводы',
        self::ELEMENT_VITAMINS  => 'Витамины',
        self::ELEMENT_MINERALS  => 'Минералы',
    ];

    public function __construct()
    {
        $this->setTable('wiki_elements');

        $this->addProperties([
            'name'        => [],
            'name_short'  => [],
            'url'         => [],
            'type'        => [],
            'text'        => [],
            'title'       => [],
            'description' => [],
        ]);

        $this->getEventManager()->attach([Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE], function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }
}