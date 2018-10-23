<?php
namespace Catalog\View\Helper;

use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductItem extends AbstractHelper
{
    public function __invoke($product, $options = [])
    {
        $options = array_merge([
            'list'          => 'Unknown',
        ], $options);

        $discount = $product->get('discount');
        if($discount) {
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

        $view = $this->getView();

        $optionsHtml = '<div class="options">';

        if($product->get('stock')) {
            $optionsHtml .=
                '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="item popup">'
                    .'<i class="fa fa-shopping-cart"></i>'
                .'</span>';
        }

        $optionsHtml .=
            '<a href="' . $product->getUrl() . '" class="item popup">'
                .'<i class="fa fa-eye"></i>'
            .'</a>';

        if(!$this->inCompare($product->getId())) {
            $optionsHtml .=
                '<a href="/compare/" class="item compare-add" data-pid="' . $product->getId() . '" data-cid="' . $product->get('catalog_id') . '">'
                .'<i class="fa fa-balance-scale"></i>'
                .'</a>';
        } else {
            $optionsHtml .=
                '<a href="/compare/" class="item compare-add active" data-pid="' . $product->getId() . '" data-cid="' . $product->get('catalog_id') . '">'
                .'<i class="fa fa-balance-scale"></i>'
                .'</a>';
        }

        $optionsHtml .= '</div>';

        $html =
            '<div class="product">'
                .'<div class="top">'
                    .'<a href="' . $product->getUrl() . '" class="pic">'
                        .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</a>'
                    . $eventsHtml
                    . $optionsHtml
                .'</div>'

                .'<a href="' . $product->getUrl() . '" class="name">' . $product->getPlugin('brand')->get('name') . ' ' . $product->get('name') . '</a>'

                .'<div class="price-box">'
                    .'<div class="price">' . $view->price($product->get('price')) . ' <i class="fa fa-ruble-sign"></i></div>'
                    .($product->get('discount') && $product->get('stock') ? '<div class="price-old">' . $view->price($product->get('price_old')) . '</span> <i class="fa fa-ruble-sign"></i></div>' : '')
                .'</div>'
            .'</div>';

        return $html;
    }

    protected $compareIds;
    protected function inCompare($productId)
    {
        if($this->compareIds === null) {
            $this->compareIds = [];
            if($cookie = json_decode($_COOKIE['compare-list'])) {
                foreach ($cookie as $row) {
                    $this->compareIds[] = $row->id;
                }
            }
        }

        return in_array($productId, $this->compareIds);
    }
}