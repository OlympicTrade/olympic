<?php
namespace WikiAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;

class CalcController extends AbstractActionController
{
    protected $fields = [
        'age_from' => [
            'name'      => 'Возраст с',
            'type'      => TableService::FIELD_TYPE_EMAIL,
            'field'     => 'age_from',
            'width'     => '14',
        ],
        'age_to' => [
            'name'      => 'по',
            'type'      => TableService::FIELD_TYPE_EMAIL,
            'field'     => 'age_to',
            'width'     => 'xd',
        ],
    ];
}