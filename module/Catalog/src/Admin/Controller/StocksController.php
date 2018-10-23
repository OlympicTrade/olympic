<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Zend\View\Model\JsonModel;

class StocksController extends AbstractActionController
{
    public function indexAction() {
        $this->generate();

        $diff = $this->getServiceLocator()->get('CatalogAdmin\Service\StocksService')->findDifference();

        return ['diffs' => $diff];
    }

    public function syncStockAction() {
        $id = $this->params()->fromQuery('id');
        $result = $this->getServiceLocator()->get('CatalogAdmin\Service\StocksService')->syncStock($id);

        var_dump($result);die();

        return new JsonModel(['result' => $result]);
    }
}