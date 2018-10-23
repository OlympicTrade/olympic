<?php
namespace BlogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;

class ExercisesController extends AbstractActionController
{
    protected $fields = array(
        'name' => [
            'name'      => 'Название',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'title',
            'width'     => '60',
            'hierarchy' => true,
        ],
        'sort' => [
            'name'      => 'Уровень',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'sort',
            'width'     => '40',
        ],
    );
}