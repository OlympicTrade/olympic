<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use CatalogAdmin\Model\Plugin\ProductProps;
use Catalog\Model\Catalog;
use Aptero\Db\Plugin\Attributes;
use Zend\Db\Sql\Sql;

class Reviews extends Entity
{
    const SOURCE_MYRPOTEIN = 'Myprotein'; //!регистр изменился

    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

    static public $statuses = array(
        self::STATUS_NEW        => 'Новый отзыв',
        self::STATUS_VERIFIED   => 'Отзыв проверен',
        self::STATUS_REJECTED   => 'Отзыв отклонен',
    );

    public function __construct()
    {
        $this->setTable('products_reviews');

        $this->addProperties(array(
            'user_id'       => array(),
            'product_id'    => array(),
            'name'          => array(),
            'review'        => array(),
            'email'         => array(),
            'answer'        => array(),
            'stars'         => array(),
            'status'        => array(),
            'time_create'   => array(),
        ));

        $this->addPlugin('product', function($model) {
            $catalog = new \CatalogAdmin\Model\Products();
            $catalog->setId($model->get('product_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('user', function($model) {
            $catalog = new \UserAdmin\Model\User();
            $catalog->setId($model->get('user_id'));

            return $catalog;
        }, array('independent' => true));
    }
}