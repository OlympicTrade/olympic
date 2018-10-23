<?php
namespace News\Service;

use News\Model\News;
use Aptero\Service\AbstractService;

class NewsService extends AbstractService
{
    /**
     * @param int $page
     * @return Paginator
     */
    public function getPaginator($page)
    {
        $news = new News();
        $news = $news->getCollection();

        return $news->getPaginator($page, 10);
    }

}