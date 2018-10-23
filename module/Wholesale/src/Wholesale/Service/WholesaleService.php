<?php

namespace Wholesale\Service;

use Aptero\Service\AbstractService;
use Aptero\MSOffice\Excel;
use Catalog\Model\Catalog;
use Catalog\Model\Product;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Aptero\String\Price;

class WholesaleService extends AbstractService
{
    protected $lastRow = 0;

    public function productsExcel($filter = array())
    {

        $percents = [
            'min' => ['sum' => 15000, 'percent' => 20],
            'med' => ['sum' => 30000, 'percent' => 25],
            'max' => ['sum' => 45000, 'percent' => 30],
        ];

        $excel = new Excel();

        //logo
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(PUBLIC_DIR . '/images/opt_logo.jpg');
        $drawing->setWorksheet($excel->getAs());
        $drawing->setHeight(61);
        $drawing->setCoordinates('A2');

        //Header
        $excel
            ->setTitle('Прайс от ' . date('d.m.Y'))
            ->setVal('Сумма', 5, 2)
            ->setVal('Скидка %', 5, 3)
            ->setVal('Итого', 5, 4)
            ->nextRow()
            ->nextRow()
            ->setVal('Наименование')->setColWidth(1, 30)
            ->setVal('Размер')->setColWidth(2, 15)
            ->setVal('Вкус/Цвет')->setColWidth(3, 30)
            ->setVal('Рек. розн. цена')->setColWidth(4, 15)
            ->setVal('Заказ шт.')->setColWidth(5, 14)
            ->setVal('Сумма')->setColWidth(6, 14)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['min']['sum']))->setColWidth(7, 16)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['med']['sum']))->setColWidth(8, 16)
            ->setVal('Заказ от ' . Price::nbrToStr($percents['max']['sum']))->setColWidth(9, 16)
            ->nextRow()
            ->setVal('Скидка ' . $percents['min']['percent'] . '%', 7)
            ->setVal('Скидка ' . $percents['med']['percent'] . '%', 8)
            ->setVal('Скидка ' . $percents['max']['percent'] . '%', 9)
            ->nextRow();

        $styleArray = [
            'font' => ['bold' => true,],
        ];

        $menuRange = $excel->getRange(6, 1, 7, 9);
        $sumRange = $excel->getRange(2, 5, 4, 5);

        $excel->getAs()->getStyle($menuRange)->applyFromArray($styleArray);
        $excel->getAs()->getStyle($sumRange)->applyFromArray($styleArray);

        $this->generateCategory($excel);

        //Summary
        $percentFormula =
            '=IF(F2<' . $percents['min']['sum'] . ', 0, ' .
                'IF(F2<' . $percents['med']['sum'] . ', ' . $percents['min']['percent'] . ', ' .
                    'IF(F2<' . $percents['max']['sum'] . ', ' . $percents['med']['percent'] . ', ' .
                        $percents['max']['percent'] .
                    ')' .
                ')' .
            ')';


        $firstRow = 7;
        $lastRow  = $this->lastRow;

        $excel->setVal('=SUM(' . $excel->getCoords($firstRow, 6) . ':' . $excel->getCoords($lastRow, 6) . ')', 6, 2);
        $excel->setVal($percentFormula, 6, 3);
        $excel->setVal('=' . $excel->getCoords(2, 6) . '*(1-' . $excel->getCoords(3, 6) . '/100)', 6, 4);

        //die();
        $excel->send();
    }

    protected function generateCategory(Excel $excel)
    {
        $catalog = Catalog::getEntityCollection();
        $catalog->select()
            ->where(['parent' => 0]);

        foreach($catalog as $category) {
            $cellRange = $excel->getRange(null, null, null, 9);

            $excel->getAs()->mergeCells($cellRange);

            $styleArray = [
                'borders' => ['top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]],
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => [
                        'argb' => '191717',
                    ],
                ],
            ];

            $excel->getAs()->getStyle($cellRange)->applyFromArray($styleArray);

            $excel->setVal($category->get('name'))
                ->nextRow();

            $categoryIds = $this->getCatalogService()->getCatalogIds($category);
            //echo $category->get('name') . "\n";
            $this->generateProducts(['catalog' => $categoryIds], $excel);
        }
    }

    protected function generateProducts($filters, $excel)
    {
        //Products
        $products = (new Product())->addProperties([
            'size_id'   => [],
            'size'      => [],
            'taste_id'  => [],
            'taste'     => [],
        ])->getCollection();

        $filters += [
            'join'      => [],
            'columns'   => ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'discount', 'url'],
            'optPrice'  => true,
            'group'     => 'ps.id',
        ];

        $select = $this->getProductsService()->getProductsSelect($filters);
        $select
            ->order('t.popularity, t.name, pss.id, pst.id')
            ->where
            ->equalTo('brand_id', 42)
            ->nest()
                ->greaterThan('pst.coefficient', 0)
                ->and
                ->greaterThan('pss.price', 0)
            ->unnest();

        $products->setSelect($select);
        //$products->dump();die();

        foreach($products as $product) {
            //echo $product->get('name') . "\n";
            $excel->setVal($product->get('name'));

            $price = $product->get('price_old');

            $excel
                ->setVal($product->get('size'))
                ->setVal($product->get('taste'))
                ->setVal($price)
                ->setVal(0);

            $x = $excel->x();

            $excel
                ->setVal('=' . $excel->getCoords(null, $x - 1) . '*' . $excel->getCoords(null, $x - 2))
                ->setVal((int) ($price * 0.80))
                ->setVal((int) ($price * 0.75))
                ->setVal((int) ($price * 0.70))
                ->nextRow();

            $this->lastRow = $excel->y() - 1;
        }
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }

    /**
     * @return \Catalog\Service\CatalogService
     */
    protected function getCatalogService()
    {
        return $this->getServiceManager()->get('Catalog\Service\CatalogService');
    }
}