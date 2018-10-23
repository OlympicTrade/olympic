<?php
namespace CallcenterAdmin\Model;

use Aptero\Db\Entity\Entity;
use CatalogAdmin\Model\Orders;
use CatalogAdmin\Model\Requests;
use UserAdmin\Model\Phone;

class WsClient extends Entity
{
    const STATUS_NEW      = 0;
    const STATUS_WAIT     = 1;
    const STATUS_CLOSED   = 2;
    const STATUS_PROCESS  = 3;

    static public $statuses = [
        self::STATUS_NEW        => 'Необработанный',
        self::STATUS_WAIT       => 'Отложен',
        self::STATUS_CLOSED     => 'Отказ',
        self::STATUS_PROCESS    => 'В работе',
    ];

    public function __construct()
    {
        $this->setTable('callcenter_wholesale');

        $this->addProperties([
            'source_id'    => [],
            'name'         => [],
            'phones'       => [],
            'email'        => [],
            'site'         => [],
            'city'         => [],
            'address'      => [],
            'route'        => [],
            'latitude'     => [],
            'longitude'    => [],
            'comments'     => [],
            'status'       => [],
        ]);
    }
}