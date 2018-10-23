<?php
namespace ContactsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class FeedbackController extends AbstractActionController
{
    protected $fields = array(
        'name' => array(
            'name'      => 'ФИО',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'name',
            'width'     => '20',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
        'contact' => array(
            'name'      => 'Контакты',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'contact',
            'width'     => '20',
        ),
        'message' => array(
            'name'      => 'Сообщение',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'text',
            'width'     => '60',
        ),
    );
}