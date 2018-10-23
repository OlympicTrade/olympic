<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Reviews;
use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class RequestsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'product_id' => array(
                'name'      => 'Товар',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'image',
                'filter'    => function($value, $row){
                    return $row->getPlugin('product')->get('name');
                },
                'width'     => '20',
                'sort'      => array(
                    'enabled'   => false
                )
            ),
            'contact' => array(
                'name'      => 'Контакты',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'contact',
                'width'     => '80',
            ),
        ));
    }
}