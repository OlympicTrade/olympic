<?php
return [
    'controllers' => [
        'invokables' => [
            'Wholesale\Controller\Wholesale'      => 'Wholesale\Controller\WholesaleController',
            'WholesaleAdmin\Controller\Wholesale' => 'WholesaleAdmin\Controller\WholesaleController',
        ],
    ],
    'router' => [
        'routes' => [
            'wholesalePrice' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/wholesale/price/',
                    'defaults' => [
                        'module'     => 'Wholesale',
                        'section'    => 'Wholesale',
                        'controller' => 'Wholesale\Controller\Wholesale',
                        'action'     => 'price',
                    ],
                ],
            ],
            'adminWholesale' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/wholesale/wholesale[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Wholesale',
                        'section'    => 'Wholesale',
                        'controller' => 'WholesaleAdmin\Controller\Wholesale',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'Wholesale' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];