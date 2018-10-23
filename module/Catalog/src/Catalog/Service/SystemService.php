<?php

namespace Catalog\Service;

use Application\Model\Sitemap;
use ApplicationAdmin\Model\Page;
use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Catalog\Model\Brand;
use Catalog\Model\Catalog;
use Catalog\Model\Product;

class SystemService extends AbstractService
{
    /**
     * @param Sitemap $sitemap
     * @return array
     */
    public function updateSitemap(Sitemap $sitemap)
    {
        $this->catalogSitemap($sitemap);
        $this->productsSitemap($sitemap);
        //$this->brandsSitemap($sitemap);
    }

    public function brandsSitemap(Sitemap $sitemap)
    {
        $brands = EntityFactory::collection(new Brand());

        foreach($brands as $brand) {
            $url = '/brands/' .  $brand->get('url') . '/';

            $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'weekly', //monthly | weekly | daily
                'priority'   => 0.7,
            ));
        }
    }

    public function catalogSitemap(Sitemap $sitemap)
    {
        $catalog = Catalog::getEntityCollection();
        $catalog->select()
            ->columns(['id', 'parent', 'url_path'])
            ->where([
                't.active'  => 1
            ]);

        foreach($catalog as $category) {
            $sitemap->addPage([
                'loc'        => $category->getUrl(),
                'changefreq' => 'weekly',
                'priority'   => 0.7,
            ]);

            foreach ($category->getPlugin('types') as $type) {
                $sitemap->addPage([
                    'loc'        => $category->getUrl() . $type->get('url') . '/',
                    'changefreq' => 'weekly',
                    'priority'   => 0.7,
                ]);
            }

            foreach ($category->getBrands() as $brand) {
                $sitemap->addPage([
                    'loc'        => $category->getUrl() . $brand->get('url') . '/',
                    'changefreq' => 'weekly',
                    'priority'   => 0.7,
                ]);
            }
        }
    }

    public function productsSitemap(Sitemap $sitemap)
    {
        $products = EntityFactory::collection(new Product());
        $products->select()
            ->columns(['id', 'type_id', 'brand_id', 'name', 'url', 'video'])
            ->join(['c' => 'catalog'], 't.catalog_id = c.id', [])
            ->join(['pa' => 'products_articles'], 'pa.depend = t.id', ['articles' => 'id'], 'left')
            ->join(['pb' => 'brands'], 't.brand_id = pb.id', ['brand-name' => 'name', 'brand-id' => 'id'], 'left')
            ->where(['pb.status' => 1])
            ->group('t.id');

        foreach($products as $product) {
			$url = '/goods/' .  $product['url'] . '/';
            $attrs = $product->getPlugin('attrs');
            $brand = $product->getPlugin('brand');

            $url = $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'weekly',
                'priority'   => 0.7,
            ));

            if(isset($_GET['images']) && $_GET['images'] == 1) {
                $imgs = (new Entity())
                    ->setTable('products_gallery')
                    ->addProperties([
                        'filename' => [],
                        'desc' => [],
                    ])->getCollection();

                $imgs->select()
                    ->columns(['filename', 'desc'])
                    ->where(['depend' => $product->getId()]);

                //products_gallery
                $typeName = $product->getCategoryType()->get('ya_cat_name');

                if ($imgs->count()) {
                    foreach ($imgs as $img) {
                        $sitemap->addImage($url, [
                            'url' => '/files/thumbs/products_gallery' . $img->get('filename'),
                            'name' => trim($typeName . ' ' . $brand->get('name') . ' ' . $product->get('name') . ' ' . $img->get('desc'))
                        ]);
                    }
                } else {
                    $img = $product->getPlugin('image');
                    $sitemap->addImage($url, [
                        'url' => $img->getImage('hr'),
                        'name' => trim($typeName . ' ' . $brand->get('name') . ' ' . $product->get('name') . ' '/* . $img->get('desc')*/)
                    ]);
                }
            }

            //reviews
            $sitemap->addPage([
                'loc'        => $url . 'reviews/',
                'changefreq' => 'monthly',
                'priority'   => 0.6,
            ]);

            //video
            if($product['video']) {
                $sitemap->addPage([
                    'loc' => $url . 'video/',
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ]);
            }

            //articles
            if($product['articles']) {
                $sitemap->addPage([
                    'loc' => $url . 'articles/',
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ]);
            }

            //tabs
            for($i = 1; $i <= 3; $i++) {
                $tab = 'tab' . $i;
                if(
                    empty($attrs->get('_url')) ||
                    empty($attrs->get('_header')) ||
                    empty($attrs->get('_title')) ||
                    empty($attrs->get('_description')) ||
                    empty($attrs->get('_text'))
                ) {
                    continue;
                }

                $sitemap->addPage([
                    'loc' => $url . $attrs->get('_url') . '/tab',
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ]);
            }
        }
    }
}