<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Balance\Controller\Balance'          => 'Balance\Controller\BalanceController',
            'BalanceAdmin\Controller\Cash'        => 'BalanceAdmin\Controller\CashController',
            'BalanceAdmin\Controller\Statistic'   => 'BalanceAdmin\Controller\StatisticController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'balance' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/update-balance/',
                    'defaults' => array(
                        'module'     => 'Balance',
                        'section'    => 'Cash',
                        'controller' => 'Balance\Controller\Balance',
                        'action'     => 'updateBalance',
                    ),
                ),
            ),
            'adminCash' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/balance/cash[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Balance',
                        'section'    => 'Cash',
                        'controller' => 'BalanceAdmin\Controller\Cash',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminStatistic' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/balance/statistic[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Balance',
                        'section'    => 'Statistic',
                        'controller' => 'BalanceAdmin\Controller\Statistic',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'balance' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);