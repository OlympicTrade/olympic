<?php
namespace Blog\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;
use Zend\Db\Sql\Expression;

class Blog extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('blog');

        $this->addProperties(array(
            'parent'      => [],
            'name'        => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'text'        => [],
        ));

        $this->addPlugin('types', function($model) {
            $types = BlogTypes::getEntityCollection();
            $types->select()->where(['depend' => $model->getId()]);
            return $types;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('blog_images');
            $image->setFolder('blog');
            $image->addResolutions([
                's' => [
                    'width'   => 360,
                    'height'  => 300,
                    'crop'    => true,
                    'opacity' => true,
                ],
            ]);

            return $image;
        });
    }

    public function getArticlesCount()
    {
        $select = $this->getSql()->select()
            ->from(['ba' => 'blog_articles'])
            ->columns(['count' => new Expression('COUNT(DISTINCT ba.id)')])
            ->join(['bat' => 'blog_articles_types'] ,'bat.depend = ba.id', [])
            ->join(['bt' => 'blog_types'], new Expression('bat.type_id = bt.id AND bt.depend = ' . $this->getId()), [])
            ->where(['bt.depend' => $this->getId()]);

        return $this->execute($select)->current()['count'];

    }

    public function getUrl()
    {
        return '/blog/' . $this->get('url') . '/';
    }
}