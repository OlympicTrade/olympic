<?php
namespace MetricsAdmin\Form;

use Aptero\Form\Form;

use BalanceAdmin\Model\BalanceFlow;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CashEditForm extends Form
{
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
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => BalanceFlow::$flowTypes,
                'label' => 'Тип',
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'price',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Доход/Расход',
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Комментарий',
            ),
        ));

        $this->add(array(
            'name' => 'date',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата',
            ),
            'attributes' => array(
                'class' => 'datepicker'
            )
        ));

        /*$this->add(array(
            'name' => 'select',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    1 => 'Пункт 1',
                    2 => 'Пункт 2',
                    3 => 'Пункт 3'
                ),
                'label' => 'Селект',
            ),
        ));*/
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}