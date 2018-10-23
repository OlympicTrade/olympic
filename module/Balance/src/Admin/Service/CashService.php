<?php
namespace BalanceAdmin\Service;

use Aptero\Service\Admin\TableService;
use BalanceAdmin\Model\BalanceFlow;
use Catalog\Model\Order;
use Catalog\Model\Product;
use CatalogAdmin\Model\Supplies;
use Zend\Db\Sql\Predicate\Expression;
use BalanceAdmin\Model\Balance;

class CashService extends TableService
{
    /**
     * @param \Aptero\Db\Entity\EntityCollection $collection
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($collection, $filters)
    {
        if($filters['search']) {
            $collection->select()->where->like('t.name', '%' . $filters['search'] . '%');
        }

        if($filters['type']) {
            $collection->select()->where(['type' => $filters['type']]);
        }

        if(!empty($filters['date_from'])) {
            $collection->select()->where->greaterThanOrEqualTo('date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $collection->select()->where->lessThanOrEqualTo('date', $filters['date_to']);
        }

        return $collection;
    }

    public function updateBalance()
    {
        $today = new \DateTime();

        $balance = new Balance();
        $balance->select()->where(array('date' => $today->format('Y-m-d')));
        $balance->load();

        $moneyCash = $this->getMoneyCash();

        $balance->setVariables(array(
            'date'  => $today->format('Y-m-d'),
            'products_cash'  => $this->getProductsCash() + $this->getSuppliesCash(),
            'orders_cash'    => $this->getOrdersCash(true),
            'money_cash'     => $moneyCash->cash + $moneyCash->orders,
            'orders_count'   => $this->getOrdersCount(),
        ));

        $balance->save();
    }

    public function getOrdersCount()
    {
        $today = new \DateTime();
        $dt = new \DateTime();
        $tomorrow  = $dt->modify('+1 day');

        $select = $this->getSql()->select()
            ->from(array('t' => 'orders'))
            ->columns(array('count' => new Expression('COUNT(*)')));

        $select->where
            ->greaterThanOrEqualTo('time_create', $today->format('Y-m-d'))
            ->lessThan('time_create', $tomorrow->format('Y-m-d'))
            ->nest()
            ->notEqualTo('status', Order::STATUS_CANCELED)
            ->unnest();

        $result = $this->execute($select)->current();

        return $result['count'];
    }

    public function getSuppliesCash()
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'supplies'))
            ->columns(array('cash' => new Expression('SUM(price)')))
            ->where(array('status' => array(
                Supplies::STATUS_NEW,
            )));

        $result = $this->execute($select)->current();

        return $result['cash'];
    }

    public function getOrdersCash($today = false)
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'orders'))
            ->columns(array('cash' => new Expression('SUM(income)')))
            ->where(array('status' => array(
                //Order::STATUS_NEW,
                Order::STATUS_PROCESSING,
                Order::STATUS_DELIVERY
            )));

        if($today) {
            $today = new \DateTime();
            $dt = new \DateTime();
            $tomorrow  = $dt->modify('+1 day');

            $select->where
                ->greaterThanOrEqualTo('time_create', $today->format('Y-m-d'))
                ->lessThan('time_create', $tomorrow->format('Y-m-d'));
        }

        $result = $this->execute($select)->current();

        return $result['cash'];
    }

    public function getMoneyCash()
    {
        $result = array(
            'cash'   => 0,
            'frozen' => 0,
            'credit' => 0,
        );

        $select = $this->getSql()->select()
            ->from(array('t' => 'balance_flow'))
            ->columns(array('price', 'type'))
            ->where(array('status' => 0));

        foreach($this->execute($select) as $row) {
            if($row['type'] == BalanceFlow::TYPE_CREDIT) {
                $result['credit'] += $row['price'];
            } elseif($row['type'] == BalanceFlow::TYPE_SUPPLIES) {
                $result['orders'] += $row['price'];
            } else {
                $result['cash'] += $row['price'];
            }
        }

        return (object) $result;
    }

    public function getProductsCash()
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'products'))
            ->columns(array('id', 'discount', 'name'))
            ->join(array('pp' => 'products_size'), 't.id = pp.depend', array('price_base' => 'price'))
            ->join(array('pt' => 'products_taste'), 't.id = pt.depend', array('coefficient'))
            ->join(array('ps' => 'products_stock'), new Expression('t.id = ps.product_id AND pt.id = ps.taste_id AND pp.id = ps.size_id'), array('count'));

        $select->where->greaterThanOrEqualTo('ps.count', 1);

        $products = Product::getEntityCollection();
        $products->setSelect($select);

        $price = 0;

        foreach($products as $product) {
            $count = $product->get('count');

            if(in_array($product->getId(), array(29, 44))) {
                $count = 5;
            }

            $price += $product->get('price') * $count;
        }

        return $price;
    }
}