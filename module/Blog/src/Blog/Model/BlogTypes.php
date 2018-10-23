<?php
namespace Blog\Model;

use Aptero\Db\Entity\Entity;

class BlogTypes extends Entity
{
    public function __construct()
    {
        $this->setTable('blog_types');

        $this->addProperties([
            'depend'      => [],
            'name'        => [],
            'short_name'  => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'sort'        => [],
        ]);

        $this->addPlugin('blog', function($model) {
            $blog = new Blog();
            $blog->setId($model->get('depend'));

            return $blog;
        });
    }

    public function getUrl()
    {
        return $this->getPlugin('blog')->getUrl() . $this->get('url') . '/';
    }
}