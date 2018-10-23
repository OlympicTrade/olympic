<?php

namespace Catalog\Service;

use Application\Model\Module;
use Catalog\Model\Order;
use Aptero\Service\AbstractService;
use Aptero\String\Translit;
use CatalogAdmin\Model\Products;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;

class SyncService extends AbstractService
{
    protected $donor = 'http://myprotein';

    protected $tables = array(
        'catalog'   => 'catalog',
        'brands'    => 'products_brands',
        'products'  => 'products',
        'orders'    => 'orders',
        'cart'      => 'orders_cart',
    );

    /**
     * @var bool
     */
    protected $isChanges;

    public function updateProduct($productId)
    {
        $product = new Products();
        $product->select()->where(['t.mp_id' => $productId]);

        if(!$product->load()) {
            throw new \Exception('Cant find product with mp_id: ' . $productId);
        }

        $productId = $product->getId();

        $data = file_get_contents($this->donor . '/sync/product/?id=' . $product->get('mp_id'));

        if(!$data = Json::decode($data)) {
            throw new \Exception('Cant parse product with mp_id: ' . $productId);
        }

        if($data->data) {
            $data = $data->data;
        } else {
            return false;
        }

        $this->cleanStock($productId);

        //Size
        $baseSelect = $this->getSql()->select('products_size')
            ->columns(['id'])
            ->where(['depend' => $productId]);

        foreach($data->size as $size) {
            $select = clone $baseSelect;
            $select->where(['mp_id' => $size->id]);

            $result = $this->execute($select);

            if($result->name == $size->name && $result->weight == $size->weight && $result->price == $size->price) {
                continue;
            }

            if($this->execute($select)->count()) {
                $sql = $this->getSql()->update('products_size')
                    ->where(['mp_id' => $size->id])
                    ->set([
                        'name'   => $size->name,
                        'weight' => $size->weight,
                        'price'  => $size->price,
                    ]);
            } else {
                $sql = $this->getSql()->insert('products_size')
                    ->values([
                        'name'   => $size->name,
                        'price'  => $size->price,
                        'weight' => $size->weight,
                        'depend' => $productId,
                        'mp_id'  => $size->id,
                    ]);
            }
            $this->execute($sql);
        }

        //Price
        $baseSelect = $this->getSql()->select('products_taste')
            ->columns(['id', 'name', 'coefficient'])
            ->where(['depend' => $productId]);

        foreach($data->price as $price) {
            $select = clone $baseSelect;
            $select->where(['mp_id' => $price->id]);

            $result = $this->execute($select);

            if($result->name == $price->name && $result->coefficient == $price->coefficient) {
                continue;
            }

            if($result->count()) {
                $sql = $this->getSql()->update('products_taste')
                    ->where(['mp_id' => $price->id])
                    ->set([
                        'name'         => $price->name,
                        'coefficient'  => $price->coefficient,
                    ]);
            } else {
                $sql = $this->getSql()->insert('products_taste')
                    ->values([
                        'name'         => $price->name,
                        'depend'       => $productId,
                        'coefficient'  => $price->coefficient,
                        'mp_id'        => $price->id,
                    ]);
            }
            $this->execute($sql);
        }

        //Stock
        $ids = [];
        foreach($data->stock as $item) {
            $select = $this->getSql()->select(['pt' => 'products_taste'])
                ->columns(['taste_id' => 'id'])
                ->join(['ps' => 'products_size'], new Expression('ps.mp_id = ' . $item->size_id), ['size_id' => 'id'], 'left')
                ->join(['st' => 'products_stock'], new Expression('st.taste_id = pt.id AND st.size_id = ps.id'), ['count', 'stock_id' => 'id'], 'left')
                ->where(['pt.mp_id' => $item->taste_id]);

            $result = $this->execute($select)->current();

            if($result->count === $item->count) {
                $ids[] = $result->stock_id;
                continue;
            }

            if($result['stock_id']) {
                $sql = $this->getSql()->update('products_stock')
                    ->where(['id'  => $result['stock_id']])
                    ->set(['count' => $item->count]);
            } else {
                $sql = $this->getSql()->insert('products_stock')
                    ->values([
                        'product_id'   => $productId,
                        'taste_id'     => $result['taste_id'],
                        'size_id'      => $result['size_id'],
                        'count'        => $item->count,
                    ]);
            }
            $this->execute($sql);

            if($result['stock_id']) {
                $ids[] = $result['stock_id'];
            } else {
                $ids[] = $this->getSql()->getAdapter()->getDriver()->getLastGeneratedValue();
            }
        }

        if($ids) {
            $delete = $this->getSql()->delete('products_stock');
            $delete->where
                ->notIn('id', $ids)
                ->equalTo('product_id', $productId);
            $this->execute($delete);
        }

        die();
    }

    protected function cleanStock($productId)
    {
        $delete = $this->getSql()->delete('products_size')
            ->where([
                'depend'  => $productId,
                'mp_id'   => 0,
            ]);

        $this->execute($delete);

        $delete = $this->getSql()->delete('products_taste')
            ->where([
                'depend'  => $productId,
                'mp_id'   => 0,
            ]);

        $this->execute($delete);
    }

    static protected $updateTime;
    static public function getUpdateTime()
    {
        if(!self::$updateTime) {
            $module = new Module();
            $settings = $module->setModuleName('Catalog')->setSectionName('Products')->getPlugin('settings');
            self::$updateTime = $settings->get('update_time');
        }

        return self::$updateTime;
    }

    static public function getUpdateTime2()
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', self::getUpdateTime());
        return $dt->format('d.m.Y H:i');
    }

    public function updateTime()
    {
        $module = new Module();
        $settings = $module->setModuleName('Catalog')->setSectionName('Products')->getPlugin('settings');
        $settings->set('update_time', date("Y-m-d H:i:s"))->save();;
    }

    public function ordersXml()
    {
        $rootXML = new \SimpleXMLElement("<КоммерческаяИнформация></КоммерческаяИнформация>");
        $rootXML->addAttribute('ВерсияСхемы', '2.08');
        $rootXML->addAttribute('ДатаФормирования', date('Y-m-d'));

        $orders = Order::getEntityCollection();

        $orders->select()->where(array('sync' => 0));

        if(!$orders->load()->count()) {
            return $rootXML->asXML();
        }

        $delivery = array(
            'delivery' => 'Доставка',
            'pickup'   => 'Самовывоз',
        );

        $payment = array(
            'office'   => 'Оплата в офисе',
            'online'   => 'Online оплата',
        );

        foreach($orders as $order) {

            $comment = '';
            if($order->get('delivery')) {
                $comment .= '+ доставка ' . $order->get('delivery') . ' руб.';
            }

            $username = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('username'));
            $surname  = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('surname'));
            $address  = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('address'));

            $orderXML = $rootXML->addChild('Документ');
            $orderXML->addChild('Ид', $order->getId());
            $orderXML->addChild('Номер', $order->getId());
            $orderXML->addChild('ХозОперация', 'Заказ товара');
            $orderXML->addChild('Валюта', 'руб');
            $orderXML->addChild('Курс', '1');
            $orderXML->addChild('Сумма', $order->get('price'));

            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $order->get('time_create'));

            $orderXML->addChild('Дата', $date->format('Y-m-d'));
            $orderXML->addChild('Комментарий', $comment);

            $clientXML = $orderXML->addChild('Контрагенты')->addChild('Контрагент');
            $clientXML->addChild('Ид', $order->get('user_id'));
            $clientXML->addChild('Роль', 'Покупатель');
            $clientXML->addChild('Имя', $username);
            $clientXML->addChild('Фамилия', $surname);
            $clientXML->addChild('ПолноеНаименование', $username);
            $clientXML->addChild('Наименование', $username);

            $clientAddressXML = $clientXML->addChild('АдресРегистрации');
            $clientAddressXML->addChild('Представление', $address);

            if($order->get('delivery')) {
                $point = new \CatalogAdmin\Model\DeliveryPoint();
                $point->setId($order->getPlugin('attrs')->get('point'));
                $clientXML->addChild('Город', trim($point->get('name')));
            }

            $clientContactsXML = $clientXML->addChild('Контакты');

            if($order['phone']) {
                $clientContactXML = $clientContactsXML->addChild('Контакт');
                $clientContactXML->addChild('Тип', 'Телефон рабочий');
                $clientContactXML->addChild('Значение', $order->getPlugin('attrs')->get('phone'));
            }

            if($order['email']) {
                $clientContactXML = $clientContactsXML->addChild('Контакт');
                $clientContactXML->addChild('Тип', 'Почта');
                $clientContactXML->addChild('Значение', $order->getPlugin('attrs')->get('email'));
            }

            $addressFieldXML = $clientAddressXML->addChild('АдресноеПоле');
            $addressFieldXML->addChild('Тип', 'Адрес');
            $addressFieldXML->addChild('Значение', $address);

            $clientAgentXML = $clientXML->addChild('Представители')->addChild('Представитель')->addChild('Контрагент');

            $clientAgentXML->addChild('Отношение', 'Контактное лицо');
            $clientAgentXML->addChild('Наименование', $username);
            $clientAgentXML->addChild('Ид', 'b342955a9185c40132d4c1df6b30af2f');

            $orderXML->addChild('Время', $date->format('H:i:s'));

            $productsXML = $orderXML->addChild('Товары');

            foreach($order->getPlugin('cart') as $cartRow) {
                $product = $cartRow->getPlugin('product');
                $productXML = $productsXML->addChild('Товар');

                $productXML->addChild('Ид', $product->get('sync_id'));
                $productXML->addChild('ИдКаталога', $product->get('sync_id'));
                $productXML->addChild('Наименование', $product->get('name'));
                $productXML->addChild('ЦенаЗаЕдиницу', $cartRow->get('price'));
                $productXML->addChild('Количество', $cartRow->get('count'));
                $productXML->addChild('Сумма', $cartRow->get('price') * $cartRow->get('count'));

                $productPropsXML = $productXML->addChild('ЗначенияРеквизитов');
                $productPropertyXML = $productPropsXML->addChild('ЗначениеРеквизита');
                $productPropertyXML->addChild('Наименование', 'ВидНоменклатуры');
                $productPropertyXML->addChild('Значение', 'Товар');

                $productPropertyXML = $productPropsXML->addChild('ЗначениеРеквизита');
                $productPropertyXML->addChild('Наименование', 'ТипНоменклатуры');
                $productPropertyXML->addChild('Значение', 'Товар');
            }

            $orderPropertiesXML = $orderXML->addChild('ЗначенияРеквизитов');
            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Метод оплаты');
            $propertyXML->addChild('Значение', $payment[$order->getPlugin('attrs')->get('payment')]);

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Заказ оплачен');
            $propertyXML->addChild('Значение', ($order->get('paid') ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Отменен');
            $propertyXML->addChild('Значение', ($order['status'] == 20 ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Финальный статус');
            $propertyXML->addChild('Значение', ($order['status'] == 15 ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Статус заказа');
            $propertyXML->addChild('Значение', Order::$processStatuses[$order->get('status')]);

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Дата изменения статуса');
            $propertyXML->addChild('Значение', $order->get('time_update'));

            //$order->set('sync', 1)->save();
        }
        //header("Content-type: text/xml; charset=utf-8");die($rootXML->asXML());

        return $rootXML->asXML();
    }

    public function ordersParser($file = '')
    {

    }

    public function importParser($file = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        $sXml = simplexml_load_string(stripslashes(file_get_contents($file)));

        if((string) $sXml->Каталог['СодержитТолькоИзменения'] == 'false') {
            $this->isChanges = false;
        }

        //$this->addClassifier($sXml->Классификатор->Свойства->Свойство);
        $this->generateCatalog($sXml->Классификатор->Группы->Группа);
        $this->generateBrands($sXml->Классификатор->Производители->Производитель);
        $this->generateProducts($sXml->Каталог->Товары->Товар);

        return true;
    }

    protected $properties = array();

    public function addClassifier($properties)
    {
        if(isset($properties)) {
            foreach ($properties as $property) {
                if(!empty($property->ВариантыЗначений)) {
                    foreach($property->ВариантыЗначений->Справочник as $value) {
                        $this->properties[(string) $value->ИдЗначения] = (string) $value->Значение;
                    }
                }
            }
        }
    }

    protected $brands = array();
    public function generateBrands($brands)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['brands']);

        if(isset($brands)) {
            foreach ($brands as $brand) {
                $syncId = (string) $brand->Ид;

                //Check exists
                $select = $this->sql->select()
                    ->from(array('t' => $this->tables['brands']))
                    ->columns(array('id'))
                    ->where(array('sync_id' => $syncId));

                if($result = $this->execute($select)->current()) {
                    $brandId = $result['id'];
                } else {
                    $brandId = 0;
                }

                if(!$brandId) {
                    $insert = $this->sql->insert();

                    $url = Translit::url((string) $brand->Наименование, true);

                    $data = array(
                        'name'     => (string) $brand->Наименование,
                        'url'      => $url,
                        'sync_id'  => $syncId,
                    );

                    $insert->into($this->tables['brands'])
                        ->columns(array_keys($data))
                        ->values($data);

                    $this->execute($insert);

                    $brandId = $this->adapter->getDriver()->getLastGeneratedValue();
                }

                $this->brands[$syncId] = array(
                    //'name'  => (string) $brand->Наименование,
                    'id'    => $brandId,
                );
            }
        }
    }

    protected $catalog = array();
    public function generateCatalog($catalog, $parentId = 0, $urlPath = '')
    {
        //$this->adapter->query('TRUNCATE TABLE ' . $this->tables['catalog']);

        foreach ($catalog as $category)
        {
            $syncId = (string) $category->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('c' => $this->tables['catalog']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $catalogId = $result['id'];
            } else {
                $catalogId = 0;
            }

            $url = Translit::url((string) $category->Наименование, true);

            $cUrlPath = $urlPath . $url;

            $data = array(
                'name'      => trim($category->Наименование),
                'parent'    => $parentId,
            );

            if(!$catalogId) {
                $data = array_merge($data, array(
                    'sync_id'   => $syncId,
                    'url'       => $url,
                    'url_path'  => $cUrlPath,
                    'active'    => 1,
                ));
            }

            if($catalogId) {
                $update = $this->sql->update();

                $update->table($this->tables['catalog'])
                    ->set($data)
                    ->where(array('sync_id' => $syncId));

                $this->execute($update);
            } else {
                $insert = $this->sql->insert();

                $insert->into($this->tables['catalog'])
                    ->columns(array_keys($data))
                    ->values($data);

                $this->execute($insert);

                $catalogId = $this->adapter->getDriver()->getLastGeneratedValue();
            }

            if(isset($category->Группы)) {
                $this->generateCatalog($category->Группы->Группа, $catalogId, $cUrlPath . '/');
            }

            $this->catalog[$syncId] = array(
                //'name'  => (string) $category->Наименование,
                'id'    => $catalogId,
            );
        }
    }

    public function generateProducts($products)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['products']);

        foreach ($products as $product)
        {
            $syncId = (string) $product->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('p' => $this->tables['products']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $productId = $result['id'];
            } else {
                $productId = 0;
            }

            $url = Translit::url((string) $product->Наименование, true);


            $data = array(
                'name'        => trim($product->Наименование),
                'tags'        => trim($product->КлючевыеСлова),
                'article'     => trim($product->Артикул),
                'text'        => nl2br((string) $product->Описание),
            );

            if(isset($this->brands[(string) $product->Производители->Ид])) {
                $data['brand_id'] = $this->brands[(string) $product->Производители->Ид]['id'];
            }

            if(isset($this->catalog[(string) $product->Группы->Ид])) {
                $data['catalog_id'] = $this->catalog[(string) $product->Группы->Ид]['id'];
            }

            if(!$productId) {
                $data = array_merge($data, array(
                    'sync_id'   => $syncId,
                    'url'       => $url,
                    'active'    => 1,
                ));
            }

            if($productId) {
                $update = $this->sql->update();

                $update->table($this->tables['products'])
                    ->set($data)
                    ->where(array('sync_id' => $syncId));

                $this->execute($update);
            } else {
                $insert = $this->sql->insert();

                $insert->into($this->tables['products'])
                    ->columns(array_keys($data))
                    ->values($data);

                $this->execute($insert);

                $productId = $this->adapter->getDriver()->getLastGeneratedValue();
            }
        }
    }

    public function offersParser($file = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        $sXml = simplexml_load_string(stripslashes(file_get_contents($file)));

        if((string) $sXml->Каталог['СодержитТолькоИзменения'] == 'false') {
            $this->isChanges = false;
        }

        $this->generatePrice($sXml->ПакетПредложений->Предложения->Предложение);

        return true;
    }

    public function generatePrice($products)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['products']);

        foreach ($products as $product)
        {
            $syncId = (string) $product->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('p' => $this->tables['products']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $productId = $result['id'];
            } else {
                continue;
            }


            if(!count($product->Цены->Цена)) {
                continue;
            }

            $data = array(
                'count' => $product->Количество
            );

            foreach($product->Цены->Цена as $price) {
                if((string) $price->ИдТипаЦены == '11bd9731-4ebd-11e3-8632-50e549c4019a') {
                    $data['price_opt'] = $price->ЦенаЗаЕдиницу;
                }

                if((string) $price->ИдТипаЦены == 'c2aa2e66-93b7-11e3-8c70-50e549c4019a') {
                    $data['price'] = $price->ЦенаЗаЕдиницу;
                }
            }

            if(empty($data)) {
                continue;
            }

            $update = $this->sql->update();

            $update->table($this->tables['products'])
                ->set($data)
                ->where(array('sync_id' => $syncId));

            $this->execute($update);
        }

        $this->updateTime();
    }
}