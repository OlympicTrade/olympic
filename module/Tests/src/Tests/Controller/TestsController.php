<?php
namespace Tests\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class TestsController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headMeta()->appendName('robots', 'noindex, nofollow');

        return [];
    }

    /**
     * @return \Tests\Service\TestsService
     */
    public function getTestsService()
    {
        return $this->getServiceLocator()->get('Tests\Service\TestsService');
    }
}