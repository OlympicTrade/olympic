<?php
namespace Blog\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ArticlesList extends AbstractHelper
{
    public function __invoke($articles, $mobile = false)
    {
        $html = '';

        if($mobile) {
            $html .= $this->renderMobile($articles);
        } else {
            $html .= $this->renderDesktop($articles);
        }

        if($articles instanceof Paginator) {
            $html .= $this->getView()->paginationControl($articles, 'Sliding', 'pagination-slide-auto', ['route' => 'application/pagination']);
        }

        return $html;
    }

    protected function renderDesktop($articles)
    {
        $view = $this->getView();
        $html = '';

        foreach($articles as $article) {
            $url = $article->getUrl();
            $html .=
                '<div class="article">'
                    .'<a class="pic" href="' . $url . '">'
                        .'<div class="date">'
                            .'<div class="month">'
                                .mb_substr($view->date($article->get('time_create'), ['month' => true, 'year'   => false, 'day'   => false]), 0, 4)
                            .'</div>'
                            .'<div class="day">'  . $view->date($article->get('time_create'), ['month' => false, 'year' => false, 'day' => true]) . '</div>'
                            .'<div class="year">' . $view->date($article->get('time_create'), ['month' => false, 'year' => true, 'day' => false]) . '</div>'
                        .'</div>'
                        .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                    .'</a>'
                    .'<div class="body">'
                        .'<div class="title"><a href="' . $url . '">' . $article->get('name') . '</a></div>'
                        .'<div class="desc">' . $view->subStr($article->get('preview'), 200) . '</div>'
                    .'</div>'
                .'</div>';
        }

        return $html;
    }

    protected function renderMobile($articles)
    {
        $view = $this->getView();

        $html = '';
        $i = 0;
        foreach($articles as $article) {
            $url = $article->getUrl();
            $blog = $article->getPlugin('blog');
            $i++;
            if($i == 1) {
                $html .=
                    '<div class="article">'
                        .'<a class="pic" href="' . $url . '">'
                            .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                        .'</a>'
                        .'<div class="body">'
                            .'<div class="title"><a href="' . $url . '">' . $article->get('name') . '</a></div>'
                            .'<div class="info">'
                                .'<a class="category" href="' . $blog->getUrl() . '">' . $blog->get('name') . '</a>'
                                .'<span class="date">' . $view->date($article->get('time_create')) . '</span>'
                            .'</div>'
                        .'</div>'
                    .'</div>';
            } else {
                $html .=
                    '<div class="article">'
                        .'<div class="body">'
                            .'<div class="title"><a href="' . $url . '">' . $article->get('name') . '</a></div>'
                            .'<div class="info">'
                                .'<a class="category" href="' . $blog->getUrl() . '">' . $blog->get('name') . '</a>'
                                .'<span class="date">' . $view->date($article->get('time_create')) . '</span>'
                            .'</div>'
                        .'</div>'
                    .'</div>';
            }
        }

        return $html;
    }
}