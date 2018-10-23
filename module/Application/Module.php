<?php
namespace Application;

use Application\Model\Settings;
use Aptero\Compressor\Compressor;
use Aptero\Mail\Mail;
use Zend\Mvc\MvcEvent;

use Zend\Mvc\I18n\Translator;
use Zend\Mvc\View\Http\ViewManager;
use Zend\Validator\AbstractValidator;

use Zend\Session\SessionManager;
use Zend\Session\Container;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

class Module
{
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $application   = $mvcEvent->getApplication();
        $sm = $application->getServiceManager();
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $side = substr($_SERVER['REQUEST_URI'], 0, 7) == '/admin/' ? 'admin' : 'public';
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function(MvcEvent $event) {
            $viewModel = $event->getViewModel();
            $viewModel->setTemplate('error/layout');
        }, -200);

        //Errors handler
        if ($side == 'admin') {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'errorDispatcherAdmin'), 100);
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderAdmin'), 100);
        } else {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'errorDispatcher'), 100);
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderPublic'), 100);
        }

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'mvcPreDispatch'), 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initMail'), 100);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'initTranslate'));

        //Default Db Adapter
        StaticDbAdapter::setStaticAdapter($sm->get('Zend\Db\Adapter\Adapter'));
        StaticCacheAdapter::setStaticAdapter($sm->get('DataCache'), 'data');
        StaticCacheAdapter::setStaticAdapter($sm->get('HtmlCache'), 'html');

        //Errors log
        /*if(MODE == 'public') {
            $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use ($sm) {
                $mail = new Mail();
                $mail->setTemplate(
                    MODULE_DIR . '/Application/view/error/error-mail.phtml',
                    MODULE_DIR . '/Application/view/mail/error.phtml')
                    ->setHeader('Ошибка')
                    ->setVariables(['exception' => $e->getParam('exception')])
                    ->addTo('info@aptero.ru')
                    ->send();
            });
        }*/
    }

    public function onRenderAdmin(MvcEvent $mvcEvent)
    {

    }

    public function onRenderPublic(MvcEvent $mvcEvent)
    {
        $this->compressCssJs();
    }

    public function compressCssJs()
    {
        $compressor = new Compressor();

        $version = Settings::getInstance()->get('html_css_js_version');

        $compressor->compress([
            PUBLIC_DIR . '/fonts/fonts.css',
            PUBLIC_DIR . '/css/libs/reset.css',
            PUBLIC_DIR . '/css/libs/rangeslider.css',
            PUBLIC_DIR . '/css/libs/owlcarousel.css',
            PUBLIC_DIR . '/css/libs/fancybox.css',
            PUBLIC_DIR . '/css/libs/tooltips.css',
            PUBLIC_DIR . '/css/libs/grid.css',
            PUBLIC_DIR . '/css/elements.css',
            PUBLIC_DIR . '/css/main.css',
        ],  PUBLIC_DIR . '/css/compress-' . $version . '.css');
		
		 $jsDesktop = [
            0  => PUBLIC_DIR . '/js/config.js',
            20 => PUBLIC_DIR . '/js/libs/fancybox/fancybox.js',
            25 => PUBLIC_DIR . '/js/libs/history.js',
            30 => PUBLIC_DIR . '/js/libs/inputmask.js',
            35 => PUBLIC_DIR . '/js/libs/owlcarousel.js',
            40 => PUBLIC_DIR . '/js/libs/imgzoom.js',
            45 => PUBLIC_DIR . '/js/libs/aptero.js',
            46 => PUBLIC_DIR . '/js/libs/sidebar.js',
            47 => PUBLIC_DIR . '/js/libs/paginator.js',
            50 => PUBLIC_DIR . '/js/libs/cookie.js',
            53 => PUBLIC_DIR . '/js/libs/tooltips.js',
            55 => PUBLIC_DIR . '/js/libs/products-list.js',
            60 => PUBLIC_DIR . '/js/libs/cart.js',
            65 => PUBLIC_DIR . '/js/libs/form-validator.js',
            70 => PUBLIC_DIR . '/js/libs/rangeslider.js',
            75 => PUBLIC_DIR . '/js/libs/counter.js',
            80 => PUBLIC_DIR . '/js/main.js',
            85 => PUBLIC_DIR . '/js/catalog.js',
            90 => PUBLIC_DIR . '/js/products.js',
        ];

        $compressor->compress($jsDesktop,  PUBLIC_DIR . '/js/compress-' . $version . '.js');

        //Mobile
        $compressor->compress([
            PUBLIC_DIR . '/mobile/css/libs/reset.css',
            PUBLIC_DIR . '/mobile/css/libs/owlcarousel.css',
            PUBLIC_DIR . '/mobile/css/libs/fancybox.css',
            PUBLIC_DIR . '/mobile/css/libs/grid.css',
            PUBLIC_DIR . '/mobile/css/elements.css',
            PUBLIC_DIR . '/mobile/css/main.css',
        ],  PUBLIC_DIR . '/mobile/css/compress-' . $version . '.css');
		
		$jsMobile = [
            0  => PUBLIC_DIR . '/mobile/js/config.js',
            20 => PUBLIC_DIR . '/js/libs/fancybox/fancybox.js',
            25 => PUBLIC_DIR . '/js/libs/history.js',
            30 => PUBLIC_DIR . '/js/libs/inputmask.js',
            40 => PUBLIC_DIR . '/js/libs/aptero.js',
            45 => PUBLIC_DIR . '/js/libs/cookie.js',
            47 => PUBLIC_DIR . '/js/libs/paginator.js',
            50 => PUBLIC_DIR . '/js/libs/products-list.js',
            55 => PUBLIC_DIR . '/js/libs/cart.js',
            60 => PUBLIC_DIR . '/js/libs/form-validator.js',
            70 => PUBLIC_DIR . '/js/libs/counter.js',
            75 => PUBLIC_DIR . '/mobile/js/main.js',
            80 => PUBLIC_DIR . '/mobile/js/catalog.js',
            85 => PUBLIC_DIR . '/mobile/js/products.js',
        ];

        $compressor->compress($jsMobile, PUBLIC_DIR . '/mobile/js/compress-' . $version . '.js');
    }

    public function mvcPreDispatch(MvcEvent $mvcEvent)
    {
        $module  = $mvcEvent->getRouteMatch()->getParam('module');
        $section = $mvcEvent->getRouteMatch()->getParam('section');

        $mvcEvent->getApplication()->getServiceManager()->get('Application\Model\Module')
            ->setModuleName($module)
            ->setSectionName($section)
            ->load();
    }

    public function errorDispatcherAdmin(MvcEvent $mvcEvent)
    {
        /** @var \Zend\Mvc\View\Http\ViewManager $viewManager */
        $viewManager = $mvcEvent->getApplication()->getServiceManager()->get('HttpViewManager');


        /*$notFoundStrategy = $viewManager->getRouteNotFoundStrategy();
        $notFoundStrategy->setNotFoundTemplate('error/admin/not-found');

        $exceptionStrategy = $viewManager->getExceptionStrategy();
        $exceptionStrategy->setExceptionTemplate('error/admin/exception');*/
    }

    public function errorDispatcher(MvcEvent $mvcEvent)
    {
        /*$viewManager = $mvcEvent->getApplication()->getServiceManager()->get('ViewManager');

        $mvcEvent->getViewModel()->setTemplate('layout/error-layout');

        $notFoundStrategy = $viewManager->getRouteNotFoundStrategy();
        $notFoundStrategy->setNotFoundTemplate('error/not-found');

        $exceptionStrategy = $viewManager->getExceptionStrategy();
        $exceptionStrategy->setExceptionTemplate('error/exception');*/
    }

    public function initMail(MvcEvent $mvcEvent)
    {
        $settings = $mvcEvent->getApplication()->getServiceManager()->get('Settings');
        Mail::setOptions([
            'sender'    => [
                'email' => $settings->get('mail_email'),
                'name'  => $settings->get('mail_sender'),
            ],
            'connection' => [
                'name' => $settings->get('mail_smtp'),
                'host' => $settings->get('mail_smtp'),
                'port' => 465,
                'connection_class' => 'login',
                'connection_config' => [
                    'username' => $settings->get('mail_email'),
                    'password' => $settings->get('mail_password'),
                    'ssl' => 'ssl'
                ],
            ]
        ]);
    }

    public function initTranslate(MvcEvent $mvcEvent)
    {
        $aliases = array(
            'ru' => 'ru_RU',
            'en' => 'en_US',
        );
        $locale = $mvcEvent->getRouteMatch()->getParam('locale');

        if($locale && isset($aliases[$locale])) {
            \Locale::setDefault($aliases[$locale]);
        } else {
            \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            \Locale::setDefault('ru_RU');
        }

        $translator = $mvcEvent->getApplication()->getServiceManager()->get('translator')->setLocale(\Locale::getDefault());
        $mvcEvent->getApplication()->getServiceManager()->get('ViewHelperManager')->get('translate')->setTranslator($translator);

        $formTranslator = new Translator($translator);

        AbstractValidator::setDefaultTranslator($formTranslator, 'Forms');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'IsMobile'              => 'Aptero\View\Helper\IsMobile',
                'Breadcrumbs'           => 'Application\View\Helper\Breadcrumbs',
                'BtnSwitcher'           => 'Aptero\View\Helper\BtnSwitcher',
                'FormRow'               => 'Aptero\Form\View\Helper\FormRow',
                'FormErrors'            => 'Aptero\Form\View\Helper\FormErrors',
                'Fieldset'              => 'Aptero\Form\View\Helper\Fieldset',
                'FormElement'           => 'Aptero\Form\View\Helper\FormElement',
                'FormImage'             => 'Aptero\Form\View\Helper\FormImage',
                'AdminFormFileManager'  => 'Aptero\Form\View\Helper\Admin\FormFileManager',
                'AdminFormTreeSelect'   => 'Aptero\Form\View\Helper\FormTreeSelect',
                'AdminFormImage'        => 'Aptero\Form\View\Helper\Admin\Image',
                'AdminFormProps'        => 'Aptero\Form\View\Helper\Admin\Props',
                'AdminFormAttrs'        => 'Aptero\Form\View\Helper\Admin\Attrs',
                'AdminFormImages'       => 'Aptero\Form\View\Helper\Admin\Images',
                'AdminFormContentImages'=> 'Aptero\Form\View\Helper\Admin\ContentImages',
                'AdminFormProductImages'=> 'Aptero\Form\View\Helper\Admin\ProductImages',
                'AdminFormExercisesImages'=> 'Aptero\Form\View\Helper\Admin\ExercisesImages',
                'AdminFormFile'         => 'Aptero\Form\View\Helper\Admin\File',
                'AdminFormRow'          => 'Aptero\Form\View\Helper\Admin\FormRow',
                'AdminPrice'            => 'Aptero\View\Helper\Admin\Price',
                'AdminTableList'        => 'Aptero\View\Helper\Admin\TableList',
                'AdminMenuWidget'       => 'ApplicationAdmin\View\Helper\MenuWidget',
                'AdminFormCollection'   => 'Aptero\Form\View\Helper\Admin\Collection',

                'AdminMessenger'        => 'ApplicationAdmin\View\Helper\Messenger',
                'AdminContentList'      => 'ApplicationAdmin\View\Helper\ContentList',
                'ContentRender'         => 'Application\View\Helper\ContentRender',
                'GenerateMeta'          => 'Application\View\Helper\GenerateMeta',
                'WidgetNav'             => 'Application\View\Helper\WidgetNav',
                'TextBlock'             => 'Application\View\Helper\TextBlock',
                'HtmlBlocks'            => 'Application\View\Helper\HtmlBlocks',
                'Header'                => 'Application\View\Helper\Header',
                'HeaderBlack'           => 'Application\View\Helper\HeaderBlack',
                'Price'                 => 'Aptero\View\Helper\Price',
                'SubStr'                => 'Aptero\View\Helper\SubStr',
                'Escape'                => 'Aptero\View\Helper\Escape',
                'Date'                  => 'Aptero\View\Helper\Date',
                'NotEmpty'              => 'Aptero\View\Helper\NotEmpty',
                'Link'                  => 'Aptero\View\Helper\Link',
                'Video'                 => 'Aptero\View\Helper\Video',
                'Stars'                 => 'Aptero\View\Helper\Stars',
                'Declension'            => 'Aptero\View\Helper\Declension',
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

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'ApplicationAdmin\Service\SettingsService'  => 'ApplicationAdmin\Service\SettingsService',
                'Application\Service\SystemService'  => 'Application\Service\SystemService',
                'Application\Service\SitemapService' => 'Application\Service\SitemapService',
                'Application\Model\Module'          => 'Application\Model\Module',
                'ApplicationAdmin\Model\Page'       => 'ApplicationAdmin\Model\Page',
            ),
            'factories' => array(
                'ApplicationAdmin\Service\PageService' => function ($sm) {
                    return new \ApplicationAdmin\Service\PageService($sm->get('ApplicationAdmin\Model\Page'));
                },
                'ApplicationAdmin\Service\CountriesService' => function ($sm) {
                    return new \ApplicationAdmin\Service\CountriesService($sm->get('ApplicationAdmin\Model\Country'));
                },
                'Settings' => function ($sm) {
                    $settings = new \Application\Model\Settings();
                    $settings->setId(1);
                    return $settings;
                },
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validator'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validator'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__   => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__.'Admin'   => __DIR__ . '/src/Admin',
                ),
            ),
        );
    }
}
