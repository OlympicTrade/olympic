<?php
namespace BlogAdmin\Form;

use Aptero\Form\Form;

use BlogAdmin\Model\ExerciseTypes;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ExercisesTypesEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
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

        $this->add([
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Название',
            ],
        ]);

        $this->add([
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Url',
            ],
        ]);

        $this->add([
            'name' => 'header',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Заголовок H1',
            ],
        ]);

        $this->add([
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Title',
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Description',
            ],
        ]);

        $this->add([
            'name' => 'sort',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Сортировка',
            ],
        ]);

        $this->add([
            'name' => 'type_id',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Тип',
                'options' => ExerciseTypes::$types
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}