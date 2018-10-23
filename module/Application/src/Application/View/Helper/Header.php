<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Header extends AbstractHelper
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

        if(!isset($options['header'])) {
            $options['header'] = $view->header;
        }

        if(empty($view->isAjax)) {
            if (!isset($options['breadcrumbs'])) {
                $options['breadcrumbs'] = $view->breadcrumbs;
            }

            $html = $view->breadcrumbs($options['breadcrumbs'], ['delimiter' => ' <i class="fa fa-angle-right"></i> ', 'lastItem' => 'span', 'class' => 'breadcrumbs']);
        }

        if($options['header']) {
            $html .= '<h1 class="std-header">' . $options['header'] . '</h1>';
        }

        return $html;
    }
}