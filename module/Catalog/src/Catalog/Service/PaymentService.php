<?php

namespace Catalog\Service;

use Aptero\Service\AbstractService;
use Catalog\Model\Order;

class PaymentService extends AbstractService
{
    public function payment($data)
    {
        $key = 'kpV1mYf2eIvzufty5pwqMWii';

        if(strpos($data['label'], 'M')) {
            $this->sendToMyproteinSpb($data);
            return true;
        };

        $hashData = [
            $data['notification_type'],
            $data['operation_id'],
            $data['amount'],
            $data['currency'],
            $data['datetime'],
            $data['sender'],
            $data['codepro'],
            $key,
            $data['label'],
        ];

        if(sha1(implode('&', $hashData)) != $data['sha1_hash']) {
            return false;
        }

        $order = new Order();
        $order->setId($data['label']);

        if(!$order->load()) {
            return false;
        }

        $order->set('paid', $data['amount'])->save();

        return true;
    }

    protected function sendToMyproteinSpb($data)
    {
        $url = 'https://myprotein.spb.ru/payment/confirm/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }
}