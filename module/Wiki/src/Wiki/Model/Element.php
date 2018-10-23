<?php
namespace Wiki\Model;

use Aptero\Db\Entity\Entity;

class Element extends Entity
{
    public function __construct()
    {
        $this->setTable('wiki_elements');

        $this->addProperties([
            'name'        => [],
            'name_short'  => [],
            'url'         => [],
            'type'        => [],
            'text'        => [],
            'title'       => [],
            'description' => [],
        ]);
    }
}