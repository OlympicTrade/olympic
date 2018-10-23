<?php
namespace Catalog\View\Helper;

use Aptero\String\Numbers;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class MobileProductsList extends AbstractHelper
{
    public function __invoke($products, $options = [])
    {
        if(!$products || !$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $view = $this->getView();

        $html =
            '<div class="products-list">';

        foreach($products as $product) {
            $img = $product->getPlugin('image')->getImage('s');

            if($discount = $product->get('discount')) {
                if($discount <= 10) {
                    $color = 'yellow';
                } elseif($discount <= 20) {
                    $color = 'orange';
                } else {
                    $color = 'red';
                }

                $eventsHtml =
                    '<div class="events">'
                    .'<div class="item ' . $color . '">-' . $discount . '%</div>'
                    .'</div>';
            } else {
                $eventsHtml = '';
            }

            $html .=
                '<a href="' . $product->getUrl() . '" class="product">'
                    .$eventsHtml
                    .'<div class="pic"><img src="' . $img . '" alt="' . $product->get('name') . '"></div>'
                    .'<div class="title">' . $product->get('name') . '</div>'
                    .'<div class="brand">' . $product->getPlugin('brand')->get('name') . '</div>'
                    . $view->stars($product->get('stars'))
                    .'<div class="order">'
                        .'<div class="price">' . $view->price($product->get('price')) . ' <i class="fa fa-ruble-sign"></i></div>';

            if(!$product->get('stock')) {
                $html .=
                    '<div class="out-of-stock">под запрос</div>';
            } elseif($product->get('discount')) {
                $html .=
                    '<div class="price-old">' . $view->price($product->get('price_old')) . ' <i class="fa fa-ruble-sign"></i></div>';
            }

            $html .=
                    '</div>'
                .'</a>';
        }

        $html .=
            '<div class="clear"></div>';

        if($products instanceof Paginator) {
            $html .=
                $view->paginationControl($products, 'Sliding', 'mobile-pagination-slide', ['route' => 'application/pagination']);
        }

        $html .=
            '</div>';

        return $html;
    }
}