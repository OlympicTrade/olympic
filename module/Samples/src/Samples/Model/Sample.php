<?php
namespace Samples\Model;

use Aptero\Db\Entity\Entity;

class Sample extends Entity
{
    public function __construct()
    {
        $this->setTable('samples');

        $this->addProperties(array(
            'name'          => array(),
            'time_create'   => array(),
        ));
    }
}