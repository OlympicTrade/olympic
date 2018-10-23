<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class HeaderBlack extends AbstractHelper
{
    public function __invoke($options = [])
    {
        if($this->getView()->isMobile()) {
            return $this->mobile($options);
        } else {
            return $this->desktop($options);
        }
    }

    public function mobile($options = [])
    {
        $view = $this->getView();

        if(!isset($options['header'])) {
            $options['header'] = $view->header;
        }

        $html =
            '<h1 class="block">' . $options['header'] . '</h1>';

        return $html;
    }

    public function desktop($options = [])
    {
        $view = $this->getView();

        $html =
            '<div class="block main-header-black">'
                .'<div class="wrapper">';

        if(!isset($options['header'])) {
            $options['header'] = $view->header;
        }

        if($options['header']) {
            $html .= '<h1>' . $options['header'] . '</h1>';
        }

        if(empty($view->isAjax)) {
            if (!isset($options['breadcrumbs'])) {
                $options['breadcrumbs'] = $view->breadcrumbs;
            }

            $html .=
                '<div class="breadcrumbs">'
                    . $view->breadcrumbs($options['breadcrumbs'], ['delimiter' => '/', 'lastItem' => 'span', 'wrapper' => false])
                .'</div>';
        }

        if($options['html']) {
            $html .= $options['html'];
        }

        $html .=
                '</div>'
            .'</div>';

        return $html;
    }
}