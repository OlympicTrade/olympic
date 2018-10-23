<?php
namespace CallcenterAdmin\Form;

use Aptero\Form\Form;

use CallcenterAdmin\Model\Call;
use CallcenterAdmin\Model\WsClient;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class WholesaleEditForm extends Form
{
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
            'name' => 'phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Телефоны',
            ],
        ]);

        $this->add([
            'name' => 'email',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'E-mail',
            ],
        ]);

        $this->add([
            'name' => 'site',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Сайт',
            ],
        ]);

        $this->add([
            'name' => 'city',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Город',
            ],
        ]);

        $this->add([
            'name' => 'address',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Адрес',
            ],
        ]);

        $this->add([
            'name' => 'route',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Маршрут',
            ],
        ]);

        $this->add([
            'name' => 'latitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'options' => [
                'label' => 'Название',
            ],
        ]);

        $this->add([
            'name' => 'longitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'options' => [
                'label' => 'Название',
            ],
        ]);

        $this->add([
            'name' => 'comments',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => [
                'label' => 'Комментарии',
            ],
        ]);

        $this->add([
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => WsClient::$statuses,
                'label' => 'Статус',
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