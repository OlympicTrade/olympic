<?php

namespace Discounts\Service;

use Aptero\Service\AbstractService;
use Discounts\Model\Discount;
use Zend\Form\Element\DateTime;

class DiscountsService extends AbstractService
{
    public function getActiveDiscount()
    {
        $discount = new Discount();
        $discount->select()->where
            ->nest()
            ->lessThanOrEqualTo('date_from', date('Y-m-d'))
            ->greaterThan('date_to', date('Y-m-d'))
            ->unnest();

        if(!$discount->load()) {
            $discount->clear();

            $dt = new \DateTime();

            $discount->select()
                ->order('date_to')
                ->where->lessThan('date_to', $dt->format('Y-m-d'));

            $discount->load();

            $discount->setVariables([
                'date_from'  => $dt->format('Y-m-d'),
                'date_to'    => $dt->modify('+7 days')->format('Y-m-d'),
            ])->save();

            $this->deactivateDiscount();
            $this->activateDiscount($discount);
        }

        return $discount;
    }

    protected function activateDiscount(Discount $discount)
    {
        foreach ($discount->getPlugin('products') as $product) {
            $product->set('discount', $product->get('discount_new'));
            $product->save();
        }
    }

    protected function deactivateDiscount()
    {
        $this->execute($this->getSql()->update('products')->set(['discount' => 0]));
    }
}