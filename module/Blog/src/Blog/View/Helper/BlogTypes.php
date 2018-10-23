<?php
namespace Blog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BlogTypes extends AbstractHelper
{
    public function __invoke($catalog, $currentId, $options = [])
    {
        $html = '';

        foreach ($catalog as $category) {
            if($category->getId() == $currentId) {
                $html .= '<a class="type active" href="' . $category->getUrl() . '">' . $category->get('name') . '</a>';
            } else {
                $html .= '<a class="type" href="' . $category->getUrl() . '">' . $category->get('name') . '</a>';
            }
        }

        $html =
            '<div class="block blog-block blog-types">'
                .'<div class="wrapper">'
                    .'<a class="type' . (!$currentId ? ' active' : '') . '" href="/blog/">Все</a>'
                    .$html
                .'</div>'
            .'</div>';

        return $html;
    }
}