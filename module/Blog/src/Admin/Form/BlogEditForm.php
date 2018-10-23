<?php
namespace BlogAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class BlogEditForm extends Form
{

    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('parent')->setOptions(array(
            'model' => $this->getModel(),
            'sort'  => 'name',
            'empty' => ''
        ));

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));

        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));
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
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Родительский каталог',
            ],
        ]);

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название',
            ),
        ));

        $this->add([
            'name' => 'types-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'  => [
                    'name'        => ['label' => 'Заголовок', 'width' => 150],
                    'short_name'  => ['label' => 'Краткое название', 'width' => 150],
                    'url'         => ['label' => 'Url', 'width' => 100],
                    'title'       => ['label' => 'Title', 'width' => 300],
                    'description' => ['label' => 'Description', 'width' => 300],
                    'sort'        => ['label' => 'Порядок', 'width' => 30],
                ]
            ],
        ]);

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Url',
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Title',
            ),
        ));

        $this->add([
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => [],
        ]);

        $this->add(array(
            'name' => 'active',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [
                    1 => 'Да',
                    0 => 'Нет',
                ],
                'label'   => 'Показать',
            ],
        ));

        $this->add(array(
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Description',
            ),
        ));
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