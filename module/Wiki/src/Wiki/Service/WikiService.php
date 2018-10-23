<?php

namespace Wiki\Service;

use Aptero\Service\AbstractService;
use Wiki\Model\Calc;

class WikiService extends AbstractService
{
    public function calc($data = [])
    {
        $data = [
            'age_to'    => '',
            'age_from'  => '',
        ] + $data;
        
        $calc = new Calc();
        $calc->select()->where
            ->greaterThanOrEqualTo('age_from', $data['age_from'])
            ->lessThan('age_to', $data['age_to']);
        
        return $calc;
    }
}