<?php
namespace Delivery\Model;

use Application\Model\Region;
use Aptero\Db\Entity\Entity;

class Point extends Entity
{
    public function __construct()
    {
        $this->setTable('delivery_points');

        $this->addProperties([
            'city_id'           => [],
            'name'              => [],
            'address'           => [],
            'route'             => [],
            'type'              => [],
            'price'             => [],
            'phone'             => [],
            'worktime'          => [],
            'delay'             => [],
            'code'              => [],
            'city'              => [],
            'latitude'          => [],
            'longitude'         => [],
            'index_express'     => [],
            'glavpunkt'         => [],
        ]);
    }

    public function getFreeDeliveryPrice()
    {
        if(in_array($this->get('city'), ['Санкт-Петербург', 'Москва'])) {
            return 1500;
        }

        return ceil($this->get('price') / 100) * 1000;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        $date = new \DateTime();
        $date->modify('+' . $this->get('delay') . ' days');

        return $date;
    }

    public function isMoscow()
    {
        return in_array($this->get('name'), ['Москва']);
    }

    public function isSpb()
    {
        return in_array($this->get('name'), ['Санкт-Петербург']);
    }
}