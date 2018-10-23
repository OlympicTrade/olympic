<?php
namespace Blog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Blog\Model\Blog;
use Zend\View\Model\JsonModel;

class MobileController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $blogService = $this->getBlogService();
        $url = $this->params()->fromRoute('url');
        if($url) {

            $blog = $blogService->getBlog(['url' => $url])->load();

            if($blog) {
                return $this->blogAction($blog);
            }

            $subUrl = substr($url, strrpos($url, '/') + 1);
            $blogUrl = substr($url, 0, strrpos($url, '/'));

            $blog = $blogService->getBlog(['url' => $blogUrl]);
            if(!$blog->load()) {
                return $this->send404();
            }

            $type = $blogService->getType(['url' => $subUrl, 'blog_id' => $blog->getId()]);
            if($type) {
                return $this->blogAction($blog, ['type' => $type]);
            }

            return $this->send404();
        }

        $page = $this->params()->fromQuery('page', 1);
        $filters = [
            'itemsPerPage'  => 5
        ];
        $articles = $this->getBlogService()->getPaginator($page, $filters);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];
            $viewHelper = $this->getViewHelper('articlesList');
            $resp['html']['items'] = $viewHelper($articles, true);
            return new JsonModel($resp);
        }

        $view = $this->generate('/blog/');

        $view->setVariables([
                //'header'      => $header,
                'articles'    => $articles,
                'blogs'       => Blog::getEntityCollection(),
                'breadcrumbs' => $this->getBreadcrumbs(),
            ] + $blogService->getWidgetsData());

        return $view;
    }

    public function blogAction(Blog $blog, $options = [])
    {
        $type  = $options['type'] ?? null;
        $view = $this->generate('/blog/');
        $blogService = $this->getBlogService();

        $this->generateMeta($blog, ['{BLOG_NAME}', '{BLOG_NAME_L}'], [$blog->get('name'), mb_strtolower($blog->get('name'))]);

        $view->setVariables([
            'blog'  => $blog,
        ]);

        if($type) {
            $url = $type->getUrl();
        } else {
            $url = $blog->getUrl();
        }

        $this->layout()->setVariable('canonical', $url);
        $view->setTemplate('blog/mobile/category');
        $this->addBreadcrumbs([['url' => $blog->getUrl(), 'name' => $blog->get('name')]]);


        $filters = [
            'blog' => [$blog->getId()],
            'type' => $type ? $type->getId() : null,
            'itemsPerPage'  => 5
        ];

        $page = $this->params()->fromQuery('page', 1);
        $articles = $this->getBlogService()->getPaginator($page, $filters);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];
            $viewHelper = $this->getViewHelper('articlesList');
            $resp['html']['items'] = $viewHelper($articles, true);
            return new JsonModel($resp);
        }

        if($type) {
            $header = $type->get('name');
            $this->addBreadcrumbs([['url' => $url, 'name' => $type->get('name')]]);
        } else {
            $header = $blog->get('name');
        }

        $view->setVariables([
                'header'      => $header,
                'articles'    => $articles,
                'type'        => $type,
                'blog'        => $blog,
                'blogs'       => Blog::getEntityCollection(),
                'breadcrumbs' => $this->getBreadcrumbs(),
            ] + $blogService->getWidgetsData());

        return $view;
    }

    public function articleAction()
    {
        $view = $this->generate('/blog/');
        $url = $this->params()->fromRoute('url');

        $article = $this->getBlogService()->getArticle($url);

        if(!$article->load()) {
            return $this->send404();
        }

        $blogService = $this->getBlogService();

        $blogService->addHits($article);

        $this->generateMeta($article);

        $url = $article->getUrl();

        $this->layout()->setVariable('canonical', $url);

        $type = $article->getPlugin('types')->rewind()->current();
        $blog = $type->getPlugin('blog');

        $this->addBreadcrumbs([['url' => $blog->getUrl(), 'name' => $blog->get('name')]]);
        $this->addBreadcrumbs([['url' => $type->getUrl(), 'name' => $type->get('name')]]);
        $this->addBreadcrumbs([['url' => $url, 'name' => $article->get('name')]]);

        //$recoArticles = $this->getBlogService()->getRecoArticles($article);

        $view->setVariables([
                'header'      => $article->get('name'),
                'breadcrumbs' => $this->getBreadcrumbs(),
                'article'     => $article,
            ] + $blogService->getWidgetsData());

        return $view;
    }


    /**
     * @return \Catalog\Service\ProductsService
     */
    public function getProductsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\ProductsService');
    }

    /**
     * @return \Blog\Service\BlogService
     */
    public function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }
}