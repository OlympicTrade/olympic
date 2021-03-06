<?php
namespace BalanceAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use BalanceAdmin\Model\BalanceFlow;

class CashController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'type' => array(
                'name'      => 'Тип расходов',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'type',
                'width'     => '16',
                'filter'    => function($value, $row) {
                    return BalanceFlow::$flowTypes[$value];
                },
            ),
            'price' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'price',
                'width'     => '16',
                'filter'    => function($value, $row) {
                    if(BalanceFlow::TYPE_FROZEN == $row->get('type')) {
                        $class = 'blue';
                    } elseif(BalanceFlow::TYPE_CREDIT == $row->get('type')) {
                        $class = 'yellow';
                    } else {
                        $class = $value > 0 ? 'green' : 'red';
                    }

                    return '<span class="wrap ' . $class . '">' . ($value > 0 ? '+' : '') . $value. ' руб.</span>';
                },
            ),
            'desc' => array(
                'name'      => 'Описание',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'desc',
                'width'     => '53',
            ),
            'date' => array(
                'name'      => 'Дата',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'date',
                'width'     => '15',
            ),
        ));
    }

    public function listAction()
    {
        $view = parent::listAction();

        $service = $this->getService();

        $view->setVariable('statistic', array(
            'cash' => array(
                'orders'    => $service->getOrdersCash(),
                'money'     => $service->getMoneyCash(),
                'products'  => $service->getProductsCash(),
                'supplies'  => $service->getSuppliesCash(),
            )
        ));

        return $view;
    }
}