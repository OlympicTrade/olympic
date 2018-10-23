<?php

namespace Blog\Service;

use Aptero\Cookie\Cookie;
use Aptero\Service\AbstractService;
use Blog\Model\Article;
use Blog\Model\Blog;
use Blog\Model\BlogTypes;
use Blog\Model\Comment;
use Blog\Model\Exercise;
use User\Service\AuthService;
use Zend\Db\Sql\Expression;

class ExercisesService extends AbstractService
{
    public function getExercise($filters)
    {
        $exercise = new Exercise();

        if(!empty($filters['url'])) {
            $exercise->select()->where(['url' => $filters['url']]);
        }

        return $exercise->load();
    }

    public function getExercises($filters)
    {
        $exercises = Exercise::getEntityCollection();

        $select = $exercises->select();
        $select->group('t.id');

        if(!empty($filters['type'])) {
            foreach ($filters['type'] as $typeId => $vals) {
                $prefix = 'et' . $typeId;
                $prefix2 = 'etv' . $typeId;
                $select
                    ->join([$prefix => 'blog_exercises_types'], new Expression( $prefix . '.type_id = ' . $typeId), [])
                    ->join([$prefix2 => 'blog_exercises_types_vals'], new Expression('t.id = ' . $prefix2 . '.depend AND ' . $prefix . '.id = ' . $prefix2 . '.type_id'), [])
                    ->where([$prefix . '.id' => $vals]);
            }
        }

        $exercises->setSelect($select);
        //$exercises->dump();die();

        return $exercises;
    }

    public function addHits(Exercise $exercise)
    {
        $hits = Cookie::getCookie('blog-hits', true);
        if($hits && in_array($exercise->getId(), $hits)) {
            return;
        }

        $exercise->set('hits', $exercise->get('hits') + 1)->save();
        $hits[] = $exercise->getId();
        Cookie::setCookie('exercises-hits', $hits, 1);
    }
}