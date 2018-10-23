<?php
namespace Wholesale\Model;

use Aptero\Db\Entity\Entity;

class WsClient extends Entity
{
    public function __construct()
    {
        $this->setTable('Wholesale');

        $this->addProperties(array(
            'name'          => array(),
            'time_create'   => array(),
        ));
    }
}