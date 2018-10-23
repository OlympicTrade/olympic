<?php
namespace Wiki\View\Helper;

use Zend\View\Helper\AbstractHelper;

class WikiCalc extends AbstractHelper
{
    public function __invoke($calc, $filters)
    {
        $gen = $filters['gender'] == 'male' ? 'male' : 'female';

        $result = [
            
        ];
        
        
        foreach($calc->getPlugin('elements') as $element) {
            $result[] =
                '<tr>'
                    .'<td class="key">' . $element->get('name') . '</td>'
                    .'<td class="val">100 мкг</td>'
                .'</tr>';
        }

        $html =
            '<table>'
                .'<tr><th colspan="2">Витамины</th></tr>'
                .'<tr>'
                    .'<td class="key">Витамин A</td>'
                    .'<td class="val">100 мкг</td>'
                .'</tr>'
            .'</table>';

        return $html;
    }
}