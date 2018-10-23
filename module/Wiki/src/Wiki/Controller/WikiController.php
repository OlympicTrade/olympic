<?php
namespace Wiki\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Wiki\Model\Element;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class WikiController extends AbstractActionController
{
    public function calcAction()
    {
        $request = $this->getRequest();
        
        if($request->isPost()) {
            $this->getWikiService()->calc($data);

            return new JsonModel([
                'html' => $this->viewHelper('wikiCalc', [])
            ]);
        }
        
        $view = new ViewModel();
        $view->setTerminal(true);

        return $view;
    }

    public function elementAction()
    {
        var_dump($this->params()->fromRoute());
        $url = $this->params()->fromRoute('url');

        $element = new Element();
        $element->select()->where(['url' => $url]);

        if(!$element->load()) {
            return $this->send404();
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables([
            'element' => $element
        ]);

        return $view;
    }
    
    public function indexAction()
    {
        $this->generate();

        $view = $this->generate();

        return $view;
    }

    /**
     * @return \Wiki\Service\WikiService
     */
    public function getWikiService()
    {
        return $this->getServiceLocator()->get('Wiki\Service\WikiService');
    }
}