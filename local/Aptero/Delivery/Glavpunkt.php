<?php

namespace Aptero\Delivery;

use CatalogAdmin\Model\Orders;
use Delivery\Model\Delivery;
use Zend\Json\Json;

class Glavpunkt
{
    protected $login = 'Olympic';
    protected $token = '6f31284e085da40df05381adfe38f8db';
    protected $invoiceFile = MAIN_DIR . '/../sync/gp_invoice.json';
    //protected $invoiceFile = DATA_DIR . '/sync/gp_invoice.json';

    public function addDelivery(Orders $order)
    {
        $attrs = $order->getPlugin('attrs');

        $whPoint = 'Veteranov-N67k2';

        $data = [
            'login'    => $this->login,
            'token'    => $this->token,
            'punkt_id' => $whPoint,
            'comments_client' => '',
        ];

        $data['orders'][] = $this->orderData($order);

        if($invoice = $this->getInvoiceData()) {
            $url = '/api/append_pkgs';
            $data['arrival_move_id'] = $invoice->invoice;
        } else {
            $url = '/api/take_pkgs';
        }

        $resp = $this->post($url, $data);

        if($resp->result != 'ok') {
            return ['errors' => $resp->message];
        }

        $invoiceCode = $resp->docnum;

        $this->addInvoice($invoiceCode);
        $attrs->set('invoice', $invoiceCode)->save();

        return ['invoice' => $invoiceCode, 'errors' => ''];
    }

    protected function orderData($order)
    {
        $attrs = $order->getPlugin('attrs');
        $phone = $order->getPlugin('phone');
        $city  = $order->getPlugin('city');

        $whPoint = 'Veteranov-N67k2';

        $orderData = [
            'sku'        => $order->getPublicId(),
            'is_prepaid' => (int) $order->isPaid(),
            'price'      => !$order->isPaid() ? $order->get('income') : 0,
            'client_delivery_price' => !$order->isPaid() ? $order->get('delivery_income') : 0,
            'buyer_fio'  => $attrs->get('name'),
            'buyer_phone'=> $phone->get('phone'),
            'weight'     => 2,
            'parts'      => [],
        ];

        foreach($order->getPlugin('cart') as $cartRow) {
            if(!$cartRow->get('count')) continue;

            $product = $cartRow->getPlugin('product');

            $prName = $product->get('name');

            if($product->get('size') || $product->get('taste')) {
                $prName .= ' (' . rtrim($product->get('size') . ', ' . $product->get('taste'), ' ,') . ')';
            }

            $orderData['parts'][] = [
                'name'  => $prName,
                'price' => $cartRow->get('price'),
                'num'   => $cartRow->get('count'),
            ];
        }

        if ($attrs->get('delivery') == Delivery::TYPE_COURIER) {
            $orderData += [
                'serv'       => 'курьерская доставка',
                'delivery'   => [
                    'city'    => $city->get('code'),
                    'address' => $order->getDeliveryAddress(),
                    'date'    => $attrs->get('date'),
                    'time'    => 'с ' . $attrs->get('time_from') . ' до 18' . $attrs->get('time_to')
                ]
            ];
        }

        if ($attrs->get('delivery') == Delivery::TYPE_PICKUP) {
            $orderData += [
                'sku'        => $order->getPublicId(),
            ];

            $point = $order->getPickupPoint();

            if($city->isCapital()) {
                $orderData['serv'] = 'выдача';
                if($point->get('code') != $whPoint) {
                    $orderData['dst_punkt_id'] = $point->get('code');
                }
            } else {
                $orderData['serv'] = 'выдача по РФ';
                $orderData['delivery_rf'] = [
                    'city_id' => $city->get('code'),
                    'pvz_id'  => $point->get('code'),
                ];
            }
        }

        return $orderData;
    }

    public function addInvoice($code)
    {
        file_put_contents($this->invoiceFile, Json::encode([
            'invoice' => $code,
            'date'    => date('Y-m-d H:i;s'),
        ]));
    }

    public function delInvoice()
    {
        @unlink($this->invoiceFile);
    }

    public function getInvoiceData()
    {
        if(!file_exists($this->invoiceFile)) {
            return false;
        }
        return Json::decode(file_get_contents($this->invoiceFile));
    }

    public function getBarcodes($orders)
    {
        $url = '/api/pkgs_labels?login=' . $this->login . '&token=' . $this->token;

        foreach ($orders as $order) {
            $url .= '&skus[]=' . $order->getPublicId();
        }

        return $this->post($url);
    }

    public function getInvoicePDF($code = null)
    {
        if(!$code && $invoice = $this->getInvoiceData()) {
            $code = $invoice->invoice;
        } else {
            return false;
        }

        return $this->post('/api/take_pkgs_pdf?login=' . $this->login . '&token=' . $this->token . '&id=' . $code);
    }

    private function post($url, $data = null, $json = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://glavpunkt.ru' . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


        if (isset($data)) {
            $post_body = http_build_query($data);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body);
        }

        $resp = curl_exec($curl);
        if($json) $resp = Json::decode($resp);

        curl_close($curl);
        if (is_null($resp)) {
            throw new \Exception("Неверный JSON ответ=> " . $resp);
        }

        return $resp;
    }
}