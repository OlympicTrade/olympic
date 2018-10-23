<?php
namespace Catalog\View\Helper;

use Aptero\File\File;
use Aptero\String\Numbers;
use Catalog\Model\Product;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductTabs extends AbstractHelper
{
    public function __invoke($product, $tab)
    {
        $html = '';

        switch($tab) {
            case 'default':
                $html .= $this->renderMain($product);
                break;
            case 'text':
                $html .= $this->renderText($product);
                break;
            case 'video':
                $html .= $this->renderVideo($product);
                break;
            case 'articles':
                $html .= $this->renderArticles($product);
                break;
            case 'props':
                $html .= $this->renderProps($product);
                break;
            case 'reviews':
                $html .= $this->renderReviews($product);
                break;
            case 'composition':
                $html .= $this->renderComposition($product);
                break;
            case 'tab1':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'tab2':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'tab3':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'certificate':
                $html .= $this->renderFile($product, 'certificate');
                break;
            case 'instruction':
                $html .= $this->renderFile($product, 'instruction');
                break;
            default:
        }

        return $html;
    }

    protected function renderFile($product, $pluginName)
    {
        if(!$product->getPlugin($pluginName)->hasFile()) {
            return '';
        }

        $file = $product->getPlugin($pluginName)->getFile();
        $extension = File::getFileType($file);

        if(in_array($extension, array('.jpg', '.png', '.jpeg', '.gif'))) {
            return '<img src="' . $file . '" alt="">';
        }

        if($extension == '.pdf') {
            return '<iframe src="' . $file . '" style="width: 100%; height: 800px"></iframe>';
        }

        return '<a href="' . $file . '" download>Скачать файл</iframe>';
    }

    protected function renderComposition($product)
    {

        $blocks = $product->getProps();

        if(!$blocks->count()) {
            return '';
        }

        switch ($product->get('units')) {
            case Product::GUNIT_CAPSULES:
                $defaultSize = 'на 1 капсулу';
                break;
            case Product::GUNIT_TABLETS:
                $defaultSize = 'на 1 таблетоку';
                break;
            case Product::GUNIT_WEIGHT:
                $defaultSize = 'на 100 грамм';
                break;
        }

        $html =
            '<div class="composition-trigger">'
                .'<div class="title">Cостав</div>'
                .'<select class="std-select2" data-units="' . $product->get('units') . '">'
                    .'<option value="1">' . $defaultSize . '</option>';

        $attrs = $product->getPlugin('attrs');
        if($attrs->get('portion')) {
            switch ($product->get('units')) {
                case Product::GUNIT_CAPSULES:
                    $html .=
                        '<option value="' . $attrs->get('portion') . '">'
                            .$attrs->get('portion')
                            .' ' . Numbers::declension($attrs->get('portion'), array('капсула', 'капсулы', 'капсул'))
                            .' (рекоменд. порция)'
                        .'</option>';
                    break;
                case Product::GUNIT_TABLETS:
                    $html .=
                        '<option value="' . $attrs->get('portion') . '">'
                            .$attrs->get('portion')
                            .' ' . Numbers::declension($attrs->get('portion'), array('таблетка', 'таблетки', 'таблеток'))
                            .' (рекоменд. порция)'
                        .'</option>';
                    break;
                case Product::GUNIT_WEIGHT:
                    $html .=
                        '<option value="' . ($attrs->get('portion') / 100) . '">' . $attrs->get('portion') . ' г. (одна порция)</option>';
                    break;
            }
        }

        foreach ($product->getPlugin('size') as $size) {
            $val = $product->get('units') == Product::GUNIT_WEIGHT ? $size->get('size') / 100 : $size->get('size');

            $html .=
                '<option value="' . $val . '" data-price="' . $size->get('price') . '">' . $size->get('name') . '</option>';
        }

        $html .=
                '</select>'
            .'</div>';

        $html .=
            '<div class="composition-list">';
        
        foreach ($blocks as $block) {
            $propsHtml = '';
            foreach ($block->getPlugin('rows', ['product_id' => $product->getId()]) as $row) {
                if($row->get('val') === '' || $row->get('key') === '') {
					$propsHtml .=
                    '<div class="row">'
                        . ($row->get('key') ? $row->get('key') : $row->get('val'))
                    .'</div>';
				} else {
                    $propsHtml .=
                    '<div class="row pr' . (15 < mb_strlen($row->get('val')) ? ' long' : '') . '">'
                        .'<div class="key">' . $row->get('key') . '</div>'
                        .'<div class="val' . ($row->get('multiplier') ? ' nbr' : '') . '" data-val="' . $row->get('val') . '" data-units="' . $row->get('units') . '">'
                            .'<span class="w">' . $row->get('val') . ' ' . $row->get('units') . '</span>'
                            .' <span class="p"></span> '
                        . '</div>'
                    .'</div>';
                }
            }

            if(!$propsHtml) {
                continue;
            }

            $html .=
                '<div class="list' . ($block->get('name') == 'Пищевая ценность' ? ' short' : '') . '">'
                    .'<div class="title">' . $block->get('name') . '</div>'
                    . $propsHtml
                .'</div>';
        }
        
        $html .=
                '<div class="clear"></div>'
            .'</div>';

        return $html;
    }

    protected function renderReviews($product)
    {
        $url = '/catalog/add-review/?pid=' . $product->getId();

        $reviews = $product->getReviews();

        if(!$reviews->count()) {
            return
                '<div class="products-review">'
                    .'<div class="empty">Пока никто не оставлял отзывы. Вы можете быть первым. <a class="add-comment popup" href="' . $url . '">Оставить отзыв</a></div>'
                .'</div>';
        }

        $html =
            '<div class="products-review">'
                .'<div class="summary">'
                    .'<span class="btn yellow add-comment popup" href="' . $url . '">Оставить отзыв</span>'
                .'</div>'
                .'<div class="list">';

        $view = $this->getView();

        foreach($reviews as $review) {
            $html .=
                '<div class="review">'
                    .'<div class="info">'
                        .'<div class="name">' . $review->get('name') .  '</div>'
                        .'<div class="date">' . $view->date($review->get('time_create')) . '</div>'
                        . $view->stars($review->get('stars'))
                    .'</div>'
                    .'<div class="text">' . nl2br($review->get('review'));

            if($review->get('answer')) {
                $html .=
                    '<div class="answer">'
                        .'<div class="name"><b>Olympic Torch</b></div>'
                        .nl2br($review->get('answer'))
                    .'</div>';
            }

            $html .=
                    '</div>'
                .'</div>';
        }

        $html .=
                '</div>'
                .'<div class="clear"></div>'
            .'</div>';

        return $html;
    }

    protected function renderTab($product, $tab)
    {
        return '<div class="std-text">' . $product->getPlugin('attrs')->get($tab . 'text') . '</div>';
    }

    protected function renderMain($product)
    {
        $html =
            '<div class="main-tab">'
                .'<div class="col-left std-text">'
                    . $this->getView()->productText($product->get('text'))
                .'</div>'
                .'<div class="col-right std-text">';


        $blocks = $product->getProps(['name' => 'Пищевая ценность']);

        foreach ($blocks as $block) {
            $propsHtml = '';
            foreach ($block->getPlugin('rows', ['product_id' => $product->getId()]) as $row) {
                if($row->get('val') === '' || $row->get('key') === '') {
                    continue;
                }

                $propsHtml .=
                    '<div class="row">'
                        .'<div class="key">' . $row->get('key') . '</div>'
                        .'<div class="val' . ($row->get('multiplier') ? ' nbr' : '') . '" data-val="' . $row->get('val') . '" data-units="' . $row->get('units') . '">'
                            .'<span class="w">' . $row->get('val') . ' ' . $row->get('units') . '</span>'
                        .'</div>'
                    .'</div>';
            }

            if(!$propsHtml) {
                continue;
            }

            $name = $block->get('name');
            $name .= $name == 'Пищевая ценность' ? ' (100г)' : '';

            $html .=
                '<div class="widget composition">'
                    .'<div class="header">' . $name . '</div>'
                    .'<div class="body">'
                        . $propsHtml
                        .'<a href="' . $product->getUrl() . 'composition/" data-tab="composition" class="readmore">Показать подробный состав</a>'
                    .'</div>'
                .'</div>';
        }

        $reviewsHtml = '';
        $view = $this->getView();
        $reviews = $product->getReviews(['limit' => 3]);
        foreach($reviews as $review) {
            $reviewsHtml .=
                '<div class="review">'
                    .'<div class="info">'
                        .'<div class="name">' . $review->get('name') .  '</div>'
                        .'<div class="date">' . $view->date($review->get('time_create')) . '</div>'
                        . $view->stars($review->get('stars'))
                    .'</div>'
                    .'<div class="text">' . nl2br($review->get('review')) . '</div>'
                .'</div>';
        }

        if($reviewsHtml) {
            $html .=
                '<div class="widget reviews">'
                    .'<div class="header">Последние отзывы</div>'
                    .'<div class="body">'
                        . $reviewsHtml
                        .'<a href="' . $product->getUrl() . 'reviews/" data-tab="reviews" class="readmore">Показать все ' . $view->declension($reviews->count(), ['отзыв', 'отзыва', 'отзывов']) . ' </a>'
                    .'</div>'
                .'</div>';
        }

        $html .=
                 '</div>'
                .'<div class="clear"></div>'
            .'</div>';

        return $html;
    }

    protected function renderText($product)
    {
        $html =
            '<div class="std-text">'
                . $this->getView()->productText($product->get('text'))
            .'</div>';

        return $html;
    }

    protected function renderVideo($product)
    {
        return $this->getView()->video($product->get('video'));
    }

    protected function renderArticles($product)
    {
        $articles = $product->getPlugin('articles');

        if(!$articles->count()) { return ''; }

        $html = '<div class="products-articles">';

        $view = $this->getView();
        foreach($articles as $article) {
            $html .=
                '<div class="article">'
                    .'<a href="' . $article->getUrl() . '" class="pic">'
                        .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $article->getUrl() . '" class="title">' . $article->get('name') . '</a>'
                        .'<div class="desc">' . $view->subStr($article->get('preview'), 250) . '</div>'
                        .'<a href="' . $article->getUrl() . '" class="read">Читать далее</a>'
                    .'</div>'
                    .'<div class="clear"></div>'
                .'</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderProps($product)
    {
        $props = $product->getPlugin('props');

        if(!$props->count()) {
            return '';
        }

        $html =
            '<div class="props">'
                .'<div class="title">Характеристики</div>'
                .'<div class="cols">';

        foreach($props as $prop) {
            $html .=
                '<div class="prop">'
                    .'<span class="label">' . $prop->get('key') . ':</span> '
                    . $prop->get('val')
                .'</div>';
        }

        $html .=
                '</div>'
            .'</div>';

        return $html;
    }

}