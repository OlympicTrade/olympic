<?php
namespace CatalogAdmin\Controller;

use ApplicationAdmin\Model\Content;
use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class CatalogController extends AbstractActionController
{
    protected $fields = array(
        'name' => array(
            'name'      => 'Название',
            'type'      => TableService::FIELD_TYPE_EMAIL,
            'field'     => 'name',
            'width'     => '100',
            'hierarchy' => true,
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
    );

    public function editAction()
    {
        $view = parent::editAction();
        $id = $this->params()->fromQuery('id');

        $model = $view->getVariable('model');
        if(!$model) {
            $model = $this->getService()->getModel();
            $model->setId($id);
        }

        $this->viewHelper('headScript')->appendFile('/engine/js/page-list.js');

        $contentItemsFields = array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'title',
                'width'     => '60',
                'hierarchy' => true,
            ),
            'sort' => array(
                'name'      => 'Сортировка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'sort',
                'width'     => '40',
            ),
        );

        if($model->getId()) {
            $contentItems = Content::getEntityCollection();
            $contentItems->select()
                ->where(array('depend' => $model->getId()))
                ->order('t.sort');

            $view->setVariables(array(
                'contentItems'       => $contentItems,
                'contentItemsFields' => $contentItemsFields,
            ));
        }

        return $view;
    }
}