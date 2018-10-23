<?php
namespace Application\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Json\Json;

class Breadcrumbs extends AbstractTranslatorHelper
{
    public function __invoke($crumbs, $options = [])
    {
        $options = array_merge(array(
            'delimiter' => ' <i class="fa fa-angle-right"></i> ',
            'allLinks'  => false,
            'lastItem'  => 'span',
            'wrapper'   => true,
        ), $options);

        $translator = $this->getTranslator();

        $html = '';

        if($options['wrapper']) {
            $html .=
                '<div class="block std-breadcrumbs">'
                    .'<div class="wrapper">';
        }

        $count = $options['allLinks'] ? count($crumbs) : count($crumbs) - 1;

        for($i = 0; $i < $count; $i++) {
            $crumb = $crumbs[$i];

            $html .=
                '<div class="item">'
                    .'<a href="' . $crumbs[$i]['url'] . '">'
                        . $translator->translate($crumb['name'])
                    .'</a>';

            if(isset($crumb['options'])) {
                $html .=
                    '<div class="options">';

                foreach($crumb['options'] as $url => $name) {
                    $html .= '<a href="' . $url . '">' . $name . '</a>';
                }

                $html .=
                    '</div>';
            }

            $html .=
                '</div>'
                . ($i + 1 < $count ?  '<div class="sep">' . $options['delimiter'] . '</div>' : '');
        }

        if(!$options['allLinks'] && $options['lastItem']) {
            $html .= '<div class="sep">' . $options['delimiter'] . '</div><div class="item"><' . $options['lastItem'] . ' class="crumb">' .  $crumbs[$i]['name'] . '</' . $options['lastItem'] . '></div>';
        }

        if($options['wrapper']) {
            $html .=
                    '</div>'
                .'</div>';
        }

        //Json LD
        $ldCrumbs = array();
        for($i = 0; $i < count($crumbs); $i++) {
            $ldCrumbs[] = (object) array(
                '@type'    => 'ListItem',
                'position' => ($i + 1),
                'item' => (object) array(
                    '@id'  => 'http://' . $_SERVER['HTTP_HOST'] . $crumbs[$i]['url'],
                    'name' => $translator->translate($crumbs[$i]['name'])
                )
            );
        }

        $jsonLd = (object) array(
            '@context'     => 'http://schema.org',
            '@type'        => 'BreadcrumbList',
            'itemListElement' => $ldCrumbs
        );

        $html .= '<script type="application/ld+json">' . Json::encode($jsonLd) . '</script>';

        return $html;
    }
}