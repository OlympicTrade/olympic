<?php
namespace WikiAdmin\Model;

use Aptero\Db\Entity\Entity;

class Calc extends Entity
{
    public function __construct()
    {
        $this->setTable('wiki_calc');

        $this->addProperties([
            'pregnant'   => [],
            'age_from'   => [],
            'age_to'     => [],
        ]);

        $this->addPlugin('elements', function($model) {
            $item = new Entity();
            $item->setTable('wiki_calc_elements');
            $item->addProperties([
                'depend'     => [],
                'male'       => [],
                'female'     => [],
                'element_id' => [],
                'units'      => [],
            ]);
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });
    }
}