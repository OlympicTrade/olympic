<?php
namespace Catalog\View\Helper;

use Catalog\Model\Order;
use Delivery\Model\Delivery;
use Delivery\Model\Pickup;
use Zend\View\Helper\AbstractHelper;

class OrdersList extends AbstractHelper
{
    public function __invoke($orders)
    {
        if(!$orders->count()) {
            return '<div class="empty-list">У вас пока нет заказов</div>';
        }

        $html =
            '<div class="order-list">';

        $view = $this->getView();
        foreach($orders as $order) {
            $attrs = $order->getPlugin('attrs');

            $isPickup = $attrs->get('delivery') == 'pickup';

            /*
            if($isPickup) {
                $point = new Pickup();
                $point->setId($attrs->get('point'));
            }
            */

            $delivery = Delivery::getInstance()->deliveryTypes[$attrs->get('delivery')];
            
            $html .=
                '<div class="order" data-id="' . $order->getId() . '">'
                    .'<div class="header">'
                        .'<div class="or-name">Заказ №' . $order->getPublicId() . '</div>'
                        .'<div class="or-status status-' . $order->get('status') . '">' . Order::$processStatuses[$order->get('status')] . '</div>'
                        .'<div class="or-date">' . $view->date($order->get('time_create'), ['time' => true]) . '</div>'
                        .'<div class="or-price">Сумма: <b>' . $view->price($order->getPrice()) . '</b> <i class="fa fa-ruble-sign"></i></div>'
                        .'<div class="or-delivery">Доставка: ' . $delivery . '</div>'
                    .'</div>'
                    .'<div class="body"></div>'
                .'</div>';
        }

        $html .=
                '<div class="clear"></div>'
            .'</div>';


        return $html;
    }
}