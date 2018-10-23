<?php
namespace Application\Model;

use ApplicationAdmin\Model\Plugin\ContentImages;
use Aptero\Db\Entity\Entity;

class Content extends Entity
{
    public function __construct()
    {
        $this->setTable('content');

        $this->addProperties([
            'depend'      => [],
            'module'      => [],
            'title'       => [],
            'text'        => [],
            'video'       => [],
            'type'        => [],
            'sort'        => [],
        ]);

        $this->addPlugin('images', function() {
            $image = new ContentImages();
            $image->setTable('content_gallery');
            $image->setFolder('content');
            $image->addResolutions([
                'm' => [
                    'width'  => 750,
                    'height' => 450,
                    'crop'   => true,
                ],
                'hr' => array(
                    'width'  => 1920,
                    'height' => 1150,
                )
            ]);

            return $image;
        });
    }
}