<?php
namespace Catalog\Model;

use Application\Model\Country;
use Aptero\Db\Entity\Entity;

class Brand extends Entity
{
    public function __construct()
    {
        $this->setTable('brands');

        $this->addProperties([
            'country_id' => [],
            'url'        => [],
            'name'       => [],
            'text'       => [],
            'html'       => [],
            'title'       => [],
            'description' => [],
        ]);

        $this->addPlugin('country', function($model) {
            $catalog = new Country();
            $catalog->setId($model->get('country_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPropertyFilterOut('url_path', function($model, $url) {
            return '/brands/' . $model->get('url') . '/';
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('brands_images');
            $image->setFolder('brands');
            $image->addResolutions([
                's' => array(
                    'width'  => 245,
                    'height' => 128,
                    'crop'   => true,
                ),
            ]);

            return $image;
        });
    }

    public function getUrl()
    {
        return '/brands/' . $this->get('url') . '/';
    }
}