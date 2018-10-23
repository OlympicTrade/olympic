<?php
namespace Catalog\View\Helper;

use Zend\Form\Element\Select;
use Zend\View\Helper\AbstractHelper;

class OrderCartList extends AbstractHelper
{
    public function __invoke($order)
    {
        $view = $this->getView();
		
		$html =
            '<div class="cart-list">';

        foreach($order->getPlugin('cart') as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $url = '/goods/' . $product->getUrl() . '/';

            $html .=
                '<div class="product" data-product_id="' . $product->getId() . '" data-size_id="' . $cartRow->get('size_id') . '" data-taste_id="' . $cartRow->get('taste_id') . '">'
                    .'<a href="' . $url . '" class="pic">'
                        .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a class="name" href="' . $product->getUrl() . '">' . $product->get('name') . '</a>'
                        .'<div class="opts">'
                            .'<div class="row"><b>Вкус:</b> ' . $product->get('taste') . '</div>'
                            .'<div class="row"><b>Размер:</b> ' . $product->get('size') . '</div>'
                            .'<div class="row"><b>Стоимость:</b> '
                                . $view->price($cartRow->get('count') * $cartRow->get('price')) .' <i class="fa fa-ruble-sign"></i>'
                                . ($cartRow->get('count') > 1 ? ' (' . $cartRow->get('count') . ' x ' . $view->price($cartRow->get('price')) . '<i class="fa fa-ruble-sign"></i>)' : '')
                            . '</div>'
                        .'</div>'
                    .'</div>'
                .'</div>';
        }

        $html .=
                '</div>'
                .'<div class="clear"></div>'
            .'</div>';

        return $html;
    }
}