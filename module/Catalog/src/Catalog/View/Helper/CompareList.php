<?php
namespace Catalog\View\Helper;

use Aptero\File\File;
use Aptero\String\Numbers;
use Catalog\Model\Product;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class CompareList extends AbstractHelper
{
    public function __invoke($data)
    {
        if(!$data) {
            return '<div class="empty-list">Нет товаров для сравнения</div>';
        }

        $html =
            '<div class="compare-list">';

        foreach ($data as $row) {
            $html .= $this->category($row);
        }

        $html .=
            '</div>';
        
        return $html;
    }
    
    public function category($data)
    {
        $products = $data['products'];
        $category = $data['category'];

        $html =
            '<table class="compare-table">'
            .'<tr>'
            .'<th class="t-header">' . $category->get('name') . '</th>';

        $props = [];
        $cols  = [];
        foreach ($products as $product) {
            $cols[] = $product->getId();

            $brand = $product->getPlugin('brand');

            $html .=
                '<th>'
                    .'<div class="product">'
                    .'<span class="compare-del del" data-id="' . $product->getId() . '"></span>'
    
                    .'<a href="' . $product->getUrl() . '" class="pic">'
                    .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</a>'
    
                    .'<a href="' . $product->getUrl() . '" class="title">' . $product->get('name') . '</a>'
                    .'<a href="' . $brand->getUrl() . '" class="brand">' . $brand->get('name') . '</a>'
    
                    .'<div class="order">'
                        .'<div class="price">' . $product->get('price') . ' <i class="fa fa-ruble-sign"></i></div>'
                        .($product->get('stock') ?
                            '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="btn orange popup"><i class="fa fa-shopping-cart"></i></span>'
                            :
                            '<span class="btn gray"><i class="fa fa-shopping-cart"></i></span>'
                        )
                        .'</div>'
                    .'</div>'
                .'</th>';

            foreach ($product->getPlugin('props') as $propsRS) {
                $pName = $propsRS->get('name');

                if(!array_key_exists($propsRS->get('name'), $props)) {
                    $props[$pName] = [];
                }

                foreach ($propsRS->getPlugin('rows', ['product_id' => $product->getId()]) as $row) {
                    if(!$row->get('compare')) { continue; }
                    $props[$pName][$row->get('key')][$product->getId()] = $row->get('val') . ' ' . $row->get('units');
                }
            }
        }

        $html .=
            '</tr>';

        $colsCount = count($cols);

        foreach ($category->getPlugin('props') as $propsRS) {
            $propsHtml = '';

            if($props[$propsRS->get('name')]) {
                foreach ($props[$propsRS->get('name')] as $propName => $rows) {
                    $propsHtml .=
                        '<tr>'
                        .'<td class="tal">' . $propName . '</td>';

                    for($i = 0; $i < $colsCount; $i++) {
                        $propsHtml .=
                            '<td>' . (isset($rows[$cols[$i]]) ? $rows[$cols[$i]] : '') . '</td>';
                    }

                    $propsHtml .=
                        '</tr>';
                }
            }

            if($propsHtml) {
                $html .=
                    '<tr>'
                        .'<td class="p-header" colspan="' . ($colsCount + 1) . '">' . $propsRS->get('name') . '</td>'
                    .'</tr>'
                    . $propsHtml;
            }
        }

        $html .=
            '</table>';

        return $html;
    }
}