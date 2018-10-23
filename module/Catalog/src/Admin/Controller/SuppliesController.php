<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Reviews;
use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Supplies;
use ReviewsAdmin\Model\Review;
use Zend\View\Model\JsonModel;

class SuppliesController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = array(
            0  => 'red',
            3  => 'yellow',
            5  => 'green',
        );

        $this->setFields(array(
            'name' => array(
                'name'      => 'Номер',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'number',
                'width'     => '10',
            ),
            'user_id' => array(
                'name'      => 'ФИО',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'user_id',
                'width'     => '20',
                'filter'    => function($value, $row) {
                    return '<b>' . Supplies::$users[$value] . '</b>';
                },
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '18',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Supplies::$statuses[$value]. '</span>';
                },
            ),
            'price' => array(
                'name'      => 'Товары',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'price',
                'filter'    => function($value, $row) use ($classes){
                    return $row->getPrice(0);
                },
                'width'     => '18',
            ),
            'delivery' => array(
                'name'      => 'Доставка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'price',
                'filter'    => function($value, $row) use ($classes){
                    return $row->getDelivery(0);
                },
                'width'     => '18',
            ),
            'date' => array(
                'name'      => 'Дата',
                'type'      => TableService::FIELD_TYPE_DATE,
                'field'     => 'date',
                'width'     => '16',
            ),
        ));
    }

    public function listAction()
    {
        $view = parent::listAction();

        $service = $this->getService();

        $view->setVariable('statistic', [
            'weight'     => $service->getWeightStatistic(),
            'lacked'     => $service->getProductsLack(),
            'requested'  => $service->getProductsRequested(),
        ]);

        return $view;
    }

    public function cartUpdateAction()
    {
        $data = $this->params()->fromPost();
        $resp = [];

        switch ($data['type']) {
            case 'price':
                $resp['price'] = $this->getService()->updateCartPrice($data)['price'];
                break;
            case 'count':
                $resp['stock'] = $this->getService()->updateCartCount($data)['stock'];
                break;
        }

        return new JsonModel($resp);
    }

    public function addProductAction()
    {
        $publicProdService = $this->getServiceLocator()->get('Catalog\Service\ProductsService');

        $this->getService()->addToCart($_POST, $publicProdService);

        return new JsonModel([]);
    }
}