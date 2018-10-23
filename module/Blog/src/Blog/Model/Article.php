<?php
namespace Blog\Model;

use Application\Model\Content;
use Aptero\Db\Entity\Entity;

class Article extends Entity
{
    public function __construct()
    {
        $this->setTable('blog_articles');

        $this->addProperties(array(
            'blog_id'     => [],
            'name'        => [],
            'preview'     => [],
            'text'        => [],
            'tags'        => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'hits'        => [],
            'time_create' => [],
            'time_update' => [],
        ));

        $this->addPlugin('blog', function($model) {
            $blog = new Blog();
            $blog->select()->where(['id' => $model->get('blog_id')]);

            return $blog;
        });

        $this->addPlugin('types', function($model) {
            $types = BlogTypes::getEntityCollection();
            $types->select()
                ->join(['bat' => 'blog_articles_types'], 'type_id = t.id', [])
                ->where(['bat.depend' => $model->getId()])
                ->order('t.sort');

            return $types;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_articles_images');
            $image->setFolder('articles');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 800,
                    'height' => 500,
                    'crop'   => true
                ),
                'hr' => array(
                    'width'  => 1350,
                    'height' => 625,
                    'crop'   => true
                ),
                's' => array(
                    'width'  => 400,
                    'height' => 225,
                    'crop'   => true
                ),
            ));

            return $image;
        });

        $this->addPlugin('content', function($model) {
            $content = Content::getEntityCollection();
            $content->select()->where([
                'module'    => 'blog',
                'depend'    => $model->getId(),
            ])->order('sort');

            return $content;
        });

        $this->addPlugin('comments', function($model) {
            $catalog = Comment::getEntityCollection();
            $catalog->select()
                ->where(array(
                    'article_id' => $model->getId(),
                    'status'     => Comment::STATUS_VERIFIED,
                ))
                ->order('time_create DESC');
            $catalog->setParentId(0);;

            return $catalog;
        });
    }

    public function getUrl()
    {
        return '/blog/article/' . $this->get('url') . '/';
    }

    public function getDt()
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->get('time_create'));
    }
}