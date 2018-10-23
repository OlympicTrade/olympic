<?php
namespace WholesaleAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use WholesaleAdmin\Model\Call;
use WholesaleAdmin\Model\WsClient;
use CatalogAdmin\Model\Requests;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use Zend\View\Model\JsonModel;

class WholesaleController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = [
            0  => 'blue',
            1  => 'green',
            2  => 'red',
        ];

        $this->setFields([
            'name' => [
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '12',
            ],
            'city' => [
                'name'      => 'Город',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'city',
                'width'     => '10',
            ],
            'phone' => [
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_TEXTAREA,
                'field'     => 'phones',
                'width'     => '14',
            ],
            'site' => [
                'name'      => 'Сайт',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'site',
                'width'     => '12',
                'filter'    => function($value, $row, $view){
                    return '<a href="' . $value . '" target="_blank">' . $value . '</a>';
                },
            ],
            'comments' => [
                'name'      => 'Комментарий',
                'type'      => TableService::FIELD_TYPE_TEXTAREA,
                'field'     => 'comments',
                'width'     => '40',
            ],
            'status' => [
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_SELECT,
                'field'     => 'status',
                'options'     => WsClient::$statuses,
                'width'     => '12',
            ],
        ]);
    }

    public function loadAction()
    {
        $this->getService()->load2Gis();
        die('SUCCESS');
    }
}