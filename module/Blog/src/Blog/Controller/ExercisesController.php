<?php
namespace Blog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Blog\Form\CommentForm;
use Blog\Model\Article;
use Blog\Model\Blog;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ExercisesController extends AbstractActionController
{
    public function indexAction()
    {
        $url = $this->params()->fromRoute('url');

        if($url) {
            $subUrl = substr($url, strrpos($url, '/') + 1);
            if($subUrl == 'female') {
                $url = substr($url, 0, strrpos($url, '/'));
            }

            if ($exercise = $this->getExercisesService()->getExercise(['url' => $url])) {
                return $this->exerciseAction($exercise, $subUrl != 'female');
            }

            return $this->send404();
        }


        if($this->getRequest()->isXmlHttpRequest()) {
            $exercises = $this->getExercisesService()->getExercises($this->params()->fromPost());
            return new JsonModel(['html' => $this->viewHelper('exercisesList', $exercises)]);
        }

        $blogService = $this->getBlogService();
        $view = $this->generate('/blog/');
        $view->setVariables([
            'exercises' => \Blog\Model\Exercise::getEntityCollection()
        ] + $blogService->getWidgetsData());
        return $view;
    }

    public function exerciseAction($exercise, $male)
    {
        $view = $this->generate('/blog/');

        $exercisesService = $this->getExercisesService();

        $blogService = $this->getBlogService();
        $exercisesService->addHits($exercise);

        $this->generateMeta($exercise);

        $url = $exercise->getUrl();

        $this->layout()->setVariable('canonical', $url);

        $this->addBreadcrumbs([['url' => '/blog/exercises/', 'name' => 'База упражнений']]);
        $this->addBreadcrumbs([['url' => $url, 'name' => $exercise->get('name')]]);

        $this->generateMeta(null, [], [], [
            'title'       => $exercise->get($male ? 'title_male' : 'title_female'),
            'description' => $exercise->get($male ? 'description_male' : 'description_female'),
        ]);

        $view->setTemplate('blog/exercises/exercise');

        $view->setVariables([
            'male'        => $male,
            'header'      => $exercise->get('name'),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'exercise'    => $exercise,
        ] + $blogService->getWidgetsData());

        return $view;
    }

    /**
     * @return \Blog\Service\ExercisesService
     */
    public function getExercisesService()
    {
        return $this->getServiceLocator()->get('Blog\Service\ExercisesService');
    }

    /**
     * @return \Blog\Service\BlogService
     */
    public function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }
}