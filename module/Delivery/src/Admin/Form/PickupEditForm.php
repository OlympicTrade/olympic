<?php
namespace DeliveryAdmin\Form;

use ApplicationAdmin\Model\Region;
use Aptero\Form\Form;

use DeliveryAdmin\Model\Pickup;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class PickupEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('delivery_id')->setOptions(array(
            'model' => new Region(),
            'sort'  => 'name',
            'empty' => ''
        ));
    }

    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'latitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'latitude',
            ),
        ));

        $this->add(array(
            'name' => 'longitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'longitude',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Адрес',
            ),
        ));

        $this->add(array(
            'name' => 'route',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Маршрут',
            ),
        ));

        $this->add(array(
            'name' => 'phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон',
            ),
            'attributes' => array(
                'placeholder' => '+7 (___) ___-__-__',
            ),
        ));

        $this->add(array(
            'name' => 'metro',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Метро',
            ),
        ));

        $this->add(array(
            'name' => 'delivery_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Регион',
            ),
        ));

        $this->add(array(
            'name' => 'work_time',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Время работы',
            ),
            'attributes' => array(
                'placeholder' => 'с __:__ до __:__',
            ),
        ));

        $this->add(array(
            'name' => 'weekend',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Выходные',
            ),
            'attributes' => array(
                'placeholder' => 'СБ, ВС',
            ),
        ));

        $this->add(array(
            'name' => 'company',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => Pickup::$companies,
                'label' => 'Компания',
            ),
        ));

        $this->add([
            'name' => 'fitting',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Компания',
            ],
        ]);

        $this->add([
            'name' => 'partial',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Компания',
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}