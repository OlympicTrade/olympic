<?php
namespace BlogAdmin\Model;

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
    }
}