<?php
namespace ApplicationAdmin\Controller;

use ApplicationAdmin\Form\ContentEditForm;
use ApplicationAdmin\Model\Content;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ContentController extends AbstractActionController
{
    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        $content = new Content();
        $content->setId($id)->remove();

        return new JsonModel();
    }

    public function editAction()
    {
        if($this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');

            $content = new Content();
            $content->setId($id)->load();
            $content->unserializeArray($this->params()->fromPost());
            $content->save();

            $helper = $this->getServiceLocator()->get('ViewHelperManager')->get('adminContentList');

            $reps = [
                'id'   => $content->getId(),
                'html' => $helper($content),
            ];

            return new JsonModel($reps);
        }

        $view = new ViewModel();
        $view->setTerminal(true);

        $content = new Content();

        $id = $this->params()->fromQuery('id');
        $module = $this->params()->fromQuery('module');
        $depend = $this->params()->fromQuery('depend');

        if($id) {
            $content->setId($id);
        } elseif($module && $depend) {
            $content->setVariables([
                'module' => $module,
                'depend' => $depend,
            ]);
        } else {
            throw new \Exception('unknown Content module or Id');
        }

        $form = new ContentEditForm();
        $form->setModel($content);
        $form->setData($content->serializeArray());

        $view->setVariables([
            'form' => $form
        ]);

        return $view;
    }
}