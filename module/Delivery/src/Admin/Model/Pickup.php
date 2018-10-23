<?php
namespace DeliveryAdmin\Model;

use Aptero\Db\Entity\Entity;

class Pickup extends Entity
{
    static public $companies = [
        2  => 'Shop Logistic',
        1  => 'Index Express',
    ];
    
    public function __construct()
    {
        $this->setTable('delivery_pickup');

        $this->addProperties([
            'address'     => [],
            'route'       => [],
            'latitude'    => ['default' => '55.753994'],
            'longitude'   => ['default' => '37.622093'],
            'phone'       => [],
            'metro'       => [],
            'fitting'     => [],
            'partial'     => [],
            'delivery_id' => [],
            'work_time'   => [],
            'weekend'     => [],
            'company'     => [],
        ]);
    }
}