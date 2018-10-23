<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class BrandsSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $help = 'Вставки:<br>{BRAND_NAME} - Производитель';

        $this->addMeta('settings-view-', $help);
        $this->addMeta('settings-products-', $help);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}