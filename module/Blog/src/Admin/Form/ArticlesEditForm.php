<?php
namespace BlogAdmin\Form;

use Aptero\Form\Form;

use BlogAdmin\Model\BlogTypes;
use Zend\Db\Sql\Expression;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ArticlesEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('blog_id')->setOption('model', $model->getPlugin('blog'));
        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
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

        $types = BlogTypes::getEntityCollection();
        $types->select()
            ->columns(['id', 'name' => new Expression('CONCAT(b.name, " - ", t.name)')])
            ->join(['b' => 'blog'], 't.depend = b.id', [])
            ->order('b.name, t.name');

        $this->add([
            'name' => 'types-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'type_id' => [
                        'label'   => 'Тип',
                        'width'   => 200,
                        'options' => $types
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'blog_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Блог'
            ],
        ]);

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'hits',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Посещения',
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Url',
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Title',
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Description',
            ),
        ));

        $this->add(array(
            'name' => 'keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Keywords',
            ),
        ));

        $this->add(array(
            'name' => 'tags',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Теги (через запятую)',
            ),
        ));

        $this->add(array(
            'name' => 'time_create',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата',
            ),
        ));

        $this->add(array(
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));

        $this->add(array(
            'name' => 'preview',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание',
            ),
        ));

        $this->add(array(
            'name' => 'links',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
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