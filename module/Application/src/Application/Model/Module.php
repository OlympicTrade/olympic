<?php
namespace Application\Model;

use Aptero\Db\Entity\EntityHierarchy;

class Module extends EntityHierarchy
{
    public function __construct($options = [])
    {
        $this->setTable('site_modules');

        $this->addProperties([
            'name'        => [],
            'module'      => [],
            'module_url'  => ['virtual' => true],
            'section'     => [],
            'section_url' => ['virtual' => true],
            'sort'        => [],
            'sitemap'     => [],
            'admin'       => [],
        ]);

        $this->addPropertyFilterOut('module_url', function($model, $val) {
            return trim(strtolower(implode('-', preg_split('/(?=[A-Z])/', $model->get('module')))), '-');
        });

        $this->addPropertyFilterOut('section_url', function($model, $val) {
            return trim(strtolower(implode('-', preg_split('/(?=[A-Z])/', $model->get('section')))), '-');
        });

        $this->addPlugin('settings', function() {
            $props = new \Aptero\Db\Plugin\Attributes();
            $props->setTable('site_modules_settings');

            return $props;
        });

        if($options) {
            $this->setModuleName($options['name']);
            $this->setSectionName($options['section']);
        }
    }

    /**
     * @param string $module
     * @return $this
     */
    public function setModuleName($module)
    {
        $this->select()->where(array('module' => $module));
        return $this;
    }

    public function setSectionName($section)
    {
        $this->select()->where(array('section' => $section));
        return $this;
    }

    static $settingsCash = [];
    static public function getSettings($name, $section)
    {
        $cashName = $name . ' - ' . $section;
        if(!isset(self::$settingsCash[$cashName])) {
            $module = new self(array(
                'name'     => $name,
                'section'  => $section,
            ));
            self::$settingsCash[$cashName] = $module->getPlugin('settings');
        }
        return self::$settingsCash[$cashName];
    }
}