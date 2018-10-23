<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CartList extends AbstractHelper
{
    public function __invoke($cart, $price)
    {
        if(!$cart || !$cart->count()) {
            return '<div class="empty-list">Ваша корзина пуста</div>';
        }

        $view = $this->getView();

        $html =
            '<div class="cart-list">';

        if(!$view->isMobile()) {
            $html .=
                '<div class="order-info">'
                    .'<div class="sum">Полная стоимость: <span class="cart-price">' . $price . '</span> <i class="fa fa-ruble-sign"></i></div>'
                    .'<a href="/order/" class="btn order orange order-popup">Оформить заказ</a>'
                .'</div>';
        }

        $html .=
                '<div class="list">';

        foreach($cart as $cartRow) {
            $product = $cartRow->getPlugin('product');

            $html .=
                '<div class="product" data-id="' . $product->getId() . '" data-product_id="' . $product->getId() . '" data-size_id="' . $cartRow->get('size_id') . '" data-taste_id="' . $cartRow->get('taste_id') . '">'
                    .'<div class="pic">'
                        .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</div>'

                    .'<div class="info">'
                        .'<a class="name" href="' . $product->getUrl() . '">' . $product->get('name') . '</a>'
                        .'<div class="opts">'
                            .'<div class="row">Вкус: ' . $cartRow->get('taste') . '</div>'
                            .'<div class="row">Размер: ' . $cartRow->get('size') . '</div>'
                        .'</div>'
                    .'</div>'

                    .'<div class="price-box">'
                        .'<div class="sum"><span>' . $view->price($product->get('price') * $cartRow->get('count')) . '</span> <i class="fa fa-ruble-sign"></i></div>'
                        .'<div class="std-counter s">'
                            .'<input class="cart-count" value="' . $cartRow->get('count') . '" min="1" max="' . $cartRow->get('stock') . '">'
                            .'<div class="incr" title="В наличии больше нет"></div>'
                            .'<div class="decr"></div>'
                        .'</div>'
                        .'<div class="per-unit">' . $view->price($product->get('price')) . ' <i class="fa fa-ruble-sign"></i> за шт.</div>'
                    .'</div>'
                    .'<span class="cart-del del"></span>'
                .'</div>';
        }

        $html .=
            //$view->deliveryNotice($price)
                '</div>'
                .'<div class="order-info">'
                    .'<div class="sum">Полная стоимость: <span class="cart-price">' . $price . '</span> <i class="fa fa-ruble-sign"></i></div>'
                    .'<a href="/order/" class="btn order orange order-popup">Оформить заказ</a>'
                .'</div>'
            .'</div>';

        return $html;
    }
}