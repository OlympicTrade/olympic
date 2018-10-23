<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Mail\Mail;
use Aptero\MSOffice\Excel;
use Aptero\String\Price;
use Aptero\String\Search;
use Catalog\Model\Catalog;
use Catalog\Model\Reviews;
use Catalog\Model\Size;
use Catalog\Model\Taste;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use User\Service\AuthService;
use Zend\Db\Sql\Expression;
use Aptero\Service\AbstractService;
use Catalog\Model\Product;
use Zend\Json\Json;
use Zend\Paginator\Paginator;

class ProductsService extends AbstractService
{
    public function productsExcel($filter = array())
    {

        $percents = [
            'min' => ['sum' => 15000, 'percent' => 20],
            'med' => ['sum' => 30000, 'percent' => 25],
            'max' => ['sum' => 45000, 'percent' => 30],
        ];

        $excel = new Excel();


        //logo
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(PUBLIC_DIR . '/images/opt_logo.jpg');
        $drawing->setWorksheet($excel->getAs());
        $drawing->setHeight(61);
        $drawing->setCoordinates('A2');

        //Header
        $excel
            ->setTitle('Прайс от ' . date('d.m.Y'))
            ->setVal('Сумма', 5, 2)
            ->setVal('Скидка %', 5, 3)
            ->setVal('Итого', 5, 4)
            ->nextRow()
            ->nextRow()
            ->setVal('Наименование')->setColWidth(1, 30)
            ->setVal('Размер')->setColWidth(2, 15)
            ->setVal('Вкус/Цвет')->setColWidth(3, 30)
            ->setVal('Рек. розн. цена')->setColWidth(4, 10)
            ->setVal('Заказ шт.')->setColWidth(5, 14)
            ->setVal('Сумма')->setColWidth(6, 14)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['min']['sum']))->setColWidth(7, 16)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['med']['sum']))->setColWidth(8, 16)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['max']['sum']))->setColWidth(9, 16)
            ->nextRow()
            ->setVal('Скидка ' . $percents['min']['percent'] . '%', 7)
            ->setVal('Скидка ' . $percents['med']['percent'] . '%', 8)
            ->setVal('Скидка ' . $percents['max']['percent'] . '%', 9)
            ->nextRow();

        //Products
        $products = (new Product)->addProperties([
            'size_id'   => [],
            'size'      => [],
            'taste_id'  => [],
            'taste'     => [],
        ])->getCollection();

        $filter = [
            'join'      => ['brands', 'brand-country', 'catalog'],
            'columns'   => ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'subname', 'discount', 'url', 'barcode']
        ];

        $filter['optPrice'] = true;
        $filter['group'] = 'ps.id';
        $select = $this->getProductsSelect($filter);
        $select
            ->order('t.popularity, t.name, pss.id, pst.id')
            ->where
            ->equalTo('brand_id', 42)
            ->nest()
                ->greaterThan('pst.coefficient', 0)
                ->and
                ->greaterThan('pss.price', 0)
            ->unnest();

        $products->setSelect($select);

        $firstRow = $lastRow = 7;
        foreach($products as $product) {
            $excel->setVal($product->get('name'));

            $price = $product->get('price_old');

            $excel
                ->setVal($product->get('size'))
                ->setVal($product->get('taste'))
                ->setVal($price)
                ->setVal(0);

            $x = $excel->x();

            $excel
                ->setVal('=' . $excel->getCoords(null, $x - 1) . '*' . $excel->getCoords(null, $x - 2))
                ->setVal((int) ($price * 0.80))
                ->setVal((int) ($price * 0.75))
                ->setVal((int) ($price * 0.70))
                ->nextRow();

            $lastRow = $excel->y() - 1;
        }

        //Summary
        $percentFormula =
            '=IF(F2<' . $percents['min']['sum'] . ', 0, ' .
                'IF(F2<' . $percents['med']['sum'] . ', ' . $percents['min']['percent'] . ', ' .
                    'IF(F2<' . $percents['max']['sum'] . ', ' . $percents['med']['percent'] . ', ' .
                        $percents['max']['percent'] .
                    ')' .
                ')' .
            ')';

        $excel->setVal('=SUM(' . $excel->getCoords($firstRow, 6) . ':' . $excel->getCoords($lastRow, 6) . ')', 6, 2);
        $excel->setVal($percentFormula, 6, 3);
        $excel->setVal('=' . $excel->getCoords(2, 6) . '*(1-' . $excel->getCoords(3, 6) . '/100)', 6, 4);
        $excel->send();
    }

    public function addReview($data)
    {
        if($user = AuthService::getUser()) {
           $data['user_id'] =  $user->getId();
        }

        $data['status'] =  Reviews::STATUS_NEW;

        $review = new Reviews();
        $review->setVariables($data)->save();

        $this->sendReviewMail($review);
    }

    public function sendReviewMail($review)
    {
        $feedbackModule = new \Application\Model\Module();
        $feedbackModule
            ->setModuleName('Contacts')
            ->setSectionName('Feedback')
            ->load();

        $siteSettings = $this->getServiceManager()->get('Settings');

        $mail = new Mail();
        $mail->setTemplate(MODULE_DIR . '/Catalog/view/catalog/mail/review.phtml')
            ->setHeader($siteSettings->get('site_name') . '. Новый отзыв')
            ->setVariables(array('review' => $review))
            ->addTo($feedbackModule->getPlugin('settings')->get('email'))
            ->send();
    }

    public function getCompareProducts()
    {
        $cookie = json_decode($_COOKIE['compare-list']);

        if(empty($cookie)) {
            return null;
        }

        $ids = [];

        foreach ($cookie as $row) {
            $ids[$row->cid][] = $row->id;
        }

        $result = [];

        //var_dump($ids);die();

        foreach ($ids as $categoryId => $productIds) {
            $category = new Catalog();
            $category->setId($categoryId);

            $products = $this->getProducts([
                'id'         => $productIds,
                'catalog_id' => $categoryId,
                'minPrice'   => true,
                'join'       => ['brand', 'image']
            ]);

            $result[] = [
                'category'  => $category,
                'products'  => $products,
            ];
        }

        return $result;
    }

    /**
     * @param int $page
     * @param array $filters
     * @return Paginator
     */
    public function getPaginator($page, $filters = [], $itemsPerPage = 12)
    {
        $filters['join'] = [
            'reviews',
            'catalog',
            'brand',
            'image',
        ];

        $filters['minPrice'] = true;

        $products = Product::getEntityCollection();
        $products->setSelect($this->getProductsSelect($filters));

        return $products->getPaginator($page, $itemsPerPage);
    }

    public function getMinMaxPrice($filters = [])
    {
        $sSelect = $this->getSql()->select()
            ->from(['ps' => 'products_size'])
            ->columns(['price' => new Expression('MIN(ps.price)')])
            ->where(['t.id' => new Expression('ps.depend')]);

        $tSelect = $this->getSql()->select()
            ->from(['pt' => 'products_taste'])
            ->columns(['coefficient' => new Expression('MIN(pt.coefficient)')])
            ->where(['t.id' => new Expression('pt.depend')]);

        $priceSql = '((' . $this->getSql()->buildSqlString($sSelect) . ') * (' . $this->getSql()->buildSqlString($tSelect) . ')) * (1 - t.discount / 100)';

        $filters['columns'] = [
            'min' => new Expression('MIN(' . $priceSql . ')'),
            'max' => new Expression('MAX(' . $priceSql . ')'),
        ];

        $filters['group'] = '';

        $select = $this->getProductsSelect($filters);
        $product = new Product();
        $results = $product->fetchRow($select);

        return [
            'min'   => max(0, $results['min']/* - 1*/),
            'max'   => $results['max']/* + 1*/,
        ];
    }

    public function getProductsInfo($data)
    {
        if($products = $data['products']) {
            $resp = array();
            foreach($products as $product) {
                $resp[] = $this->getProductsInfo($product);
            }

            return $resp;
        }

        $id      = $data['product_id'];
        $tasteId = isset($data['taste_id']) ? $data['taste_id'] : null;
        $sizeId  = isset($data['size_id']) ? $data['size_id'] : null;

        $product = $this->getProduct([
            'id'        => $id,
            'taste_id'  => $tasteId,
            'size_id'   => $sizeId,
        ]);

        if(!$product->load()) {
            return false;
        }

        $variant = $product->get('name');

        $taste = new Taste();
        $taste->setId($tasteId);
        if($taste->load()) {
            $variant .= ' ' . $taste->get('name');
        }

        $size = new Size();
        $size->setId($sizeId);
        if($size->load()) {
            $variant .= ' ' . $size->get('name');
        }

        if($variant == $product->get('name')) {
            $variant = '';
        }

        $result = [
            'id'        => $product->getId(),
            'name'      => $product->get('name'),
            'price'     => $product->get('price'),
            'price_old' => $product->get('price_old'),
            'brand'     => $product->getPlugin('brand')->get('name'),
            'catalog'   => $product->getPlugin('catalog')->get('name'),
            'variant'   => $variant,
        ];

        if(isset($data['count'])) {
            $result['count'] = $data['count'];
        }

        return $result;
    }

    /**
     * @param array $filters
     * @param array $extend
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function getProducts($filters = [], $extend = [])
    {
        $product = new Product();

        foreach($extend as $prop) {
            $product->addProperty($prop);
        }

        $products = $product->getCollection();
        $products->setSelect($this->getProductsSelect($filters));

        return $products;
    }

    /**
     * @param $filters
     * @param array $extend
     * @return Product
     */
    public function getProduct($filters, $extend = [])
    {
		$product = new Product();
        $product->addProperties([
            'size'  => ['virtual' => true],
            'taste' => ['virtual' => true],
        ]);

        foreach($extend as $prop) {
            $product->addProperty($prop);
        }

        $product->setSelect($this->getProductsSelect($filters));

        //die($product->dump());

        return $product;
    }

    public function getProductForView($filter)
    {
        $product = new Product();

        $product->addProperties([
            'size_id'     => [],
            'taste_id'    => [],
        ]);

        $filter['join'] = array(
            'brands',
            'reviews',
            'stock',
        );


        $filter['columns'] = ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'subname', 'discount', 'url',
			'preview', 'text', 'video', 'title', 'description', 'units'];

        $filter['sort'] = 'price';

        $select = $this->getProductsSelect($filter);

        $product->setSelect($select);

        return $product;
    }

    public function updateProductsStatistic()
    {
        $update = $this->getSql()->update();
        $update
            ->table('products')
            ->set(['popularity' => 0]);

        $this->execute($update);

        $select =
            $this->getSql()->select()
            ->from(['o' => 'orders'])
            ->columns(['id', 'count' => new Expression('count(DISTINCT o.id)')])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', [])
            ->join(['p' => 'products'], 'c.product_id = p.id', ['id'])
            ->group('p.id')
            ->order('count DESC');

        $dt = new \DateTime();

        $select->where
            ->lessThanOrEqualTo('o.time_create', $dt->format('Y-m-d'))
            ->greaterThanOrEqualTo('o.time_create', $dt->modify('-2 months')->format('Y-m-d'));

        $result = $this->execute($select);

        foreach ($result as $row) {
            $update =
                $this->getSql()->update()
                ->table('products')
                ->set(['popularity' => $row['count']])
                ->where(['id' => $row['id']]);

            $this->execute($update);
        }
    }

    public function getRecoProducts($product)
    {
        $products = EntityFactory::collection(new Product());

        $select = $this->getProductsSelect(array(
            'category'  => $product->get('catalog_id'),
			'join' => array('reviews')
        ));
		
        $select
            ->order(new Expression('RAND()'))
            ->limit(10)
            ->where->notEqualTo('t.id', $product->getId());

        $products->setSelect($select);

        return $products;
    }

    public function getViewedProducts($product = null)
    {
        if(!isset($_COOKIE['viewed-products'])) {
            return array();
        }

        if(!$cookie = Json::decode($_COOKIE['viewed-products'])) {
            return array();
        }

        foreach($cookie as $cProduct) {
            if(!$id = (int) $cProduct->id) {
                continue;
            }
            $ids[] = $id;
        }

        if(!$ids) {
            return array();
        }

        $products = EntityFactory::collection(new Product());

        $select = $this->getProductsSelect(array('join' => array('reviews')))
            ->limit(10);

        $select->where->in('t.id', $ids);

        if($product) {
            $select->where->notEqualTo('t.id', $product->getId());
        }

        $products->setSelect($select);

        return $products;
    }

    public function getProductsBrands($filter)
    {
        $product = new Product();

        $select = $this->getProductsSelect($filter);
        $select->join(array('b2' => 'brands'), 't.brand_id = b2.id', array('brand_name' => 'name', 'brand_url' => 'url'));
        $select->group('t.brand_id');

        return $product->fetchAll($select);
    }

    public function getProductsCategories($filter)
    {
        $product = new Product();

        $select = $this->getProductsSelect($filter);
        $select->join(['c2' => 'catalog'], 't.catalog_id = c2.id', ['category_name' => 'name', 'category_url' => 'url_path']);
        $select->group('t.catalog_id');

        return $product->fetchAll($select);
    }

    public function getProductsSelect($filters = [])
    {
        $filters = array_merge([
            'group'     => 't.id',
            'minPrice'  => false,
            'join'      => [],
            'columns'   => ['id', 'catalog_id', 'brand_id', 'name', 'subname', 'discount', 'url'],
        ], $filters);

        $columns = $filters['columns'];

        if($filters['minPrice']) {
            $stSelect = $this->getSql()->select()
                ->from(['ps2' => 'products_stock'])
                ->columns(['stock' => new Expression('IF(MAX(ps2.count) >= 1,1,0)')])
                ->where([
                    't.id' => new Expression('ps2.product_id'),
                ]);

            $siSelect = $this->getSql()->select()
                ->from(['ps' => 'products_size'])
                ->columns(['price' => new Expression('MIN(ps.price)')])
                ->where(['t.id' => new Expression('ps.depend')]);

            $rSelect = $this->getSql()->select()
                ->from(['pt' => 'products_taste'])
                ->columns(['coefficient' => new Expression('MIN(pt.coefficient)')])
                ->where([
                    't.id' => new Expression('pt.depend'),
                ]);

            $columns['price_base'] = $siSelect;
            $columns['stock'] = $stSelect;
            $columns['coefficient'] = $rSelect;
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'products']);

        /*if($filters['columns']) {
            $columns = array_merge($columns, $filters['columns']);
        }*/

        if($filters['size_id'] && $filters['taste_id']) {
            $select
                ->join(['pp' => 'products_size'],  new Expression('t.id = pp.depend AND pp.id = ' . $filters['size_id']), ['price_base' => 'price', 'size_id' => 'id', 'size' => 'name'])
                ->join(['pt' => 'products_taste'], new Expression('t.id = pt.depend AND pt.id = ' . $filters['taste_id']), ['taste_id' => 'id', 'coefficient', 'taste' => 'name'])
                ->join(['ps' => 'products_stock'], new Expression('t.id = ps.product_id AND ps.taste_id = ' . $filters['taste_id'] .' AND ps.size_id = ' . $filters['size_id']), ['stock' => 'count'], 'left')
                ->where([
                    'pp.id' => $filters['size_id'],
                    'pt.id' => $filters['taste_id']
                ]);
        }

        if($filters['yandexYmlFull']) { //Для Яндекс Маркета
            $select
                ->join(['ps'  => 'products_stock'], 't.id = ps.product_id', ['stock' => 'count', 'stock_id' => 'id'], 'left')
                ->join(['pss' => 'products_size'],  'pss.id = ps.size_id', [ 'size_id' => 'id', 'price_base' => 'price', 'size' => 'name'], 'left')
                ->join(['pst' => 'products_taste'], 'pst.id = ps.taste_id', ['taste_id' => 'id', 'coefficient', 'taste' => 'name'], 'left');
        }

        if($filters['optPrice']) { //Для оптовго прайса
            $select
                ->join(['ps'  => 'products_stock'], 't.id = ps.product_id', ['stock' => 'count', 'stock_id' => 'id'], 'left')
                ->join(['pss' => 'products_size'],  'pss.id = ps.size_id', [ 'size_id' => 'id', 'price_base' => 'price', 'size' => 'name'], 'left')
                ->join(['pst' => 'products_taste'], 'pst.id = ps.taste_id', ['taste_id' => 'id', 'coefficient', 'taste' => 'name'], 'left');
        }

        if($filters['group']) {
            $select->group($filters['group']);
        }

        if (in_array('reviews', $filters['join'])) {
            $columns['reviews'] = new Expression('(SELECT COUNT(pr2.id) FROM products_reviews AS pr2 WHERE t.id= pr2.product_id AND pr2.status = 1)');

            $select
                ->join(['pr' => 'products_reviews'], new Expression('t.id = pr.product_id AND pr.status = ' . Reviews::STATUS_VERIFIED), [
                    'stars'   => new Expression('AVG(pr.stars)'),
                ], 'left');
        }

        $select
            ->join(['pb' => 'brands'], new Expression('t.brand_id = pb.id AND pb.status = 1'), ['brand-name' => 'name', 'brand-id' => 'id']);

        if (in_array('brand', $filters['join']) || !empty($filters['brand'])) {
            if (in_array('brand-country', $filters['join'])) {
                $select
                    ->join(['pbc' => 'countries'], 'pbc.id = pb.country_id', ['brand-country-name' => 'name', 'brand-country-id' => 'id'], 'left');
            }
        }

        if (in_array('image', $filters['join'])) {
            $select
                ->join(['pi' => 'products_images'], 't.id = pi.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left');
        }

        if (in_array('catalog', $filters['join']) || !empty($filters['catalog_id'])) {
            $select
                ->join(['pc' => 'catalog'], 't.catalog_id = pc.id', ['catalog-id' => 'id', 'catalog-name' => 'name', 'catalog-url' => 'url'], 'left');
        }

        if(!empty($filters['brand'])) {
            $select->where(['pb.id' => $filters['brand']]);
        }

        if(!empty($filters['id'])) {
            $select->where(['t.id' => $filters['id']]);
        }

        if(!empty($filters['pid'])) {
            $select->where(['t.id' => $filters['pid']]);
        }

        if(!empty($filters['name'])) {
            $select->where(['t.name' => $filters['name']]);
        }

        if(!empty($filters['url'])) {
            $select->where(['t.url' => $filters['url']]);
        }

        if(!empty($filters['event'])) {
            $select->where(['event' => $filters['event']]);
        }

        if(!empty($filters['type'])) {
            $select
                ->join(['pty' => 'products_types'], 't.id = pty.depend', [], 'left')
                ->where(['pty.type_id' => $filters['type']]);
        } elseif(!empty($filters['catalog'])) {
            $select
                ->join(['pct' => 'catalog_types'], new Expression('pct.depend IN (' . implode(',', $filters['catalog']) . ')'), [])
                ->join(['pty' => 'products_types'], new Expression('pct.id = pty.type_id AND pty.depend = t.id'), []);
        }

        if(!empty($filters['onlyDiscount'])) {
            $select->where->greaterThan('discount', 0);
        }

        if($filters['price']) {
            $sSelect = $this->getSql()->select()
                ->from(['ps' => 'products_size'])
                ->columns(['price' => new Expression('MIN(ps.price)')])
                ->where(['t.id' => new Expression('ps.depend')]);

            $tSelect = $this->getSql()->select()
                ->from(['pt' => 'products_taste'])
                ->columns(['price' => new Expression('MIN(pt.coefficient)')])
                ->where(['t.id' => new Expression('pt.depend')]);

            $priceSql = '((' . $this->getSql()->buildSqlString($sSelect) . ') * (' . $this->getSql()->buildSqlString($tSelect) . ')) * (1 - t.discount / 100)';

            if (!empty($filters['price']['min'])) {
                $select->where->greaterThanOrEqualTo(new Expression($priceSql), $filters['price']['min']);
            }

            if (!empty($filters['price']['max'])) {
                $select->where->lessThanOrEqualTo(new Expression($priceSql), $filters['price']['max']);
            }
        }

        if($filters['onlyDiscounts']) {
            $select->where->notEqualTo('discount', 0);
        }


        if(!empty($filters['limit'])) {
            $select->limit($filters['limit']);
        }

        if(isset($filters['sort']) && $filters['sort'] !== null) {
            $select->order('stock DESC');

            switch($filters['sort']) {
                case 'discount':
                    $select->order('discount DESC');
                    break;
                case 'price_up':
                    $select->order('price_base ASC');
                    break;
                case 'price_down':
                    $select->order('price_base DESC');
                    break;
                case 'popularity':
                    $select
                        ->order('popularity DESC');
                    break;
                case 'rand':
                    $select->order(new Expression('RAND()'));
                    break;
                default:
                    $select->order('t.popularity DESC');
            };
        } elseif($filters['minPrice']) {
            $select
                ->order('stock DESC')
                ->order('popularity DESC');;
        }

        if($filters['query']) {
            $queries = Search::prepareQuery($filters['query']);
            $select
                ->join(['pta' => 'products_tags'], new Expression('pta.depend = t.id'), [], 'left');

            $where = '';
            foreach($queries as $query) {
                $where .= ($where ? ') OR (' : '((') . 't.name LIKE "' . ('%' . $query . '%') . '"';
            }
            $where .= ') OR (pta.name LIKE "%' . $filters['query'] . '%"))';

            $select->where($where);
        }

        $select->columns($columns);

        //((new Product())->setSelect($select)->dump());

        return $select;
    }
}