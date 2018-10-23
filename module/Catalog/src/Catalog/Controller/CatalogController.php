<?php
namespace Catalog\Controller;

use Application\Model\Module;
use Aptero\Mvc\Controller\AbstractActionController;

use Catalog\Model\Catalog;
use Catalog\Model\Product;
use Catalog\Form\ReviewForm;
use Delivery\Model\Delivery;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class CatalogController extends AbstractActionController
{
    public function balanceAction()
    {
        $this->getProductsService()->getProductsBalance();
        return $this->send404();
    }

    public function indexAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $catalogService = $this->getCatalogService();

        if($url) {
            if($url == 'event') {
                return $this->eventProductsAction();
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

            $type = $catalogService->getTypeByUrl($category->getId(), $subUrl);
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
        $catalog = Catalog::getEntityCollection();
        $catalog->setParentId(0);

        $filters = $this->params()->fromQuery();
        
        $products = $this->getProductsService()->getPaginator($page, $filters);

        $view = new ViewModel();
        return $view->setVariables([
            'header'   => $this->layout()->getVariable('header'),
            'catalog'  => $catalog,
            'products' => $products,
			'page'     => $page,
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ]);
    }

    public function compareAction()
    {
        $productsService = $this->getProductsService();

        $compareList = $productsService->getCompareProducts();

        $this->generate('/compare/');

        return [
            'breadcrumbs' => $this->getBreadcrumbs(),
            'header'      => $this->layout()->getVariable('header'),
            'compareList' => $compareList
        ];
        
    }

    public function popularProductsAction()
    {
        //$categoryIds = $this->getCatalogService()->getCatalogIds($category);
        $products = $this->getProductsService()->getProducts([
            'sort'      => 'discount',
            //'catalog'   => $categoryIds,
            'join'      => ['image', 'brand'],
            'minPrice'  => true,
            'limit' => 3
        ]);

        $html = '';

        $helper = $this->getViewHelper('productItem');

        foreach($products as $product) {
            $html .= $helper($product, ['list' => 'search']);
        }
        
        die($html);
    }

    public function eventProductsAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $productsService = $this->getProductsService();

        $this->generate('/catalog/event/');
        $meta =

        $priceMinMax  = $productsService->getMinMaxPrice();

        $filtersData = ['priceMinMax' => $priceMinMax];

        $filters = [];

        $filters = array_merge($filters, $this->productsFilters($filtersData));
        $filters['catalog'] = 0;
        $filters['onlyDiscount'] = true;

        $products = $productsService->getPaginator($page, $filters);

        $brands = $this->getBrandsService()->getProductsBrands();

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];

            $resp['html']['products'] = $this->viewHelper('productsList', $products);

            $widgetsHelper = $this->getViewHelper('catalogWidgets');

            $resp['html']['filters'] =
                $widgetsHelper('price', ['data' => $filters['price'], 'min' => $priceMinMax['min'], 'max' => $priceMinMax['max']])
                .$widgetsHelper('sort', ['data' => $filters['sort']])
                .$widgetsHelper('brands', ['data' => $filters['brands'], 'brands' => $brands]);

            $resp['meta'] = $meta;

            return new JsonModel($resp);
        }

        $this->layout()->setVariable('canonical', '/catalog/all/');

        $view = new ViewModel();
        $view->setTemplate('catalog/catalog/category-all');

        //$this->addBreadcrumbs([['url' => '', 'name' => 'Товары со скидкой']]);

        return $view->setVariables([
            'priceMinMax'   => $priceMinMax,
            'filters'  => $filters,
            'brands'   => $brands,
            'products' => $products,
            'page'     => $page,
            'header'   => 'Товары со скидкой',
            'breadcrumbs'  => $this->getBreadcrumbs(),
        ]);
    }

    public function categoryAction($category, $options = [])
    {
        $catalogService = $this->getCatalogService();

        $brand = $options['brand'] ?? null;
        $type  = $options['type'] ?? null;

        if($brand) {
            $this->generateMeta(null, ['{CATALOG_NAME}', '{BRAND_NAME}'], [$category->get('name'), $brand->get('name')], [
                'title'       => '{CATALOG_NAME} {BRAND_NAME} - купить в интернет-магазине Olympic Trade',
                'description' => '{CATALOG_NAME} {BRAND_NAME} - каталог с ценами, фото и подробными описаниями. Заказать в интернет-магазине Olympic Trade.',
            ]);
        } elseif($type) {
            $this->generateMeta($type, ['{CATALOG_NAME}', '{CATALOG_NAME_L}'], [$category->get('name'), mb_strtolower($category->get('name'))]);
        } else {
            $this->generateMeta($category, ['{CATALOG_NAME}', '{CATALOG_NAME_L}'], [$category->get('name'), mb_strtolower($category->get('name'))]);
        }


        $meta = $this->layout()->getVariable('meta');
        $meta->title = $meta->title ? $meta->title : $category->get('header');

        $parent = $category;
        while($parent = $parent->getParent()) {
            $meta->keywords .= ', ' . $parent->get('name');
        }

        $this->layout()->setVariable('meta', $meta);

        $categoryIds = $catalogService->getCatalogIds($category);
        $page = $this->params()->fromQuery('page', 1);
        $productsService = $this->getProductsService();

        $priceMinMax  = $productsService->getMinMaxPrice([
            'catalog' => ($category->get('parent') ? $catalogService->getCatalogIds($category->getParent()) : $categoryIds)
        ]);

        $filtersData = ['priceMinMax'   => $priceMinMax];

        $filters = [];

        if($brand) {
            $filters['brand'] = [$brand->getId()];
        } elseif($type) {
            $filters['type'] = $type->getId();
        }

        $filters = array_merge($filters, $this->productsFilters($filtersData));
        $filters['catalog'] = $categoryIds;

        $products = $productsService->getPaginator($page, $filters);

        $brands = $this->getBrandsService()->getProductsBrands(['catalog' => $categoryIds]);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];

            $resp['html']['products'] = $this->viewHelper('productsList', $products);

            $widgetsHelper = $this->getViewHelper('catalogWidgets');

            $resp['html']['filters'] =
                 $widgetsHelper('price', ['data' => $filters['price'], 'min' => $priceMinMax['min'], 'max' => $priceMinMax['max']])
                .$widgetsHelper('sort', ['data' => $filters['sort']])
                .$widgetsHelper('brands', ['data' => $filters['brands'], 'brands' => $brands]);

            $resp['meta'] = $meta;

            return new JsonModel($resp);
        }

        if($brand) {
            $url = $category->getUrl() . $brand->get('url') . '/';
        } elseif($type) {
            $url = $type->getUrl();
        } else {
            $url = $category->getUrl();
        }

        $this->layout()->setVariable('canonical', $url);
        $this->addBreadcrumbs($catalogService->getCategoryCrumbs($category));

        $view = new ViewModel();
        $view->setTemplate('catalog/catalog/category');

        if($brand) {
            $header = $category->get('name') . ' ' . $brand->get('name');
            $this->addBreadcrumbs([['url' => $url, 'name' => $brand->get('name')]]);
        } elseif($type) {
            $header = $type->get('name');
            $this->addBreadcrumbs([['url' => $url, 'name' => $type->get('name')]]);
        } else {
            $header = $category->get('name');
        }

        $filters['type'] = $type;

        return $view->setVariables([
            'priceMinMax'   => $priceMinMax,
            'filters'  => $filters,
            'brands'   => $brands,
            'category' => $category,
            'products' => $products,
            'page'     => $page,
            'header'   => $header,
            'breadcrumbs'     => $this->getBreadcrumbs(),
        ]);
    }

    public function brandsAction()
    {
        $url = $this->params()->fromRoute('url');

        $view = $this->generate('/brands/');

        if(!$url) {
            $brands = $this->getBrandsService()->getBrands(['join' => ['image']]);
            return $view->setVariables([
                'brands'        => $brands,
            ]);
        }

        $brandService =  $this->getBrandsService();

        $brand = $brandService->getBrand([
            'url' => $url,
            'columns' => ['id', 'name', 'url', 'html', 'title', 'description']
        ]);

        if(!$brand->load()) {
            return $this->send404();
        }

        if(!$brand->get('html')) {
            $this->redirect()->toUrl($brand->getUrl() . 'products/');
        }

        $settings = Module::getSettings('catalog', 'brands');
        $this->generateMeta($brand, ['{BRAND_NAME}'], [$brand->get('name')], [
            'title'       => $settings->get('view-title'),
            'description' => $settings->get('view-description'),
        ]);

        $this->addBreadcrumbs([['url' => $brand->getUrl(), 'name' => $brand->get('name')]]);

        $view->setTemplate('catalog/catalog/brand');

        if($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        return $view->setVariables([
            'brand' => $brand,
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }

    public function brandProductsAction()
    {
        $url = $this->params()->fromRoute('url');

        $view = $this->generate('/brands/');

        $brandService =  $this->getBrandsService();
        
        if(!$url) {
            $brands = $this->getBrandsService()->getBrands(['join' => ['image']]);
            
            return [
                'header'        => $this->layout()->getVariable('header'),
                'brands'        => $brands,
                'breadcrumbs'   => $this->getBreadcrumbs(),
            ];
        }

        if(!$brand = $brandService->getBrand(['url' => $url])->load()) {
            return $this->send404();
        }

        $productsService = $this->getProductsService();

        $filtersData = [
            'priceMinMax'   => $productsService->getMinMaxPrice(['brand' => ['brand' => $brand->getId()]])
        ];

        $filters = $this->productsFilters($filtersData);

        $settings = Module::getSettings('catalog', 'brands');
        $this->generateMeta($brand, ['{BRAND_NAME}'], [$brand->get('name')], [
            'title'       => $settings->get('products-title'),
            'description' => $settings->get('products-description'),
        ]);

        $filters['brand'] = $brand->getId();

        $products = $this->getProductsService()->getPaginator($this->params()->fromQuery('page'), $filters);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];

            $resp['html']['products'] = $this->viewHelper('productsList', $products);

            $widgetsHelper = $this->getViewHelper('catalogWidgets');

            $resp['html']['filters'] =
                $widgetsHelper('price', ['data' => $filters['price'], 'min' => $filtersData['priceMinMax']['min'], 'max' => $filtersData['priceMinMax']['max']])
                .$widgetsHelper('sort', ['data' => $filters['sort']]);

            $meta = $this->layout()->getVariable('meta');
            $resp['meta'] = $meta;

            return new JsonModel($resp);
        }

        $view->setTemplate('catalog/catalog/brand-products');

        $this->addBreadcrumbs([['url' => $brand->getUrl(), 'name' => $brand->get('name')]]);

        $view->setVariables([
            'products'      => $products,
            'brand'         => $brand,
            'filters'       => $filters,
            'filtersData'   => $filtersData,
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
        
        return $view;
    }

    public function productsFilters($options)
    {
        $filters = $this->params()->fromQuery();

        if($filters['price']) {
            $filters['price'] = [];
            list($filters['price']['min'], $filters['price']['max']) = explode(';', $_GET['price']);

            if($options['priceMinMax']['min'] >= $filters['price']['min']) {
                unset($filters['price']['min']);
            }

            if($options['priceMinMax']['max'] <= $filters['price']['max']) {
                unset($filters['price']['max']);
            }
        }

        if(!isset($filters['sort'])) {
            $filters['sort'] = 'popularity';
        }

        if($filters['brand']) {
            $filters['brand'] = explode(',', $filters['brand']);
        }

        if($options['type']) {
            $filters['type'] = $options['type']->getId();
        }

        return $filters;
    }

    public function googleMerchantAction()
    {
        $ymlService = $this->getServiceLocator()->get('Catalog\Service\GoogleMerchant');

        header("Content-type: text/xml; charset=utf-8");

        echo $ymlService->getYML();

        die();
    }

    public function searchAction()
    {
        $query = trim(urldecode($this->params()->fromQuery('query')));

        if($this->getRequest()->isXmlHttpRequest()) {
            $results = $this->getCatalogService()->getAutoComplete($query);
            return new JsonModel($results);
        }

        $category = $this->getCatalogService()->getCategoryByName($query);
        if($category->load()) {
            $this->redirect()->toUrl('/catalog/' . $category->getUrl() . '/');
        }

        $category = $this->getBrandsService()->getBrand(['query' => $query]);
        if($category->load()) {
            $this->redirect()->toUrl('/brands/' . $category->get('url') . '/');
        }

        $this->generate('/catalog/search/');

        if(empty($query)) {
            return [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'header'    => 'Поиск',
                'products'  => [],
                'catalog'   => [],
                'query'     => $query,
            ];
        }

        $page = $this->params()->fromRoute('page', 1);
        $products = $this->getProductsService()->getPaginator($page, array('query' => $query));

        return array(
            'breadcrumbs' => $this->getBreadcrumbs(),
            'header'    => 'Поиск "' . $this->viewHelper('escapeHtml', $query) . '"',
            'products'  => $products,
            'catalog'   => $this->getProductsService()->getProductsCategories(array('query' => $query)),
            'brands'    => $this->getProductsService()->getProductsBrands(array('query' => $query)),
            'query'     => $query,
        );
    }

    public function addReviewAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        if ($request->isPost()) {
            $form = new ReviewForm();
            $form->setData($request->getPost())->setFilters();

            if ($form->isValid()) {
                $this->getProductsService()->addReview($form->getData());
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $product = new Product();
        $product->setId($this->params()->fromQuery('pid'));
        if(!$product->load()) {
            $this->send404();
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'product'   => $product
        ));

        return $viewModel;
    }

    public function getPriceAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        $data = $this->params()->fromPost();

        if(!(int) $data['product_id']) {
            return $this->send404();
        }

        $product = $this->getProductsService()->getProduct(array(
            'taste_id'  => $data['taste_id'],
            'size_id'   => $data['size_id'],
            'id'        => $data['product_id'],
        ));

        return new JsonModel(array(
            'price'     => $product->get('price'),
            'price_old' => $product->get('price_old'),
            'stock'     => $product->get('stock'),
        ));
    }

    public function getProductStockAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        $data = $this->params()->fromPost();

        if(!(int) $data['product_id']) {
            return $this->send404();
        }

        $product = new Product();
        $product->setId($data['product_id']);

        $resp = [];

        foreach($product->getPlugin('size') as $size) {
            $sizeId = $size->getId();

            $resp[$sizeId] = [
                'taste' => [],
                'stock' => [],
                'id'    => $sizeId,
            ];

            $stock = 0;
            $product->clearPlugin('taste');
            foreach($product->getPlugin('taste', array('size_id' => $sizeId)) as $taste) {
                $resp[$sizeId]['taste'][$taste->getId()] = $taste->get('stock');
                $stock += $taste->get('stock');
            }

            $resp[$sizeId]['stock'] = $stock;
        }

        return new JsonModel($resp);
    }

    public function getProductInfoAction()
    {
        $products = $this->getProductsService()->getProductsInfo($this->params()->fromPost());

        return new JsonModel($products);
    }

    public function productsPopularityAction()
    {
        $this->getProductsService()->updateProductsStatistic();
        return $this->send404();
    }
    
    public function productAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $productsService = $this->getProductsService();

        $filters = ['url' => $url];

        if(!empty($_GET['variation']) && preg_match('/^(\d+)-(\d+)$/', $_GET['variation'], $matches)) {
            $filters['size_id'] = $matches[1];
            $filters['taste_id'] = $matches[2];
        } else {
            $filters['minPrice'] = true;
        }

        $product = $productsService->getProductForView($filters);

        if(!$product->load()) {
            $product = new Product();
            $product->select()->where(['old_url' => $url]);
            if($product->load()) {
                return $this->redirect()->toUrl($product->getUrl())->setStatusCode(301);
            }

            return $this->send404();
        }

        $metaSearch  = ['{PRODUCT_NAME}', '{CATALOG_NAME}', '{BRAND_NAME}'];
        $metaReplace = [$product->get('name'), $product->getPlugin('catalog')->get('name'), $product->getPlugin('brand')->get('name')];

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
                            $product->set('title', $product->get('title') . ' купить недорого в СПб, Москве и других регионах России - {BRAND_NAME}');
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

        $recoProducts   = $productsService->getRecoProducts($product);
        $viewedProducts = $productsService->getViewedProducts($product);

        $url = $product->getUrl();

        $this->layout()->setVariable('header', $product->get('name'));
        $this->layout()->setVariable('canonical', $url);

        $category = $product->getPlugin('catalog');
        $urlCatalog = $category->getUrl();

        $brand = $product->getPlugin('brand');

        $this->addBreadcrumbs($this->getCatalogService()->getCategoryCrumbs($category));
        $this->addBreadcrumbs([['url' => $urlCatalog . $brand->get('url') . '/', 'name' => $brand->get('name')]]);
        $this->addBreadcrumbs([['url' => $product->getUrl(), 'name' => $product->get('name')]]);

        $view = new ViewModel();

        $view->setVariables([
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'header'       => $product->get('name'),
            //'inCart'       => $this->getCartService()->checkInCart($product->getId()),
            'product'      => $product,
            'category'     => $category,
            'delivery'     => Delivery::getInstance(),
            'recoProducts'   => $recoProducts,
            'viewedProducts' => $viewedProducts,
            'tabs'         => $tabs,
            'tabUrl'       => $tabUrl,
        ]);

        if($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
            $view->setTemplate('catalog/catalog/product-ajax.phtml');
        }

        return $view;
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