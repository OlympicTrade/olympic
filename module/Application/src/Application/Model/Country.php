<?php
namespace Application\Model;

use Aptero\Db\Entity\Entity;

class Country extends Entity
{
    public function __construct()
    {
        $this->setTable('countries')
            ->addProperties([
                'name'   => [],
            ]);
    }
}