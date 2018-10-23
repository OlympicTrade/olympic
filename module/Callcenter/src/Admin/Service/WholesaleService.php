<?php
namespace CallcenterAdmin\Service;

use Aptero\Service\Admin\TableService;
use CallcenterAdmin\Model\WsClient;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WholesaleService extends TableService
{
    public function load2Gis()
    {
        $inputFileName = DATA_DIR . '/sources/wholesale2gis.xlsx';

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($inputFileName);


        $worksheet = $spreadsheet->getActiveSheet();
        $r = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $r++;
            if($r == 1) continue;

            $client = new WsClient();

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $c = 0;

            $data = [];
            $phones = '';
            foreach ($cellIterator as $cell) {
                $c++;
                switch ($c) {
                    case 1:  $data['source_id'] = '2gis-' . preg_replace('/\D+/', '', $cell->getValue()); break;
                    case 4:  $data['name'] = $cell->getValue(); break;
                    case 10: $data['city'] = trim(str_replace(['г.'], '', $cell->getValue())); break;
                    case 13: $data['phones']  = $cell->getValue(); break;
                    case 14: $data['phones'] .= ($phones ? ';' : '') . $cell->getValue(); break;
                    case 11: $data['address'] = $cell->getValue(); break;
                    case 12: $data['route'] = $cell->getValue(); break;
                    case 16: $data['site'] = $cell->getValue(); break;
                    case 17: $data['email'] = $cell->getValue(); break;
                    case 18: $data['latitude'] = $cell->getValue(); break;
                    case 19: $data['longitude'] = $cell->getValue(); break;
                    case 23:
                        if($data['site']) break;
                        $data['site'] = $cell->getValue();
                        break;
                }
            }

            $data['phones'] = str_replace(';', "\n", $data['phones']);

            $client->select()->where(['source_id' => $data['source_id']]);
            $client->load();

            /*$select = $this->getSql()->select($client->table())
                ->where(['source_id' => $client->get('source_id')]);*/

            $client->setVariables($data)->save();
        }
    }
}