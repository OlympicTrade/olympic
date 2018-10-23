<?php
namespace CatalogAdmin\Service;

use Aptero\Service\AbstractService;
use Catalog\Model\Product;
use CatalogAdmin\Model\Products;
use Zend\Json\Json;

class StocksService extends AbstractService
{
    const SITE = 'https://myprotein.spb.ru';

    public function syncStock($productId)
    {
        $result = ['errors' => []];

        $product = new Products();
        $product->setId($productId);

        if(!$product->get('sync_id')) {
            $result['errors'][] = 'Не указан Myprotein ID';
            return $result;
        }

        $data = file_get_contents(self::SITE . '/sync/stock/product/?id=' . $product->get('sync_id'));

        try {
            $data = Json::decode($data);
        } catch (\Exception $e) {
            $result['errors'][] = 'Не удалось установить связь: ' .
                self::SITE . '/sync/stock/product/?id=' . $product->get('sync_id');
            return $result;
        }

        $delete = $this->getSql()->delete();
        $delete->from('products_stock')
            ->where(['product_id' => $product->getId()]);
        $this->execute($delete);

        foreach ($data->stock as $stockPos) {
            $sizeId = $this->checkSizes($product, $stockPos->size);
            if(!$sizeId) {
                $result['errors'][] = 'Не найден размер "' . $stockPos->size . '"';
                return $result;
            }

            $tasteId = $this->checkTaste($product, $stockPos->price);
            if(!$tasteId) {
                $result['errors'][] = 'Не найден вкус "' . $stockPos->price . '"';
                return $result;
            }

            $insert = $this->getSql()->insert();
            $insert->into('products_stock')
                ->values([
                    'product_id' => $product->getId(),
                    'size_id'    => $sizeId,
                    'taste_id'   => $tasteId,
                    'count'      => $stockPos->count,
                ]);

            $this->execute($insert);
        }

        return $result;
    }

    public function findDifference()
    {
        $diff = [];
        $otDomain = 'https://' . $_SERVER["HTTP_HOST"];

        $products = Products::getEntityCollection();
        $products->select()
            ->columns(['id', 'name'])
            ->limit(3)
            ->where
                ->equalTo('sync_id', 0);

        $diff['products'] = [];
        foreach ($products as $product) {
            $diff['products'][] = [
                'name' => $product->get('name'),
                'url'  => $otDomain . '/admin/catalog/products/edit/?id=' . $product->getId(),
            ];
        }

        $products = Products::getEntityCollection();
        $products->select()
            ->columns(['id', 'sync_id', 'name'])
            //->limit(3)
            ->where
                //->equalTo('id', 7)
                ->notEqualTo('sync_id', 0);

        foreach ($products as $product) {
            $pDiff = [
                'name'  => $product->get('name'),
                'ot_url'  => $otDomain . '/admin/catalog/products/edit/?id=' . $product->getId(),
                'mp_url'  => self::SITE . '/admin/catalog/products/edit/?id=' . $product->get('sync_id'),
            ];

            $data = file_get_contents(self::SITE . '/sync/stock/product/?id=' . $product->get('sync_id'));

            try {
                $data = Json::decode($data);
            } catch (\Exception $e) {
                die(self::SITE . '/sync/stock/product/?id=' . $product->get('sync_id'));
            }

            $exclude['size'] = [];
            $all['size'] = [];
            $pDiff['size'] = [];
            foreach ($data->size as $mpSize) {
                $result = $this->checkSizes($product, $mpSize->name);
                $all['size'][] = $mpSize->name;
                if(!$result) {
                    $exclude['size'][] = $mpSize->name;
                    $pDiff['size'][] = $mpSize->name;
                }
            }

            $select = $this->getSql()->select();
            $select->from(['t' => 'products_size'])
                ->columns(['name'])
                ->where
                    ->equalTo('depend', $product->getId())
                    ->notIn('name', $all['size']);
            foreach ($this->execute($select) as $row) {
                $pDiff['size'][] = $row['name'];
            }

            $exclude['taste'] = [];
            $all['taste'] = [];
            $pDiff['taste'] = [];
            foreach ($data->price as $mpTaste) {
                $result = $this->checkTaste($product, $mpTaste->name);
                $all['taste'][] = $mpTaste->name;
                if(!$result) {
                    $exclude['taste'][] = $mpTaste->name;
                    $pDiff['taste'][] = $mpTaste->name;
                }
            }

            $select = $this->getSql()->select();
            $select->from(['t' => 'products_taste'])
                ->columns(['name'])
                ->where
                ->equalTo('depend', $product->getId())
                ->notIn('name', $all['taste']);
            foreach ($this->execute($select) as $row) {
                $pDiff['taste'][] = $row['name'];
            }

            $pDiff['stock'] = [];
            foreach ($data->stock as $stockPos) {
                if(in_array($stockPos->price, $exclude['taste']) || in_array($stockPos->size, $exclude['size'])) {
                    continue;
                }

                $result = $this->checkStock($product, $stockPos);
                if(!$result['status']) {
                    $pDiff['stock'][] = $result;
                }
            }

            if($pDiff['size'] || $pDiff['taste'] || $pDiff['stock']) {
                $diff[] = $pDiff;
            }
        }

        return $diff;
    }

    public function checkStock(Products $product, $mpData)
    {
        $select = $this->getSql()->select();
        $select->from(['p' => 'products_stock'])
            ->columns(['count'])
            ->join(['ps' => 'products_size'], 'ps.id = p.size_id', [])
            ->join(['pt' => 'products_taste'], 'pt.id = p.taste_id', [])
            ->where([
                'p.product_id' => $product->getId(),
                'ps.name' => $mpData->size,
                'pt.name' => $mpData->price,
            ]);

        $row = $this->execute($select)->current();
        if($row === false) {
            $otCount = 0;
        } else {
            $otCount = (int) $row['count'];
        }

        $mpCount = (int) $mpData->count;

        if($otCount == $mpCount) {
            return ['status' => true];
        }

        return [
            'ot'     => $otCount,
            'mp'     => $mpCount,
            'size'   => $mpData->size,
            'taste'  => $mpData->price,
            'status' => false,
        ];
    }

    public function checkTaste(Products $product, $tasteName)
    {
        $select = $this->getSql()->select();
        $select->from(['t' => 'products_taste'])
            ->columns(['id'])
            ->where([
                'depend' => $product->getId(),
                'name'   => $tasteName
            ]);

        $result = $this->execute($select);

        if(!$result) {
            return false;
        }

        return $result->current()['id'];
    }

    public function checkSizes(Products $product, $sizeName)
    {
        $select = $this->getSql()->select();
        $select->from(['t' => 'products_size'])
            ->columns(['id'])
            ->where([
                'depend' => $product->getId(),
                'name'   => $sizeName
            ]);

        $result = $this->execute($select);

        if(!$result) {
            return false;
        }

        return $result->current()['id'];
    }
}