<?php

namespace Blog;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Blog\Service\ArticlesService' => 'Blog\Service\ArticlesService',
                'Blog\Service\BlogService' => 'Blog\Service\BlogService',
                'BlogAdmin\Model\Article'  => 'BlogAdmin\Model\Article',
                'BlogAdmin\Model\Blog'     => 'BlogAdmin\Model\Blog',
                'BlogAdmin\Model\Exercise' => 'BlogAdmin\Model\Exercise',
                'BlogAdmin\Model\ExerciseTypes' => 'BlogAdmin\Model\ExerciseTypes',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'BlogAdmin\Service\BlogService' => function ($sm) {
                    $service = new \BlogAdmin\Service\BlogService();
                    $service->setModel(new \BlogAdmin\Model\Blog());
                    return $service;
                },
                'BlogAdmin\Service\ArticlesService' => function ($sm) {
                    $service = new \BlogAdmin\Service\ArticlesService();
                    $service->setModel(new \BlogAdmin\Model\Article());
                    return $service;
                },
                'BlogAdmin\Service\CommentsService' => function ($sm) {
                    $service = new \BlogAdmin\Service\CommentsService();
                    $service->setModel(new \BlogAdmin\Model\Comment());
                    return $service;
                },
                'BlogAdmin\Service\ExercisesService' => function ($sm) {
                    $service = new \BlogAdmin\Service\ExercisesService();
                    $service->setModel(new \BlogAdmin\Model\Exercise());
                    return $service;
                },
                'BlogAdmin\Service\ExercisesTypesService' => function ($sm) {
                    $service = new \BlogAdmin\Service\ExercisesTypesService();
                    $service->setModel(new \BlogAdmin\Model\ExerciseTypes());
                    return $service;
                },
            )
        );
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'ExercisesList'     => 'Blog\View\Helper\ExercisesList',
                'ArticlesList'      => 'Blog\View\Helper\ArticlesList',
                'ArticlesComments'  => 'Blog\View\Helper\ArticlesComments',
                'BlogWidgets'       => 'Blog\View\Helper\BlogWidgets',
                'BlogTypes'         => 'Blog\View\Helper\BlogTypes',
            ),
            'initializers' => array(
                function ($instance, $helperPluginManager) {
                    $sm = $helperPluginManager->getServiceLocator();

                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
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