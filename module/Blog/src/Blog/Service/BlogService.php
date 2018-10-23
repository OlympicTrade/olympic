<?php

namespace Blog\Service;

use Aptero\Cookie\Cookie;
use Aptero\Service\AbstractService;
use Blog\Model\Article;
use Blog\Model\Blog;
use Blog\Model\BlogTypes;
use Blog\Model\Comment;
use User\Service\AuthService;
use Zend\Db\Sql\Expression;

class BlogService extends AbstractService
{
    public function getWidgetsData()
    {
        $data = [];

        $data['wProducts'] = $this->getProductsService()->getProducts([
            'minPrice'  => true,
            'onlyDiscounts'  => true,
            'limit'     => 4,
            'sort'      => 'rand',
            'join'   => [
                'reviews',
                'catalog',
                'brand',
                'image',
            ],
        ]);

        $data['wArticles'] = $this->getArticles([
            'limit' => 4,
            'sort'  => 'hits',
        ]);

        return $data;
    }

    public function getPaginator($page, $filter = [])
    {
        $itemsPerPage = !empty($filter['itemsPerPage']) ? $filter['itemsPerPage'] : 8;
        $articles = $this->getArticles($filter);
        return $articles->getPaginator($page, $itemsPerPage);
    }

    public function getType($filters = [])
    {
        $type = new BlogTypes();

        if(!empty($filters['url'])) {
            $type->select()->where(['url' => $filters['url']]);
        }

        if(!empty($filters['blog_id'])) {
            $type->select()->where(['depend' => $filters['blog_id']]);
        }

        if(!empty($filters['article_id'])) {
            $type->select()
                ->join(['bat' => 'blog_articles_types'], 'type_id = t.id', [])
                ->where(['bat.depend' => $filters['article_id']]);
        }

        return $type->load();
    }

    public function getBlog($filters = [])
    {
        $blog = new Blog();
        return $blog->setSelect($this->getBlogSelect($filters));
    }

    public function addHits(Article $article)
    {
        $hits = Cookie::getCookie('blog-hits', true);
        if($hits && in_array($article->getId(), $hits)) {
            return;
        }

        $article->set('hits', $article->get('hits') + 1)->save();
        $hits[] = $article->getId();
        Cookie::setCookie('blog-hits', $hits, 1);
    }

    public function getArticle($url)
    {
        $article = new Article();
        $article->select()
            ->where
            ->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"))
            ->equalTo('url', $url);

        return $article;
    }

    public function getArticles($filters = [])
    {
        $articles = Article::getEntityCollection();
        $articles->setSelect($this->getArticlesSelect($filters));

        return $articles;
    }

    public function getArticlesSelect($filters)
    {
        $select = $this->getSql()->select()
            ->from(['t' => 'blog_articles'])
            ->columns(['id', 'blog_id', 'name', 'url', 'preview', 'time_create'])
            ->join(['ai' => 'blog_articles_images'], 't.id = ai.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left')
            ->order('time_create DESC')
            ->group('t.id');

        $select
            ->where->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"));

        if($filters['limit']) {
            $select->limit($filters['limit']);
        }

        if(!empty($filters['type'])) {
            $select
                ->join(['bat' => 'blog_articles_types'], 't.id = bat.depend', [], 'left')
                ->where(['bat.type_id' => $filters['type']]);
        } elseif(!empty($filters['blog'])) {
            $select
                ->join(['bt' => 'blog_types'], new Expression('bt.depend IN (' . implode(',', $filters['blog']) . ')'), [])
                ->join(['bat' => 'blog_articles_types'], new Expression('bt.id = bat.type_id AND bat.depend = t.id'), []);
        }

        return $select;
    }

    public function getBlogSelect($filters)
    {
        $select = $this->getSql()->select()
            ->from(['t' => 'blog'])
            ->columns(['id', 'name', 'url'])
            ->join(['bi' => 'blog_images'], 't.id = bi.depend', ['image-id' => 'id', 'image-filename' => 'filename', 'image-desc' => 'desc'], 'left')
            ->order('sort DESC');

        if($filters['url']) {
            $select->where(['url' => $filters['url']]);
        }

        return $select;
    }

    public function getRecoArticles($article, $filter = [])
    {
        $articles = $this->getArticles(['limit' => 5]);
        $articles->select()
            ->where
                ->notEqualTo('t.id', $article->getId())
                ->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"));

        return $articles;
    }

    public function getTrendArticles($article, $filter = [])
    {
        $articles = $this->getArticles(['limit' => 5]);
        $articles->select()
            ->where
                ->notEqualTo('t.id', $article->getId())
                ->lessThanOrEqualTo('time_create', date("Y-m-d H:i:s"));

        return $articles;
    }

    public function addComment($data)
    {
        if($user = AuthService::getUser()) {
            $data['user_id'] =  $user->getId();
        }

        $data['status'] = Comment::STATUS_NEW;

        $review = new Comment();
        $review->setVariables($data)->save();
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}