<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Catalog\Model\Catalog;
use Catalog\Model\Product;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class MobileCatalogController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $catalogService = $this->getCatalogService();

        if($url) {
            switch ($url) {
                case 'nutrition':
                    $catalog = Catalog::getEntityCollection();
                    $catalog->select()->where->notEqualTo('id', 17);
                    $catalog->setParentId(0);
                    die($this->viewHelper('mobileCatalogList', $catalog));
                    break;
                case 'clothes':
                    $catalog = Catalog::getEntityCollection();
                    $catalog->setParentId(17);
                    die($this->viewHelper('mobileCatalogList', $catalog));
                    break;
                default:
            }

            $category = $catalogService->getCategory(['url' => $url])->load();

            if($category) {
                return $this->categoryAction($category);
            }

            $subUrl = substr($url, strrpos($url, '/') + 1);
            $categoryUrl = substr($url, 0, strrpos($url, '/'));

            $category = $catalogService->getCategory(['url' => $categoryUrl]);

            if(!$category->load()) {
                return $this->send404();
            }

            $type = $catalogService->getTypeByUrl($subUrl);
            if($type) {
                return $this->categoryAction($category, ['type' => $type]);
            }

            $brand = $this->getBrandsService()->getBrandByUrl($subUrl);
            if($brand) {
                return $this->categoryAction($category, ['brand' => $brand]);
            }

            return $this->send404();
        }

        /* Products */
        $page = $this->params()->fromQuery('page', 1);

        $view = new ViewModel();
        return $view->setVariables([
            'header'   => $this->layout()->getVariable('header'),
            'page'     => $page,
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ]);
    }
    public function categoryAction($category)
    {
        $catalogService = $this->getCatalogService();

        $url = $this->url()->fromRoute('catalog', array('url' => $category->getUrl()));
        $this->layout()->setVariable('canonical', $url);
        $this->addBreadcrumbs($catalogService->getCategoryCrumbs($category));

        $this->generateMeta($category, array('{CATALOG_NAME}', '{CATALOG_NAME_L}'), array($category->get('name'), mb_strtolower($category->get('name'))));

        $category->clearSelect();
        $category->select()->where(array('active' => 1));
        $categoryIds = $catalogService->getCatalogIds($category);

        $view = new ViewModel();
        $view->setTemplate('catalog/mobile-catalog/category');

        $page = $this->params()->fromQuery('page', 1);
        $productsService = $this->getProductsService();
        $filter['catalog'] = $categoryIds;
        $products = $productsService->getPaginator($page, $filter);

        $bLink = $category->getParent() ? '/catalog/' .  $category->getParent()->get('url_path' . '/') : '/catalog/';

        return $view->setVariables(array(
            'header'   => $category->get('name'),
            'category' => $category,
            'products' => $products,
            'page'     => $page,
            'bLink'    => $bLink,
        ));
    }

    public function yandexMarkerAction()
    {
        $ymlService = $this->getServiceLocator()->get('Catalog\Service\YandexYml');

        header("Content-type: text/xml; charset=utf-8");
        echo $ymlService->getYML();
        die();
    }

    public function productAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $productsService = $this->getProductsService();
        $product = $productsService->getProductForView(['url' => $url, 'minPrice' => true]);

        if(!$product->load()) {
            $product = new Product();
            $product->select()->where(['old_url' => $url]);
            if($product->load()) {
                return $this->redirect()->toUrl($product->getUrl())->setStatusCode(301);
            }

            return $this->send404();
        }

        if(!$product->load()) {
            return $this->send404();
        }

        $metaSearch  = array('{PRODUCT_NAME}', '{CATALOG_NAME}', '{BRAND_NAME}');
        $metaReplace = array($product->get('name'), $product->getPlugin('catalog')->get('name'), $product->getPlugin('brand')->get('name'));

        $tabUrl = $this->params()->fromRoute('tab', '');

        $tabs = [];

        $tabs[] = [
            'tab'    => 'default',
            'header' => 'Описание',
            'url'    => '',
        ];

        $tabs[] = [
            'tab'    => 'composition',
            'header' => 'Состав',
            'url'    => 'composition',
        ];

        $attrs = $product->getPlugin('attrs');

        for($i = 1; $i <= 3; $i++) {
            $tab = 'tab' . $i;
            if(!$attrs->get($tab . '_url')) { continue; }
            $tabs[] = [
                'tab'    => $tab,
                'header' => $attrs->get($tab . '_header'),
                'url'    => $attrs->get($tab . '_url'),
            ];
        }

        $tabs[] = [
            'tab'    => 'reviews',
            'header' => 'Отзывы' . ($product->get('reviews') ? ' <span>(' . $product->get('reviews') . ')</span>' : ''),
            'url'    => 'reviews',
        ];

        $tabs[] = [
            'tab'    => 'articles',
            'header' => 'Статьи',
            'url'    => 'articles',
        ];

        $tabs[] = [
            'tab'    => 'video',
            'header' => 'Видео',
            'url'    => 'video',
        ];

        $meta = null;

        foreach($tabs as $key => $tab) {
            $viewHelper = $this->getSL()->get('ViewHelperManager')->get('productTabs');
            $html = $viewHelper($product, $tab['tab']);

            if($tab['url'] == $tabUrl) {
                switch($tab['tab']) {
                    case 'default':
                        if($product->get('title')) {
                            $product->set('title', $product->get('title') . ' | {BRAND_NAME}');
                        }

                        if($product->get('keywords')) {
                            $product->set('keywords', $product->get('keywords') . ', спортивное питание, {BRAND_NAME}');
                        }

                        if($product->get('description')) {
                            $product->set('description', rtrim($product->get('description'), '. ') . '. Доставка по Москве и Санкт-Петербургу.');
                        }

                        $meta = $this->generateMeta($product, $metaSearch, $metaReplace);
                        break;
                    case 'video':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'reviews':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'composition':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'articles':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'certificate':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'tab1':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab1_title'),
                            'description' => $attrs->get('tab1_description'),
                            'keywords'    => $attrs->get('tab1_keywords'),
                        ));
                        break;
                    case 'tab2':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab2_title'),
                            'description' => $attrs->get('tab2_description'),
                            'keywords'    => $attrs->get('tab2_keywords'),
                        ));
                        break;
                    case 'tab3':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab3_title'),
                            'description' => $attrs->get('tab3_description'),
                            'keywords'    => $attrs->get('tab3_keywords'),
                        ));
                        break;
                    default:
                }

                $tabs[$key]['html'] = $html;

                if($this->getRequest()->isXmlHttpRequest()/* && $this->params()->fromQuery('view') == 'tab'*/) {
                    $resp = array(
                        'html'  => $html,
                        'meta'  => $meta,
                    );
                    return new JsonModel($resp);
                }
            }

            if(!$html) {
                unset($tabs[$key]);
                continue;
            }
        }

        if(!$meta) {
            $this->send404();
        }

        if(!$product->getId()) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->sendHeaders();
        }

        $url = $product->getUrl();

        $this->layout()->setVariable('header', $product->get('name'));
        $this->layout()->setVariable('canonical', $url);

        $category = $product->getPlugin('catalog');

        $urlCatalog = $category->getUrl();

        $this->addBreadcrumbs($this->getCatalogService()->getCategoryCrumbs($category));
        $this->addBreadcrumbs(array(array('url' => $urlCatalog, 'name' => $category->get('name'))));

        return array(
            'bLink'        => '/catalog/' . $category->getUrl() . '/',
            'header'       => $product->get('name'),
            //'inCart'       => $this->getCartService()->checkInCart($product->getId()),
            'product'      => $product,
            'category'     => $category,
            'tabs'         => $tabs,
            'tabUrl'       => $tabUrl,
        );
    }

    /**
     * @return \Catalog\Service\BrandsService
     */
    protected function getBrandsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\BrandsService');
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
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
}