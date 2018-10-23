<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Catalog\Model\Product;
use Catalog\Model\Reviews;

class ParserController extends AbstractActionController
{
    public function updateUrlsAction()
    {
        $products = Product::getEntityCollection();

        foreach ($products as $product) {
            $product->set('url', \Aptero\String\Translit::url($product->getPlugin('brand')->get('name') . ' ' . $product->get('name')))
                ->save();
        }

        die();
    }

    public function reviewsAction()
    {
        $products = Product::getEntityCollection();
        $products->select()->where([
            'mp_url' => ''
        ]);

        foreach ($products as $product) {
            if(!$product->getPlugin('attrs')->get('mp_url') && $product->get('brand_id') == 42) {
                echo '<a href="/admin/catalog/products/edit/?id=' . $product->getId() . '">' . $product->get('name')
                    . '</a> - <a href="https://www.myprotein.ru/elysium.search?search=' . $product->get('name') . '">искать<br>';
            }
        }

        die('END');
    }

    public function parserAction()
    {
        $file = __DIR__ . '/counter.txt';
        $firstId = (int) file_get_contents($file);

        $products = Product::getEntityCollection();
        $products->select()
            ->limit(10)
            ->where
            ->equalTo('t.brand_id', 42) //myprotein
            ->greaterThan('t.id', $firstId);

        $counter = 0;
        $lastId = 0;
        foreach ($products as $product) {
            if(!$product->getPlugin('attrs')->get('mp_url')) {
                file_put_contents($file, $product->getId());
                continue;
            }
            $counter += $this->parse($product);
            file_put_contents($file, $product->getId());
            sleep(2);
        }


        echo('Добавлено: ' . $counter) . '<br>';
        echo 'Last ID: ' . $product->getId();
        die();
    }

    public function parse($product)
    {
        include_once(MAIN_DIR . '/vendor/phpquery/phpQuery.php');
        $html = file_get_contents($product->getPlugin('attrs')->get('mp_url'));
        $document = \phpQuery::newDocumentHTML($html);

        $reviews = $document->find('div.review-block');

        $counter = 0;
        foreach($reviews as $review) {
            $data = [
                'product_id' => $product->getId(),
            ];
            $review = \phpQuery::pq($review);

            $data['name'] = trim($review->find('.product-review-author span')->text());

            $data['stars'] = trim($review->find('.rating-holder .rating-stars:not(.rating-stars-secondary)')->text());
            $desc = $review->find('.review-description')->html();
            $desc = str_replace(["\n", "\r"], ' ', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc);

            $cutPos = strpos($desc,'<b>Данный продукт отлично сочетается с:</b>');
            if($cutPos) {
                $desc = trim(substr($desc, 0, strpos($desc,'<b>Данный продукт отлично сочетается с:</b>')));
            }
            $data['review'] = $desc;

            $review->find('.product-review-author')->remove();
            $dateStr = trim(str_replace(['по'], [''], $review->find('.author-wrapper')->text()));
            $dateStr = substr($dateStr, 0, 28);
            $date = \DateTime::createFromFormat('D M d H:i:s Y', str_replace(['BST ', 'GMT '], '', $dateStr));

            if($date) {
                $data['time_create'] = $date->format('Y-m-d H:i:s');
            } else {
                var_dump($dateStr);die();
            }

            $review = new Reviews();
            $review->select()->where($data);

            if($review->load()) {
                continue;
            }

            $data['status'] = Reviews::STATUS_NEW;
            $data['source'] = Reviews::SOURCE_MYRPOTEIN;
            $review->setVariables($data)->save();
            $counter++;
        }

        return $counter;
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\OrdersService');
    }
}