<?php
namespace DeliveryAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use DeliveryAdmin\Model\Pickup;

class PickupController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields([
            'address' => [
                'name'      => 'Адрес',
                'type'      => TableService::FIELD_TYPE_LINK,
                'width'     => '60',
            ],
            'company' => [
                'name'      => 'Компания',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'filter'    => function($value, $row){
                    return Pickup::$companies[$value];
                },
                'width'     => '20',
            ],
            'phone' => [
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'width'     => '20',
            ],
        ]);
    }
}