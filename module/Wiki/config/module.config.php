<?php
return [
    'controllers' => [
        'invokables' => [
            'Wiki\Controller\Wiki'          => 'Wiki\Controller\WikiController',
            'WikiAdmin\Controller\Elements' => 'WikiAdmin\Controller\ElementsController',
            'WikiAdmin\Controller\Calc'     => 'WikiAdmin\Controller\CalcController',
        ],
    ],
    'router' => [
        'routes' => [
            'wiki' => [
                'type'    => 'literal',
                'priority' => 500,
                'options' => [
                    'route'    => '/wiki/',
                    'defaults' => [
                        'module'     => 'Wiki',
                        'section'    => 'Wiki',
                        'controller' => 'Wiki\Controller\Wiki',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'calc' => [
                        'type'    => 'literal',
                        'priority' => 200,
                        'options' => [
                            'route'    => 'calc/',
                            'defaults' => [
                                'action'     => 'calc',
                            ],
                        ],
                    ],
                    'elements' => [
                        'type'    => 'segment',
                        'priority' => 100,
                        'options' => [
                            'route'    => ':url/',
                            'constraints' => [
                                'url' => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'action'     => 'element',
                            ],
                        ],
                    ],
                ],
            ],
            'adminWiki' => [
                'type'    => 'literal',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/wiki/',
                    'defaults' => [
                        'module'     => 'Wiki',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'elements' => [
                        'type'    => 'segment',
                        'priority' => 100,
                        'options' => [
                            'route'    => 'elements/[:action/][:id/]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'section'    => 'Elements',
                                'controller' => 'WikiAdmin\Controller\Elements',
                            ],
                        ],
                    ],
                    'calc' => [
                        'type'    => 'segment',
                        'priority' => 100,
                        'options' => [
                            'route'    => 'calc/[:action/][:id/]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'section'    => 'Calc',
                                'controller' => 'WikiAdmin\Controller\Calc',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'wiki' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];