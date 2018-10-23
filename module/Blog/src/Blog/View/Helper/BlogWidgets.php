<?php
namespace Blog\View\Helper;

use Aptero\String\Numbers;
use Zend\View\Helper\AbstractHelper;

class BlogWidgets extends AbstractHelper
{
    public function __invoke($type, $options = [])
    {
        switch ($type) {
            case 'products':
                return $this->widgetProducts($options);
                break;
            case 'articles':
                return $this->widgetArticles($options);
                break;
            default:
                return '';
        }
    }

    public function widgetArticles($options = [])
    {
        $articles = $options['articles'];

        $html = '';
        $view = $this->getView();

        foreach ($articles as $article) {
            $url = $article->getUrl();

            $html .=
                '<div class="article">'
                    .'<div class="title"><a href="' . $url . '">' . $article->get('name') . '</a></div>'
                    .'<div class="info">'
                        .'<a class="pic" href="' . $url . '">'
                            .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                        . '</a>'
                        .'<div class="desc">' . $view->subStr($article->get('preview'), 100) . '</div>'
                        .'<div class="date">' . $view->date($article->get('time_create')) . '</div>'
                    .'</div>'
                .'</div>';
        }

        $html =
            '<div class="widget articles">'
                .'<div class="header">Тренды</div>'
                .'<div class="body">'
                    . $html
                    .'<div class="clear"></div>'
                .'</div>'
            .'</div>';

        return $html;
    }

    public function widgetProducts($options = [])
    {
        $products = $options['products'];

        if(!$products || !$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $view = $this->getView();
        $html = '';

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

            $html .=
                '<div class="product">'
                    .'<div class="top">'
                        .'<a href="' . $product->getUrl() . '" class="pic">'
                            .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                        .'</a>'
                        . $eventsHtml
                    .'</div>'

                    .'<div class="name-box">'
                        .'<a href="' . $product->getUrl() . '" class="name">' . $product->get('name') . '</a>'
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
        }

        $html =
            '<div class="widget products">'
                .'<div class="header"><a href="/catalog/event/">Акции</a></div>'
                .'<div class="body">'
                    . $html
                    .'<div class="clear"></div>'
                .'</div>'
            .'</div>';

        return $html;
    }
}