<?php
namespace Blog\View\Helper;

use Blog\Model\ExerciseTypes;
use Zend\View\Helper\AbstractHelper;

class ExercisesList extends AbstractHelper
{
    public function __invoke($exercises, $male = true)
    {
        $html = '';

        foreach ($exercises as $exercise) {
            $url = $exercise->getUrl();

            $html .=
                '<div class="item">'
                    .'<div class="pics">';

            $images = $exercise->getPlugin('images', ['sex' => ($male ? 'm' : 'w'), 'type' => 'photo']);

            foreach ($images as $image) {
                $html .=
                    '<a href="' . $url . '" class="pic">'
                        .'<img src="' . $image->getImage('p_s') . '" alt="">'
                    .'</a>';
            }

            $html .=
                '</div>'
                .'<div class="info">'
                    .'<div class="title"><a href="' . $url . '">' . $exercise->get('name') . '</a></div>'
                    .'<div class="tags">';

            $typesArr = [];
            $typeId = ExerciseTypes::TYPE_MUSCLES;
            foreach ($exercise->getPlugin('types', ['type_id' => $typeId], true) as $type) {
                $typesArr[] = '<a href="' . $type->getUrl() . '">' . $type->get('name') . '</a>';
            }
            $html .=
                '<div class="row">' . ExerciseTypes::$types[$typeId] . ': ' . implode(', ', $typesArr) . '</div>';

            $typesArr = [];
            $typeId = ExerciseTypes::TYPE_INVENTORY;
            foreach ($exercise->getPlugin('types', ['type_id' => $typeId], true) as $type) {
                $typesArr[] = '<a href="' . $type->getUrl() . '">' . $type->get('name') . '</a>';
            }
            $html .=
                '<div class="row">' . ExerciseTypes::$types[$typeId] . ': ' . implode(', ', $typesArr) . '</div>';

            $html .=
                        '</div>'
                    .'</div>'
                    .'<div class="rating">'
                        .'<div class="lb">Рейтинг</div>'
                        .'<div class="nbr">' . ($exercise->get('rating') / 10) . '</div>'
                    .'</div>'
                    .'<div class="clear"></div>'
                .'</div>';
        }

        return $html;
    }
}