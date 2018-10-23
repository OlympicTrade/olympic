<?php

namespace Wiki;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'wikiCalc' => 'Wiki\View\Helper\WikiCalc',
            ],
        ];
    }
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'WikiAdmin\Model\Element'  => 'WikiAdmin\Model\Element',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'WikiAdmin\Service\ElementsService' => function ($sm) {
                    return new \WikiAdmin\Service\ElementsService($sm->get('WikiAdmin\Model\Element'));
                },
                'WikiAdmin\Service\CalcService' => function ($sm) {
                    return new \WikiAdmin\Service\CalcService($sm->get('WikiAdmin\Model\Calc'));
                },
            )
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Admin',
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}