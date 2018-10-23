<?php
namespace Catalog\View\Helper;

use Zend\Form\Element\Select;
use Zend\View\Helper\AbstractHelper;

class CatalogWidgets extends AbstractHelper
{
    public function __invoke($widget = '', $data = [])
    {
        switch ($widget) {
            case 'catalog':
                return $this->widgetCatalog($data);
                break;
            case 'brands':
                return $this->widgetBrands($data);
                break;
            case 'price':
                return $this->widgetPrice($data);
                break;
            case 'sort':
                return $this->widgetSort($data);
                break;
            default:
                break;
        }

        return '';
    }

    public function widgetPrice($data)
    {
        if($data['min'] == $data['max']) {
            return '';
        }
        
        $attrs = 'data-min="' . (int) $data['min'] . '" data-max="' . (int) $data['max'] . '"';

        if($data['data']['min']) {
            $attrs .= ' data-from="' . (int) $data['data']['min'] . '"';
        }

        if($data['data']['max']) {
            $attrs .= ' data-to="' . (int) $data['data']['max'] . '"';
        }

        $html =
            '<div class="widget">'
                .'<div class="header">Цена</div>'
                .'<div class="body">'
                    .'<input type="text" class="price-slider" name="price" value="" ' . $attrs . '>'
                .'</div>'
            .'</div>';
        
        return $html;
    }

    public function widgetSort($data)
    {
        $element = new Select('sort', [
            'options' => [
                'popularity' => 'Популярность',
                'price_down' => 'Цена по убыванию',
                'price_up'   => 'Цена по возрастанию',
                'discount'   => 'Скидка',
            ],
        ]);

        $element
            ->setAttribute('class', 'std-select2')
            ->setValue($data['data']);

        $html = $this->getView()->formElement($element);

        $html =
            '<div class="widget sort">'
                .'<div class="header">Сортировка</div>'
                .'<div class="body">'
                    . $html
                .'</div>'
            .'</div>';
        
        return $html;
    }
    
    public function widgetBrands($data)
    {
        $html = '';

        $i = 0;
        foreach ($data['brands'] as $brand) {
            $i++;

            if($i == 6) {
                $html .= '<div class="h-box">';
            }

            $checked = (isset($data['data']) && in_array($brand->getId(), $data['data'])) ? ' checked' : '';

            $html .=
                '<label class="checkbox">'
                    .'<input type="checkbox" name="brand"' . $checked . ' value="' . $brand->getId() . '"> ' . $brand->get('name')
                .'</label>';
        }

        if($i > 5) {
            $html .=
                '</div>'
                .'<span class="readmore">показать все</span>';
        } elseif($i <= 1) {
            return '';
        }

        $html =
            '<div class="widget brands">'
                .'<div class="header">Производитель</div>'
                .'<div class="body">'
                    . $html
                .'</div>'
            .'</div>';

        return $html;
    }

    public function widgetCatalog($data)
    {
        $category = $data['category'];

        /*if($category->get('parent')) {
            $activeId = $category->getId();
            $category = $category->getParent();
            $sub = $category->getChildren();
        } else {
            $activeId = 0;
            $sub = $category->getChildren();
        }*/

        $html =
            '<input class="update-url" type="hidden" value="' . $category->getUrl() . '">';

        $types = $category->getPlugin('types');
        $types->select()->order('sort');

        foreach ($category->getPlugin('types') as $type) {
            if($data['type'] && $type->get('url') == $data['type']->get('url')) {
                $class = ' class="active"';
            } else {
                $class = '';
            }

            $html .=
                '<div class="row">'
                    .'<a' . $class . ' href="' . $category->getUrl() . $type->get('url') . '/">' . $type->get('short_name') . '</a>'
                .'</div>';
        }

        $html =
            '<div class="widget catalog">'
                .'<div class="header">Категории</div>'
                .'<div class="body">'
                    . $html
                .'</div>'
            .'</div>';
        
        return $html;
    }
}