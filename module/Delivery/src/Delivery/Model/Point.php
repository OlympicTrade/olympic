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

        $this->addPlugin('city', function($model) {
            $city = new City();
            $city->setId($model->get('city_id'));

            return $city;
        });
    }

    public function getFreeDeliveryPrice()
    {
        if(in_array($this->get('city'), ['Санкт-Петербург', 'Москва'])) {
            return 1500;
        }

        return ceil($this->get('price') / 100) * 1000;
    }

    public function getDeliveryDate()
    {
        $delay = $this->getPlugin('city')->getDeliveryDelay(['type' => Delivery::TYPE_PICKUP]);
        $dt = (new \DateTime())->modify('+' . $delay . ' days');

        return $dt;
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