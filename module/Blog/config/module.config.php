<?php
return [
    'controllers' => [
        'invokables' => [
            'Blog\Controller\Mobile'        => 'Blog\Controller\MobileController',
            'Blog\Controller\Blog'          => 'Blog\Controller\BlogController',
            'Blog\Controller\Exercises'     => 'Blog\Controller\ExercisesController',
            'BlogAdmin\Controller\Blog'     => 'BlogAdmin\Controller\BlogController',
            'BlogAdmin\Controller\Articles' => 'BlogAdmin\Controller\ArticlesController',
            'BlogAdmin\Controller\Comments' => 'BlogAdmin\Controller\CommentsController',
            'BlogAdmin\Controller\Exercises' => 'BlogAdmin\Controller\ExercisesController',
            'BlogAdmin\Controller\ExercisesTypes' => 'BlogAdmin\Controller\ExercisesTypesController',
        ],
    ],
    'router' => [
        'routes' => [
            'mobile' => [
                'type' => 'Hostname',
                'priority' => 600,
                'options' => [
                    'route' => 'm.:domain',
                    'constraints' => ['domain' => '.*',],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'blog' => [
                        'type' => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route' => '/blog',
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'blog' => [
                                'type'    => 'segment',
                                'priority' => 600,
                                'options' => [
                                    'route'    => '[/:url]/',
                                    'constraints' => [
                                        'url' => '.*',
                                    ],
                                    'defaults' => [
                                        'module'     => 'Blog',
                                        'section'    => 'Blog',
                                        'controller' => 'Blog\Controller\Mobile',
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'addComment' => [
                                'type'    => 'segment',
                                'priority' => 500,
                                'options' => [
                                    'route'    => '/add-comment/',
                                    'defaults' => [
                                        'module'     => 'Blog',
                                        'section'    => 'Blog',
                                        'controller' => 'Blog\Controller\Blog',
                                        'action'     => 'addComment',
                                    ],
                                ],
                            ],
                            'articleData' => [
                                'type'    => 'segment',
                                'priority' => 500,
                                'options' => [
                                    'route'    => '/get-article-data/',
                                    'defaults' => [
                                        'module'     => 'Blog',
                                        'section'    => 'Blog',
                                        'controller' => 'Blog\Controller\Blog',
                                        'action'     => 'getArticleData',
                                    ],
                                ],
                            ],
                            'article' => [
                                'type'    => 'segment',
                                'priority' => 700,
                                'options' => [
                                    'route'    => '/article/:url/',
                                    'defaults' => [
                                        'module'     => 'Blog',
                                        'section'    => 'Blog',
                                        'controller' => 'Blog\Controller\Mobile',
                                        'action'     => 'article',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'blog' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/blog',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'blog' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Blog',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'exercises' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/exercises[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Exercises',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'addComment' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/add-comment/',
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Blog',
                                'action'     => 'addComment',
                            ],
                        ],
                    ],
                    'articleData' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/get-article-data/',
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Blog',
                                'action'     => 'getArticleData',
                            ],
                        ],
                    ],
                    'article' => [
                        'type'    => 'segment',
                        'priority' => 700,
                        'options' => [
                            'route'    => '/article/:url/',
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Blog',
                                'action'     => 'article',
                            ],
                        ],
                    ],
                ],
            ],
            'adminBlog' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/admin',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'blog' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/blog/blog[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'BlogAdmin\Controller\Blog',
                                'action'     => 'index',
                                'side'       => 'admin'
                            ],
                        ],
                    ],
                    'articles' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/blog/articles[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Articles',
                                'controller' => 'BlogAdmin\Controller\Articles',
                                'action'     => 'index',
                                'side'       => 'admin'
                            ],
                        ],
                    ],
                    'exercises' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/blog/exercises[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Exercises',
                                'controller' => 'BlogAdmin\Controller\Exercises',
                                'action'     => 'index',
                                'side'       => 'admin'
                            ],
                        ],
                    ],
                    'exercisesTypes' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/blog/exercises-types[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'ExercisesTypes',
                                'controller' => 'BlogAdmin\Controller\ExercisesTypes',
                                'action'     => 'index',
                                'side'       => 'admin'
                            ],
                        ],
                    ],
                    'comments' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/blog/comments[/:action][/:id]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'module'     => 'Blog',
                                'section'    => 'Comments',
                                'controller' => 'BlogAdmin\Controller\Comments',
                                'action'     => 'index',
                                'side'       => 'admin'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'blog' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];