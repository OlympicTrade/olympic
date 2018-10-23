<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CatalogListShort extends AbstractHelper
{
    public function __invoke($catalogList, $options = [])
    {
        /*$options = array_merge([
            'class' => ''
        ], $options);

        $view = $this->getView();
        */

        if(!$catalogList->load()) {
            return '';
        }

        $html =
            '<div class="catalog-list-short">'
                .'<div class="category">';

        $first = true;
        foreach($catalogList as $category) {
            $children = $category->getChildren();

            if($children->count()) {
                if(!$first) {
                    $html .=
                        '<div class="clear"></div>'
                            .'</div><div class="category">';
                }

                $html .=
                    '<div class="header">' . $category->get('name') . '</div>';

                foreach ($children as $child) {
                    $html .= $this->renderCategory($child);
                }
            } else {
                $html .=
                    $this->renderCategory($category);
            }

            $first = false;
        }

        $html .=
                    '<div class="clear"></div>'
                .'</div>'
            .'</div>';

        return $html;
    }

    public function renderCategory($category)
    {
        $html =
            '<div class="item">'
                .'<a href="' . $category->getUrl() . '" class="pic">'
                    .'<img src="' . $category->getPlugin('image')->getImage('s') . '" alt="' . $category->get('name') . '">'
                .'</a>'
                .'<div class="info">'
                    .'<a href="' . $category->getUrl() . '" class="title">' . $category->get('name') . '</a>'
                    .'<ul class="sub">';

        foreach($category->getChildren('types') as $child) {
            $html .= '<li><a href="'. $child->getUrl() . '">' . $child->get('name') . '</a></li>';
        }

        foreach($category->getPlugin('types') as $type) {
            $html .= '<li><a href="'. $category->getUrl() . $type->get('url') . '/">' . $type->get('name') . '</a></li>';
        }

        $html .=
                    '</ul>'
                .'</div>'
            .'</div>';

        return $html;
    }
}