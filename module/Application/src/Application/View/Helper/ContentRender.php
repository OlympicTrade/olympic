<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class ContentRender extends AbstractHelper
{
    public function __invoke($blocks)
    {
        $html = '<div class="content-block">';

        foreach($blocks as $block) {
            $html .=
                 $this->text($block)
                .$this->images($block);
        }

        $html .= '</div>';

        return $html;
    }

    protected function text($block) {
        $html = '';

        $html .=
            '<div class="cb-text std-text">' . $block->get('text') . '</div>';

        return $html;
    }

    protected function images($block) {
        $html = '';

        $images = $block->getPlugin('images');

        $image = $images->rewind()->current();

        $html .=
            '<div class="cb-gallery">'
                .'<a href="' . $image->getImage('hr') . '" class="pic" data-fancybox="images">'
                    .'<img src="' . $image->getImage('m') . '" alt="">';

        if($image->get('desc')) {
            $html .=
                '<div class="desc">' . $image->get('desc') . '</div>';
        }

        $html .=
            '</a>';

        /*foreach ($block->getPlugin('images') as $image) {
            $html .=
                '<img src="' . $image->getImage('m') . '" alt="">';
        }*/

        $html .=
            '</div>';

        return $html;
    }
}