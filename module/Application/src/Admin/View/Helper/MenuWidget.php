<?php
namespace ApplicationAdmin\View\Helper;

use Application\Model\Module;
use Zend\View\Helper\AbstractHelper;

class MenuWidget extends AbstractHelper
{
    /**
     * @var \Aptero\Db\Entity\EntityCollectionHierarchy
     */
    protected $modules;

    public function __construct()
    {
        $moduleMdl = new Module();

        $this->modules = $moduleMdl
            ->setParentId(0)
            ->getCollection();
        $this->modules->select()
            ->order('t.sort');
    }

    public function __invoke()
    {
        $view = $this->getView();

        $html = '<ul class="menu">';

        $module = $view->engine->module;

        $curModule  = $module->get('module');
        $curSection  = $module->get('section');

        foreach($this->modules as $module) {

            if(!$module->get('admin')) {
                continue;
            }

            $children = $module->getChildren();
            $childrenCount = $children->count();

            $hasActive = false;
            $submenuHtml = '';
            if($childrenCount) {
                $submenuHtml =
                    '<div class="submenu">'
                        .'<div class="arr"></div>';
                foreach($children as $child) {
                    $isActive = $curSection == $child->get('section') && $curModule == $child->get('module');
                    $hasActive = $isActive || $hasActive;

                    $url = $view->url('admin', array('module' => $child->get('module_url'), 'section' => $child->get('section_url')));
                    $submenuHtml .=
                        '<div' . ($isActive ? ' class="active"' : '') . '>'
                            .'<a href="' . $url . '">' . $child->get('name') . '</a>'
                            .'</div>';
                }
                $submenuHtml .=
                    '</div>';
            }

            $emClass =
                ($hasActive ? 'open acpr' : '')
                    .(!$childrenCount && $curSection == $module->get('section_url') && $curModule == $module->get('module_url') ? ' active' : '');

            $url = $view->url('admin', array('module' => $module->get('module_url'), 'section' => $module->get('section_url')));

            $html .=
                '<li' . ($emClass ? ' class="' . $emClass . '"' : '') . '>'
                    .'<a href="' . $url . '">'
                    .'<i class="icon fa module-icon-' . $module->get('section_url') . '"></i>'
                    . $module->get('name')
                    .($childrenCount ? '<i class="fa fa-angle-right arr"></i>' : '')
                    .'</a>'
                    .$submenuHtml
                .'</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
?>