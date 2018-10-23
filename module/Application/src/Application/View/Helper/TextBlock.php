<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class TextBlock extends AbstractHelper
{
    public function __invoke($text, $options)
    {
        $options = $options + ['open' => true];

        if(!$text) {
            return '';
        }

        if($options['open']) {
            $html =
                '<div class="text-block block gray">'
                    .'<div class="wrapper">'
                        .'<div class="text open">'
                            . $text
                        .'</div>'
                    .'</div>'
                .'</div>';
        } else {
            $html =
                '<div class="text-block block gray">'
                    .'<div class="wrapper">'
                        .'<div class="text">'
                            . $text
                            .'<div class="overlay"></div>'
                        .'</div>'

                        .'<span class="readmore">Читать далее</span>'
                    .'</div>'
                .'</div>';
        }

        return $html;
    }
}