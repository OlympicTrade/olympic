<?php
namespace Sync\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Catalog\Model\Product;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

class SyncController extends AbstractActionController
{
    public function stockAction()
    {
        $type = $this->params()->fromRoute('type');

        switch ($type) {
            /*case 'full':
                $data = $this->getSyncService()->fullSync();
                break;
            case 'changes':
                $data = $this->getSyncService()->syncChanges();
                break;
            case 'erase':
                $data = $this->getSyncService()->eraseChanges();
                break;
            case 'data':
                $data = $this->getSyncService()->getChanges();
                break;*/
            case 'sync-product':
                $id = $this->params()->fromQuery('id');
                $data = $this->getSyncService()->syncStock($id);
                break;
            default:
                return $this->send404();
        }

        return new JsonModel($data);
    }

    public function tasksAction()
    {
        return $this->send404();
    }

    /**
     * @return \Sync\Service\SyncService
     */
    public function getSyncService()
    {
        return $this->getServiceLocator()->get('Sync\Service\SyncService');
    }
}