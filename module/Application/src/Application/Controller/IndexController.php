<?php
namespace Application\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function redirectAction()
    {
        $code =  $this->params()->fromQuery('code');

        $session = new Container();
        if(!$session->redirect) {
            return $this->send404();
        }

        $redirectUrl = $session->redirect;
        $redirectUrl .= (strpos($redirectUrl, '?') ? '' : '?') . 'code=' . $code;

        return $this->redirect()->toUrl($redirectUrl);
    }

    public function indexAction()
    {
        $view = $this->generate();

        $contacts = $this->layout()->getVariable('contacts');

        $productsService = $this->getProductsService();
        $products = $productsService->getProducts([
            'sort'      => 'popularity',
            'minPrice'  => true,
            'join'      => ['reviews', 'image'],
            'limit'     => 5
        ]);

        $catalogService = $this->getCatalogService();
        $catalog = $catalogService->getCatalog([
            'join'      => ['image'],
            //'limit'     => 3
        ]);

        $catalog->setParentId(0);

        $articles = $this->getBlogService()->getArticles(['limit' => 4]);

        $discount = $this->getDiscountsService()->getActiveDiscount();

        return $view->setVariables([
            'discount'      => $discount,
            'products'      => $products,
            'catalog'       => $catalog,
            'contacts'      => $contacts,
            'articles'      => $articles,
            'page'          => $this->layout()->getVariable('page'),
        ]);
    }

    public function sitemapAction()
    {
        $sitemapXml = $this->getSitemapService()->generateSitemap();
        header('Content-type: application/xml');
		
		file_put_contents(PUBLIC_DIR . '/sitemap.xml', $sitemapXml);
        die($sitemapXml);
    }

    public function robotsAction()
    {
        $settings = $this->getServiceLocator()->get('Settings');
        header('Content-type: text/plain');
        die($settings->get('robots'));
    }

    public function pageAction()
    {
        $view = $this->generate();

        $layout = $this->layout();
        $page = $layout->getVariable('page');

        if(!$page) {
            return $this->send404();
        }

        if($page->get('redirect_url')) {
            return $this->redirect()->toUrl($page->get('redirect_url'));
        }

        if(!$page->getId()) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->sendHeaders();
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTemplate('application/index/page-ajax');
            $view->setTerminal(true);


            $view->setVariables(array(
                'header'  => $layout->getVariable('header'),
                'text'    => $layout->getVariable('page')->get('text'),
            ));
        }

        return $view;
    }

    /**
     * @return \Application\Service\SitemapService
     */
    protected function getSitemapService()
    {
        return $this->getServiceLocator()->get('Application\Service\SitemapService');
    }

    /**
     * @return \Catalog\Service\CatalogService
     */
    protected function getCatalogService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CatalogService');
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\ProductsService');
    }

    /**
     * @return \Blog\Service\BlogService
     */
    protected function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }

    /**
     * @return \Discounts\Service\DiscountsService
     */
    protected function getDiscountsService()
    {
        return $this->getServiceLocator()->get('Discounts\Service\DiscountsService');
    }
}
