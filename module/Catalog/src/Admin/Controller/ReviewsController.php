<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Reviews;
use Aptero\Service\Admin\TableService;

class ReviewsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = array(
            0  => 'red',
            1  => 'green',
            2  => 'gray',
        );

        $this->setFields(array(
            'name' => array(
                'name'      => 'Имя',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '8',
            ),
            'review' => array(
                'name'      => 'Отзыв',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'review',
                'width'     => '68',
                'tdTitle'   => true,
                'tdStyle'   => [
                    'height'    => '100px',
                    'line-height'    => '1.4',
                ],
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '11',
                'filter'    => function($value, $row) use ($classes){
                    if($value == Reviews::STATUS_NEW) {
                        return
                             '<span class="wrap green st-review" data-id="' . $row->getId() . '" data-status="' . Reviews::STATUS_VERIFIED . '">Подтвердить</span><br>'
                            .'<span class="wrap red st-review" data-id="' . $row->getId() . '" data-status="' . Reviews::STATUS_REJECTED . '">Отклонить</span>';
                    }

                    return '<span class="wrap ' . $classes[$value] . '">' . Reviews::$statuses[$value]. '</span>';
                },
                'tdStyle'   => [
                    'height'    => '85px',
                    'line-height'    => '2',
                    'padding-top'    => '10px',
                ],
            ),
            'product_id' => array(
                'name'      => 'Товар',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'product_id',
                'filter'    => function($value, $row, $view){
                    $product = $row->getPlugin('product');
                    $url = $view->url('adminProducts', array('action' => 'edit')) . '?id=' . $product->get('id');

                    return '<a href="' . $url . '">' . $product->get('name') . '</a>';
                },
                'width'     => '13',
            ),
        ));
    }

    public function changeStatusAction()
    {
        $id = $this->params()->fromPost('id');
        $status = $this->params()->fromPost('status');

        $review = new Reviews();
        $review->setId($id);

        if($review->load()) {
            $review->set('status', $status)->save();
        }
    }
}