<?php
namespace ApplicationAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use ApplicationAdmin\Model\Menu;

class ContentEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('images-images')->setOptions([
            'model'   => $model->getPlugin('images'),
            'content' => $model,
        ]);

        if(!$model->get('depend')) {
            $this->get('depend')->setValue((int) $_GET['parent']);
        }

        $this->get('depend')->setValue(12);
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
            'name' => 'module',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'depend',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\ContentImages',
            'options' => [],
        ]);

        $this->add([
            'name' => 'sort',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label'  => 'Сортировка',
            ]
        ]);

        $this->add([
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes' => [
                'class' => 'editor',
                'id'    => 'page-text'
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        /*$inputFilter->add($factory->createInput(array(
            'name'     => 'title',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));*/

        $this->setInputFilter($inputFilter);
    }
}