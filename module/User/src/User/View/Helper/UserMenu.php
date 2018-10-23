<?php
namespace User\View\Helper;

use User\Service\AuthService;
use Zend\View\Helper\AbstractHelper;

class UserMenu extends AbstractHelper
{
    public function __invoke()
    {
        $items = [
            ['name' => 'Корзина', 'url' => '/user/'],
            ['name' => 'Мои заказы', 'url' => '/orders/'],
            //['name' => 'Мой адрес', 'url' => '/user/address/'],
            //['name' => 'Персональные данные', 'url' => '/user/settings/'],
        ];

        $html =
            '<div class="profile-menu">'
                .'<ul>';

        foreach ($items as $item) {
            if($item['url'] == $_SERVER['REQUEST_URI']) {
                $html .= '<li><span>' . $item['name'] . '</span></li>';
            } else {
                $html .= '<li><a href="' . $item['url'] . '">' . $item['name'] . '</a></li>';
            }
        }

        $html .=
                '</ul>'
                .'<a href="/user/logout/" class="logout"><i class="fa fa-sign-out-alt"></i> Выйти</a>'
                .'<div class="clear"></div>'
            .'</div>';

        return $html;
    }
}