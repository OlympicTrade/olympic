<?php
namespace BlogAdmin\Form;

use Aptero\Form\Form;

use BlogAdmin\Model\Exercise;
use BlogAdmin\Model\ExerciseTypes;
use CatalogAdmin\Model\Reviews;
use Zend\Db\Sql\Expression;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ExercisesEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('images-images')->setOptions([
            'model'   => $model->getPlugin('images'),
        ]);

        $this->get('image-image')->setOptions([
            'model' => $model->getPlugin('image'),
        ]);

        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));
        $recommendedModel = $model->getPlugin('recommended');
        $this->get('recommended-collection')->setOption('model', $recommendedModel);

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
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\ExercisesImages',
            'options' => [],
        ]);

        $types = ExerciseTypes::getEntityCollection();
        $sqlReplace = '';
        foreach (ExerciseTypes::$types as $key => $val) {
            $sqlReplace = 'REPLACE(' . ($sqlReplace ? $sqlReplace : 't.type_id') . ', "' . $key . '", "' . $val . '")';
        }

        $types->select()
            ->columns(['id', 'name' => new Expression('CONCAT(' . $sqlReplace . ', " - ", t.name)')])
            ->order('t.type_id, t.name');

        $this->add([
            'name' => 'types-collection',
            'type' => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options' => [
                    'type_id' => [
                        'label'   => 'Тип',
                        'width'   => 150,
                        'sort'    => 'name',
                        'options' => $types
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => [],
        ]);

        $this->add([
            'name' => 'recommended-collection',
            'type' => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options' => [
                    'exercise_id' => [
                        'label'   => 'Связанные статьи',
                        'width'   => 150,
                        'sort'    => 'name',
                        'options' => new Exercise()
                    ],
                ]
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
            'name' => 'level',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Уровень',
                'options' => Exercise::$levels
            ],
        ]);

        $this->add([
            'name' => 'rating',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Рейтинг (от 1 до 10)',
            ],
        ]);

        $this->add([
            'name' => 'title_male',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Title',
            ],
        ]);

        $this->add([
            'name' => 'title_female',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Title',
            ],
        ]);

        $this->add([
            'name' => 'description_male',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Description',
            ],
        ]);

        $this->add([
            'name' => 'description_female',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Description',
            ],
        ]);

        $this->add([
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=> [
                'class' => 'editor',
                'id'    => 'page-text'
            ],
        ]);

        $this->add([
            'name' => 'video_male',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Видео М',
            ],
        ]);

        $this->add([
            'name' => 'video_female',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Видео Ж',
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