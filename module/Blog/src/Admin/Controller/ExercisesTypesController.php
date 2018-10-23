<?php
namespace BlogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use BlogAdmin\Model\ExerciseTypes;

class ExercisesTypesController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields([
            'type_id' => [
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'type_id',
                'filter'    => function($value, $row){
                    return ExerciseTypes::$types[$value];
                },
                'width'     => '14',
            ],
            'name' => [
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '26',
            ],
            'sort' => [
                'name'      => 'Сортировка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'sort',
                'width'     => '60',
            ],
        ]);
    }
}