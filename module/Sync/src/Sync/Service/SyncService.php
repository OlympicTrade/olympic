<?php

namespace Sync\Service;

use Aptero\Service\AbstractService;
use CatalogAdmin\Model\Products;

class SyncService extends AbstractService
{
    const SITE = 'https://myprotein.spb.ru';

    public function syncStock($productId)
    {
        $product = new Products();
        $product->select()->where([
            'sync_id' => $productId
        ]);

        if(!$product->load()) {
            return ['errors' => ['Не найден товар с синх. ID ' . $productId]];
        }

        $stocksService = $this->getServiceManager()->get('CatalogAdmin\Service\StocksService');
        return $stocksService->syncStock($product->getId());
    }

    public function getProductData($productId)
    {
        $data = [
            'size'  => [],
            'price' => [],
            'stock' => [],
        ];

        $product = new Products();
        $product->setId($productId);

        if(!$product->load()) {
            return false;
        }

        foreach ($product->getPlugin('size') as $taste) {
            $data['size'][] = [
                'id'     => $taste->getId(),
                'name'   => $taste->get('name'),
                'price'  => $taste->get('price'),
                'weight' => $taste->get('weight'),
            ];
        }

        foreach ($product->getPlugin('taste') as $taste) {
            $data['price'][] = [
                'id'          => $taste->getId(),
                'name'        => $taste->get('name'),
                'coefficient' => $taste->get('coefficient'),
            ];
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'products_stock'])
            ->columns(['count', 'size_id', 'taste_id'])
            ->join(['s' => 'products_size'],  't.size_id = s.id', ['size' => 'name'])
            ->join(['p' => 'products_taste'], 't.taste_id = p.id', ['price' => 'name'])
            ->where(['product_id' => $product->getId()]);

        foreach ($this->execute($select) as $row) {
            $data['stock'][] = $row;
        }

        return $data;
    }

    /*public function eraseChanges()
    {
        $this->execute($this->getSql()->delete('sync_stock_diff'));

        return ['status' => 1];
    }

    public function syncChanges()
    {
        $resp = ['status' => 0];

        $data = file_get_contents(self::SITE . '/sync/stock/data/');

        if (!$data = Json::decode($data)) {
            $resp['error'] = 'Cant parse changes data';
            return $resp;
        }

        foreach ($data as $row) {
            $diff = (int) $row->diff;

            $select = $this->getSql()->select()
                ->from(['t' => 'products_stock'])
                ->columns(['count', 'product_id', 'size_id', 'taste_id'])
                ->join(['p'  => 'products'], new Expression('p.sync_id = ' . (int) $row->product_id), [])
                ->join(['ps' => 'products_size'], new Expression('ps.sync_id = ' . (int) $row->size_id), [])
                ->join(['pt' => 'products_taste'], new Expression('pt.sync_id = ' . (int) $row->taste_id), [])
                ->where([
                    't.product_id = p.id',
                    't.size_id = ps.id',
                    't.taste_id = pt.id',
                ]);

            if(!$result = $this->execute($select)) { continue; }
            $cData = $result->current();

            $count = $cData['count'] + $diff;
            $count = max(0, $count);

            $update = $this->getSql()->update('products_stock')
                ->where([
                    'product_id'    => $cData['product_id'],
                    'size_id'       => $cData['size_id'],
                    'taste_id'      => $cData['taste_id'],
                ])
                ->set(['count' => $count]);

            $this->execute($update);
        }

        file_get_contents(self::SITE . '/sync/stock/erase/');
        $resp['status'] = 1;

        return $resp;
    }

    public function getChanges()
    {
        $sync = SyncStock::getEntityCollection();

        $sync->select()
            ->columns(['diff'])
            ->join(['p'  => 'products'], 'p.id = t.product_id', ['product_id' => 'sync_id'])
            ->join(['ps' => 'products_size'], 'ps.id = t.size_id', ['size_id' => 'sync_id'])
            ->join(['pt' => 'products_taste'], 'pt.id = t.taste_id', ['taste_id' => 'sync_id']);

        $data = [];
        foreach ($sync as $row) {
            $data[] = [
                'product_id'    => $row->get('product_id'),
                'size_id'       => $row->get('size_id'),
                'taste_id'      => $row->get('taste_id'),
                'diff'          => $row->get('diff'),
            ];
        }

        return $data;
    }

    public function fullSync()
    {
        $select = $this->getSql()->select();
        $select->from(['t' => 'products'])
            ->columns(['id']);

        $select
            ->where->notEqualTo('sync_id', 0);

        $result = $this->execute($select);

        if(!$result) { return; }

        foreach ($result as $row) {
            $this->updateProduct($row['id']);
        }

        return ['status' => 1];
    }

    public function updateProduct($productId)
    {
        $product = new Products();
        $product->select()->where(['t.id' => $productId]);

        if (!$product->load()) {
            throw new \Exception('Cant find product with id: ' . $productId);
        }

        $productId = $product->getId();
        $data = file_get_contents(self::SITE . '/sync/stock/product/?id=' . $product->get('sync_id'));

        if (!$data = Json::decode($data)) {
            throw new \Exception('Cant parse product with id: ' . $productId);
        }

        //Clean old taste and size
        $this->execute($this->getSql()->delete('products_size')
            ->where([
                'depend'  => $productId,
                'sync_id'   => 0,
            ]));

        $this->execute($this->getSql()->delete('products_taste')
            ->where([
                'depend'  => $productId,
                'sync_id'   => 0,
            ]));

        //Size
        $baseSelect = $this->getSql()->select('products_size')
            ->columns(['id'])
            ->where(['depend' => $productId]);

        foreach ($data->size as $size) {
            $select = clone $baseSelect;
            $select->where(['sync_id' => $size->id]);

            if ($this->execute($select)->count()) {
                $sql = $this->getSql()->update('products_size')
                    ->where(['sync_id' => $size->id])
                    ->set([
                        'name'   => $size->name,
                        'weight' => (int) $size->weight,
                        'price'  => $size->price,
                    ]);
            } else {
                $sql = $this->getSql()->insert('products_size')
                    ->values([
                        'name'    => $size->name,
                        'price'   => $size->price,
                        'weight'  => (int) $size->weight,
                        'depend'  => $productId,
                        'sync_id' => $size->id,
                    ]);
            }
            $this->execute($sql);
        }

        //Price
        $baseSelect = $this->getSql()->select('products_taste')
            ->columns(['id', 'name', 'coefficient'])
            ->where(['depend' => $productId]);

        foreach ($data->price as $price) {
            $select = clone $baseSelect;
            $select->where(['sync_id' => $price->id]);

            $result = $this->execute($select);

            if ($result->name == $price->name && $result->coefficient == $price->coefficient) {
                continue;
            }

            if ($result->count()) {
                $sql = $this->getSql()->update('products_taste')
                    ->where(['sync_id' => $price->id])
                    ->set([
                        'name' => $price->name,
                        'coefficient' => $price->coefficient,
                    ]);
            } else {
                $sql = $this->getSql()->insert('products_taste')
                    ->values([
                        'name' => $price->name,
                        'depend' => $productId,
                        'coefficient' => $price->coefficient,
                        'sync_id' => $price->id,
                    ]);
            }
            $this->execute($sql);
        }

        //Stock
        $ids = [];
        foreach ($data->stock as $item) {
            $select = $this->getSql()->select(['pt' => 'products_taste'])
                ->columns(['taste_id' => 'id'])
                ->join(['ps' => 'products_size'], new Expression('ps.sync_id = ' . $item->size_id), ['size_id' => 'id'], 'left')
                ->join(['st' => 'products_stock'], new Expression('st.taste_id = pt.id AND st.size_id = ps.id'), ['count', 'stock_id' => 'id'], 'left')
                ->where(['pt.sync_id' => $item->taste_id]);

            $result = $this->execute($select)->current();

            if ($result->count === $item->count) {
                $ids[] = $result->stock_id;
                continue;
            }

            if ($result['stock_id']) {
                $sql = $this->getSql()->update('products_stock')
                    ->where(['id' => $result['stock_id']])
                    ->set(['count' => $item->count]);
            } else {
                $sql = $this->getSql()->insert('products_stock')
                    ->values([
                        'product_id' => $productId,
                        'taste_id' => $result['taste_id'],
                        'size_id' => $result['size_id'],
                        'count' => $item->count,
                    ]);
            }
            $this->execute($sql);

            if ($result['stock_id']) {
                $ids[] = $result['stock_id'];
            } else {
                $ids[] = $this->getSql()->getAdapter()->getDriver()->getLastGeneratedValue();
            }
        }

        if ($ids) {
            $delete = $this->getSql()->delete('products_stock');
            $delete->where
                ->notIn('id', $ids)
                ->equalTo('product_id', $productId);
            $this->execute($delete);
        }
    }
    
    public function addProductToSync($options)
    {
        $sql = $this->getSql();
        $select = $sql->select('products');
        $select
            ->columns(['id'])
            ->where
                ->equalTo('id', $options['product_id'])
                ->notEqualTo('sync_id', 0);

        $result = $this->execute($select);

        if(!$result) {
            return;
        }

        $where = [
            'product_id'    => $options['product_id'],
            'size_id'       => $options['size_id'],
            'taste_id'      => $options['taste_id'],
        ];

        $sync = new SyncStock();
        $sync->select()->where($where);

        if(!$sync->load()) {
            $sync->setVariables($where);
        }

        $sync->load();
        $sync->set('diff', $sync->get('diff') + $options['diff'])
            ->save();
    }*/
}