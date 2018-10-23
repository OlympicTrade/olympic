<?php

namespace Callcenter;

use CallcenterAdmin\Model\Call;
use CallcenterAdmin\Model\WsClient;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return [
            'invokables' => [

            ],
            'initializers' => [
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ],
            'factories' => [
                'CallcenterAdmin\Service\CallcenterService' => function ($sm) {
                    return new \CallcenterAdmin\Service\CallcenterService(new Call());
                },
                'CallcenterAdmin\Service\WholesaleService' => function ($sm) {
                    return new \CallcenterAdmin\Service\WholesaleService(new WsClient());
                },
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Admin',
                ]
            ]
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}