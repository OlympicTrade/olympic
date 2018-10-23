<?php
namespace WikiAdmin\Form;

use Aptero\Form\Admin\Form;

use WikiAdmin\Model\Element;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CalcEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
        
        $this->get('elements-collection')->setOption('model', $model->getPlugin('elements'));
    }

    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'age_from',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Возраст с',
            ],
        ]);

        $this->add([
            'name' => 'age_to',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Возраст по',
            ],
        ]);

        $this->add([
            'name' => 'elements-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'element_id' => [
                        'label'   => 'Елемент',
                        'width'   => 150,
                        'options' => new Element()
                    ],
                    'male' => [
                        'label'   => 'Мужчины',
                        'width'   => 150,
                    ],
                    'female' => [
                        'label'   => 'Женщины',
                        'width'   => 150,
                    ],
                    'units' => [
                        'label'   => 'Единицы',
                        'width'   => 80,
                        'options' => [
                            ''     => '',
                            'кг'   => 'кг',
                            'г'    => 'г',
                            'мг'   => 'мг',
                            'мкг'  => 'мкг',
                            'кал'  => 'кал',
                            'ккал' => 'ккал',
                        ]
                    ],
                ]
            ],
        ]);

        $this->addMeta();
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'age_from',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}