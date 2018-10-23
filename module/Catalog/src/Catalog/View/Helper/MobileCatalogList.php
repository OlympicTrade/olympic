<?php
namespace Catalog\View\Helper;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

use Catalog\Model\Catalog;
use Zend\View\Helper\AbstractHelper;

class MobileCatalogList extends AbstractHelper
{
    public function __invoke($catalog)
    {
        if(!$catalog->load()) {
            return '';
        }

        $html =
            '<ul class="catalog-list">';

        foreach ($catalog as $category) {
            $html .=
                '<li>'
                    .'<a href="' . $category->getUrl() . '" class="item">'
                        .'<div class="pic"><img src="' . $category->getPlugin('image')->getImage('m') . '" alt=""></div>'
                        . $category->get('name')
                    .'</a>';

            if($children = $category->getChildren()->load()) {
                $html .= '<ul>';

                foreach ($category->getChildren() as $child) {
                    $html .= '<li><a href="' . $child->getUrl() . '">' . $child->get('name') . '</a></li>';
                }

                $html .= '</ul>';
            }

            $html .=
                '</li>';
        }

        $html .=
            '</ul>';

        return $html;
    }
}