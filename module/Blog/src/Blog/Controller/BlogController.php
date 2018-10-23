<?php
namespace Blog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Blog\Form\CommentForm;
use Blog\Model\Article;
use Blog\Model\Blog;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class BlogController extends AbstractActionController
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
        $articles = $this->getBlogService()->getPaginator($page, []);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];
            $resp['html']['items'] = $this->viewHelper('articlesList', $articles);
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
        $view->setTemplate('blog/blog/category');
        $this->addBreadcrumbs([['url' => $blog->getUrl(), 'name' => $blog->get('name')]]);

        $filters = [
            'blog' => [$blog->getId()],
            'type' => $type ? $type->getId() : null,
        ];

        $page = $this->params()->fromQuery('page', 1);
        $articles = $this->getBlogService()->getPaginator($page, $filters);

        if($this->getRequest()->isXmlHttpRequest()) {
            $resp = [];
            $resp['html']['items'] = $this->viewHelper('articlesList', $articles);
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

        //$type = $article->getPlugin('types')->rewind()->current();
        //$blog = $type->getPlugin('blog');

        //$this->addBreadcrumbs([['url' => $blog->getUrl(), 'name' => $blog->get('name')]]);
        //$this->addBreadcrumbs([['url' => $type->getUrl(), 'name' => $type->get('name')]]);
        $this->addBreadcrumbs([['url' => $url, 'name' => $article->get('name')]]);

        //$recoArticles = $this->getBlogService()->getRecoArticles($article);

        $view->setVariables([
            'header'      => $article->get('name'),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'article'     => $article,
        ] + $blogService->getWidgetsData());

        return $view;
    }

    public function getArticleDataAction()
    {
        //$type = $this->params()->fromPost('type', 'next');
        $id = $this->params()->fromPost('id');

        $article = new Article();
        $article->select()
            ->order('id DESC')
            ->where
                ->lessThan('id', $id);

        $article->load();

        return new JsonModel([
            'id'    => $article->getId(),
            'name'  => $article->get('name'),
            'desc'  => $article->get('desc'),
            'date'  => $article->getDt()->format('d.m.Y'),
            'image' => $article->getPlugin('image')->getImage('hr'),
            'url'   => $article->getUrl(),
        ]);

    }

    public function addCommentAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        if ($request->isPost()) {
            $form = new CommentForm();
            $form->setData($request->getPost())->setFilters();

            if ($form->isValid()) {
                $this->getBlogService()->addComment($form->getData());
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $article = new Article();
        $article->setId($this->params()->fromQuery('aid'));
        if(!$article->load()) {
            $this->send404();
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'article'   => $article,
            'parent'    => $this->params()->fromQuery('pid'),
        ));

        return $viewModel;
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