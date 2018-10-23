<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Catalog\Form\OrderForm;
use Catalog\Form\ProductRequestForm;
use Catalog\Model\Order;

use User\Service\AuthService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class MobileOrdersController extends AbstractMobileActionController
{
    public function cartAction()
    {
        $this->generate('/catalog/cart/');

        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest() && AuthService::hasUser()) {
            $this->redirect()->toUrl('/user/');
        }

        $cartService = $this->getCartService();
        $cart  = $cartService->getCookieCart();
        $price = $cartService->getCartPrice($cart);

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'cart'   => $cart,
            'price'  => $price,
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'header'       => $this->layout()->getVariable('header'),
        ));

        return $viewModel;
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\OrdersService');
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\ProductsService');
    }
}