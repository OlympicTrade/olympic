<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CatalogEditForm extends Form
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

        $this->get('props-collection')->setOption('model', $model->getPlugin('props'));
        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));
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
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Название',
            ],
        ]);


        $this->add([
            'name' => 'short_name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Короткое название',
            ],
        ]);

        $this->add([
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Url',
                'help'  => 'Используется как ЧПУ. По умолчанию заполняется транслитом на основании названия страницы'
            ],
        ]);

        $this->add([
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Родительский каталог',
            ],
        ]);

        $this->add([
            'name' => 'header',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Заголовок страницы (H1)'
            ],
        ]);

        $this->add([
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Заголовок (Title)'
            ],
        ]);

        $this->add([
            'name' => 'keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Ключевые слова (Keywords)'
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Описание (Description)'
            ],
        ]);

        $this->add([
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => [],
        ]);

        $this->add([
            'name' => 'props-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'name'     => ['label' => 'Название', 'width' => 150],
                    'sort'     => ['label' => 'Порядок', 'width' => 30],
                ]
            ],
        ]);

        $this->add([
            'name' => 'types-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'  => [
                    'name'        => ['label' => 'Заголовок', 'width' => 150],
                    'short_name'  => ['label' => 'Название', 'width' => 100],
                    'ya_cat_name' => ['label' => 'Янд. маркет', 'width' => 100],
                    'url'         => ['label' => 'Url', 'width' => 100],
                    'title'       => ['label' => 'Title', 'width' => 300],
                    'description' => ['label' => 'Description', 'width' => 300],
                    'sort'        => ['label' => 'Порядок', 'width' => 30],
                ]
            ],
        ]);

        $this->add([
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>[
                'class' => 'editor',
                'id'    => 'page-text'
            ],
        ]);

        $this->add([
            'name' => 'active',
            'type' => 'Zend\Form\Element\Radio',
            'options' => [
                'options' => [
                    1 => 'Показать',
                    0 => 'Скрыть',
                ],
                'label' => 'Показать на сайте',
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
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 0,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name'    => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z1-9_-]*$/',
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}