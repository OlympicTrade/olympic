<?php
namespace Catalog\View\Helper;

use Aptero\String\Numbers;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductsList extends AbstractHelper
{
    public function __invoke($products, $options = [])
    {
        if(!$products || !$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $options = array_merge([
            'pagination'    => true,
            'list'          => 'Unknown',
        ], $options);
        
        $view = $this->getView();
        $html = '';
        $script = '';

        $i = 0;
        foreach($products as $product) {
            $i++;

            $img = $product->getPlugin('image')->getImage('s');
            $reviews = $product->get('reviews');

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

            $optionsHtml = '<div class="options">';

            if($product->get('stock')) {
                $optionsHtml .=
                    '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="item popup">'
                        .'<i class="fa fa-shopping-cart"></i> Добавить в корзину'
                    .'</span>';
            } else {
                $optionsHtml .=
                    '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="item popup">'
                        .'<i class="fa fa-shopping-cart"></i> Предзаказ'
                    .'</span>';
            }

            $optionsHtml .=
                '<a href="' . $product->getUrl() . '" class="item products-popup">'
                    .'<i class="fa fa-eye"></i> Быстрый просмотр'
                .'</a>';

            if(!$product->inCompare()) {
                $optionsHtml .=
                    '<a href="/compare/" class="item compare-add" data-pid="' . $product->getId() . '" data-cid="' . $product->get('catalog_id') . '">'
                        .'<i class="fa fa-balance-scale"></i> <span>Добавить к сравнению</span>'
                    .'</a>';
            } else {
                $optionsHtml .=
                    '<a href="/compare/" class="item compare-add active" data-pid="' . $product->getId() . '" data-cid="' . $product->get('catalog_id') . '">'
                        .'<i class="fa fa-balance-scale"></i> <span>Добавлен к сравнению</span>'
                    .'</a>';
            }

            $name = $product->getPlugin('brand')->get('name') . ' ' . $product->get('name');

            if($product->get('subname')) {
                $subname = '<a href="' . $product->getUrl() . '" class="subname">' . $product->get('subname') . '</a>';
            } else {
                $subname = '';
            }

            $optionsHtml .= '</div>';

            $html .=
                '<div class="product">'
                    .'<div class="top">'
                        .'<a href="' . $product->getUrl() . '" class="pic">'
                            .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                        .'</a>'
                        . $eventsHtml
                        . $optionsHtml
                    .'</div>'

                    .'<div class="name-box">'
                        .'<a href="' . $product->getUrl() . '" class="name">' . $name . '</a>'
                        . $subname
                    .'</div>'

                    .'<div class="reviews-box">'
                        .$view->stars($product->get('stars'))
                        .'<a href="' . $product->getUrl() . 'reviews/#product-tabs" class="reviews">'
                        . ($reviews ? $reviews . ' ' . Numbers::declension($reviews, ['отзыв', 'отзыва', 'отзывов']) : '')
                        .'</a>'
                    .'</div>'

                    .'<div class="price-box">'
                        .'<div class="price">' . $view->price($product->get('price')) . ' <i class="fa fa-ruble-sign"></i></div>'
                        .($product->get('discount') && $product->get('stock') ? '<div class="price-old">' . $view->price($product->get('price_old')) . '</span> <i class="fa fa-ruble-sign"></i></div>' : '')
                        .(!$product->get('stock') ? '<div class="out-of-stock">под заказ</div>' : '')
                    .'</div>'
                .'</div>';
            /*$script .=
                'ga("ec:addImpression", {'
                    .'"id": "' . $product->getId() . '",'
                    .'"name": "' . $product->get('name') . '",'
                    .'"category": "' . $product->getPlugin('catalog')->get('name') . '",'
                    .'"brand": "' . $product->getPlugin('brand')->get('name') . '",'
                    .'"list": "List",'
                    .'"position": ' . $i
                .'});';*/
        }

        if($products instanceof Paginator && $options['pagination']) {
            $html .=
                $view->paginationControl($products, 'Sliding', 'pagination-slide-auto', array('route' => 'application/pagination'));
        }

        //$html .= '<script>' . $script . '</script>';

        return $html;
    }
}