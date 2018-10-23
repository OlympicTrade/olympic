<?php
namespace ApplicationAdmin\View\Helper;

use Aptero\Db\Entity\EntityCollection;
use Zend\View\Helper\AbstractHelper;

class ContentList extends AbstractHelper {
    public function __invoke($content)
    {
        if(!($content instanceof EntityCollection)) {
            return $this->renderItem($content);
        }

        $html =
            '<div class="content-list">';

        foreach ($content as $item) {
            $html .=
                $this->renderItem($item);
        }

        $html .=
            '</div>';

        return $html;
    }

    protected function renderItem($item)
    {
        $html =
            '<div class="item" data-id="' . $item->getId() . '">'
                .'<div class="btns">'
                    .'<a href="/admin/application/content/edit/?id=' . $item->getId() . '" class="edit popup-form">Редактировать</a>'
                    .'<a data-id="' . $item->getId() . '" class="del">Удалить</a>'
                .'</div>';

        if($item->get('text')) {
            $html .=
                '<div class="text">'
                    .$item->get('text')
                .'</div>';
        }

        $images = $item->getPlugin('images');

        $html .=
            '<div class="pics">';

        foreach ($images as $image) {
            $html .=
                '<div class="pic">'
                    .'<a href="' . $image->getImage('hr') . '" class="popup-image">'
                        .'<img src="' . $image->getImage('a') . '">'
                    .'</a>'
                .'</div>';
        }

        $html .=
                '<div class="clear"></div>'
            .'</div>';

        $html .=
            '</div>';

        return $html;
    }
}