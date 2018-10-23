<?php
namespace Wholesale\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class WholesaleController extends AbstractActionController
{
    public function priceAction()
    {
        $this->getWholesaleService()->productsExcel();
        die();
    }

    /**
     * @return \Wholesale\Service\WholesaleService
     */
    public function getWholesaleService()
    {
        return $this->getServiceLocator()->get('Wholesale\Service\WholesaleService');
    }
}