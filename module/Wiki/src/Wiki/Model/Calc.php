<?php
namespace Wiki\Model;

use Aptero\Db\Entity\Entity;
use Zend\Db\Sql\Expression;

class Calc extends Entity
{
    public function __construct()
    {
        $this->setTable('wiki_calc');

        $this->addProperties([
            'gender'     => [],
            'pregnant'   => [],
            'age_from'   => [],
            'age_to'     => [],
        ]);

        $this->addPlugin('elements', function($model, $options) {
            $item = new Element();
            $item->addProperty('amount');
            $items = $item->getCollection();
            $items->select()
                ->join(['wce' => 'wiki_calc_elements'], 'wce.element_id = t.id', [])
                ->where(['wce.depend' => $model->getId()])
                ->order('type');

            return $items;
        });
    }
}