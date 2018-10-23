<?php
namespace WholesaleAdmin\Form;

use WholesaleAdmin\Model\Call;
use WholesaleAdmin\Model\WsClient;
use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class WholesaleFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('list-filter');

        $this->setAttribute('method', 'get');

        $this->add([
            'name' => 'search',
        ]);

        $this->add([
            'name' => 'status',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Тип заявки',
                'options' => ['' => 'Все типы'] + WsClient::$statuses,
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'search',
            'required' => false,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'status',
            'required' => false,
        ]));

        $this->setInputFilter($inputFilter);
    }
}