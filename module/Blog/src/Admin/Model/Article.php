<?php
namespace BlogAdmin\Model;

use ApplicationAdmin\Model\Content;
use Aptero\Db\Entity\Entity;

class Article extends Entity
{
    public function __construct()
    {
        $this->setTable('blog_articles');

        $tomorrow = new \DateTime();
        $tomorrow->add(new \DateInterval('P1D'));

        $this->addProperties(array(
            'name'        => [],
            'preview'     => [],
            'text'        => [],
            'tags'        => [],
            'url'         => [],
            'title'       => [],
            'links'       => [],
            'description' => [],
            'hits'        => [],
            'time_update' => [],
            'time_create' => ['default' => $tomorrow->format('Y-m-d H:i:s')],
        ));

        $this->addPlugin('blog', function($model) {
            $blog = new Blog();
            $blog->select()->where(['id' => $model->get('blog_id')]);

            return $blog;
        });

        $this->addPlugin('content', function($model) {
            $content = Content::getEntityCollection();
            $content->select()->where([
                'module'    => 'blog',
                'depend'    => $model->getId(),
            ])->order('sort');

            return $content;
        });

        $this->addPlugin('types', function($model) {
            $item = new Entity();
            $item->setTable('blog_articles_types');
            $item->addProperties(array(
                'depend'    => [],
                'type_id'   => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_articles_images');
            $image->setFolder('articles');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 120,
                    'height' => 120,
                    'crop'   => true
                ),
                'hr' => array(
                    'width'  => 1350,
                    'height' => 625,
                    'crop'   => true
                ),
            ));

            return $image;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }
}