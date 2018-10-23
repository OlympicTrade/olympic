<?php
namespace Catalog\View\Helper;

use Zend\Json\Json;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductText extends AbstractHelper
{
    public function __invoke($html)
    {
        $html = preg_replace_callback('~SIZE_TABLE(.*)SIZE_TABLE~', function($dataStr) {
            return $this->sizeTable($dataStr[1]);
        }, $html);

        return $html . '<div class="clear"></div>';
    }

    protected function sizeTable($dataStr)
    {		
	
		$str = str_replace(["&#39;", "&quot;"], ['"', '"'], $dataStr);
        if(!$blocs = Json::decode($str)) { return ''; }

        $html =
            '<div class="std-table size-table">'
            .'<table>';

        for($h = 0; $h < 12; $h++) {
            $html .= '<tr>';

            for($w = 0; $w < 12; $w++) {
                if($h == 0 && $w == 0) {
                    $html .= '<td class="wh">см/кг</td>';
                } elseif ($h == 0) {
                    $html .= '<td class="w">' . (($w * 5) + 35) . '</td>';
                } elseif ($w == 0) {
                    $html .= '<td class="h">' . (($h * 5) + 140) . '</td>';
                } else {
                    $html .= '<td></td>';
                }
            }

            $html .= '</tr>';
        }

        $cellSize = 8.333;

       /* $blocs = [
            [
                'name'   => 'XXL',
                'size' => ['l' => 9, 'w' => 2, 't' => 4, 'h' => 7],
                'color'  => '#ee7923'
            ],
            [
                'name'   => 'XL',
                'size' => ['l' => 6, 'w' => 3, 't' => 2, 'h' => 9],
                'color'  => '#eecf23'
            ],
            [
                'name'   => 'L',
                'size' => ['l' => 4, 'w' => 3, 't' => 1, 'h' => 6],
                'color'  => '#08ce65'
            ],
            [
                'name'   => 'M',
                'size' => ['l' => 2, 'w' => 3, 't' => 0, 'h' => 4],
                'color'  => '#23aeee'
            ],
            [
                'name'   => 'S',
                'size' => ['l' => 0, 'w' => 3, 't' => 0, 'h' => 2],
                'color'  => '#d674da'
            ],
        ];*/

        foreach ($blocs as $block) {
            $html .=
                '<div class="block" style="'
                    .'left:' . ($cellSize * ($block->size->l + 1)) . '%; '
                    .'top:' . ($cellSize * ($block->size->t + 1)) . '%; '
                    .'width:' . ($cellSize * ($block->size->w)) . '%; '
                    .'height:' . ($cellSize * ($block->size->h)) . '%; '
                    .'line-height:' . (20 * ($block->size->h)) . 'px; '
                    .'background: ' . $block->color . ';'
                .'">' . $block->name . '</div>';
        }

        $html .=
            '</table>'
        .'</div>';

        return $html;
    }
}