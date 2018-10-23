<?php
namespace ApplicationAdmin\Service;

use ApplicationAdmin\Model\MenuItems;
use Aptero\Service\Admin\TableService;

class ContentService extends TableService
{
    public function save($formData, $queryData, $model)
    {
        $model->unserializeArray($formData);

        if(isset($queryData['parent'])) {
            $model->set('depend', (int) $queryData['parent']);
        }

        if(isset($queryData['module'])) {
            $model->set('module', $queryData['module']);
        }

        $model->save();

        return true;
    }
}