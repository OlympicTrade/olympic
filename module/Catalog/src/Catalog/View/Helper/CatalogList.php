<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CatalogList extends AbstractHelper
{
    public function __invoke($catalogList, $options = [])
    {
        /*$options = array_merge([
            'class' => ''
        ], $options);

        $view = $this->getView();
        */

        $html = '<div class="catalog-list">';

        foreach($catalogList as $category) {
            $html .=
                '<div class="item">'
                    .'<a href="' . $category->getUrl() . '" class="pic">'
                        .'<img src="' . $category->getPlugin('image')->getImage('s') . '" alt="' . $category->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $category->getUrl() . '" class="title">' . $category->get('name') . '</a>'
                        .'<ul class="sub">';

            $i = 0;
            foreach($category->getChildren('types') as $child) {
                $i++;
                if($i > 5) { break; }
                $html .= '<li><a href="'. $child->getUrl() . '">' . $child->get('name') . '</a></li>';
            }

            foreach($category->getPlugin('types') as $type) {
                $i++;
                if($i > 5) { break; }
                $html .= '<li><a href="'. $category->getUrl() . $type->get('url') . '/">' . $type->get('name') . '</a></li>';
            }

            $html .=
                        '</ul>'
                        .'<a href="' . $category->getUrl() . '" class="readmore">Показать все</a>'
                    .'</div>'
                .'</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}