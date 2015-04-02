<?php
return array(
	'controllers' => array(
        'invokables' => array(
            'app'			=> 'Application\Controller\IndexController',
        	'Application\Controller\AuthController'		=> 'Application\Controller\AuthController',
        	'Application\Controller\CallbackController'		=> 'Application\Controller\CallbackController',
        	'Application\Controller\RedirecturiController'		=> 'Application\Controller\RedirecturiController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'application' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller'    => 'app',
                        'action'        => 'index',
                    ),
                ),
            	'may_terminate' => true,
            	'child_routes' => array(
            		'auth' => array(
            			'type' => 'segment',
            			'options' => array(
            				'route' => 'auth',
            				'defaults' => array(
            					'controller'    => 'Application\Controller\AuthController',
            					'action'        => 'index',
            				)
            			)
            		),
            		'callback' => array(
            			'type' => 'segment',
            			'options' => array(
            				'route' => 'callback',
            				'defaults' => array(
            					'controller'    => 'Application\Controller\CallbackController',
                				'action'        => 'index',
                  			)
            			)
            		),
            		'redirecturi' => array(
            			'type' => 'segment',
            			'options' => array(
            				'route' => 'redirecturi',
            				'defaults' => array(
            					'controller'    => 'Application\Controller\RedirecturiController',
            					'action'        => 'index',
            				)
            			)
            		),
            	)
            ),
//         	'callback' => array(
//         		'type'    => 'literal',
//         		'options' => array(
//         			'route'    => '/callback',
//         			'defaults' => array(
//         				'controller'    => 'callback',
//         				'action'        => 'index',
//         			),
//         		),
//         		'may_terminate' => true
//              ),
//         	'admin' => array(
// 				'type' => 'literal',
//         		'options'=> array(
//         			'route'	=> '/admin',
//         			'defaults' => array(
//         				'controller' => 'app',
//         				'action' => 'index'
//         			)
//         		),
//         		'may_terminate' => true,
//         		'child_routes' => array(
//         			'actionroutes' => array(
//         				'type' => 'segment',
//         				'options' => array(
//         					'route' => '[/:controller][/:action]',
//         					'constraints' => array(
//         						'controller' => '[a-z-]*',
//         						'action' => '[a-z-]*'
//         					),
//         					'defaults' => array(
//         						'controller' => 'app',
//         						'action' => 'index'
//         					)
//         				),
//         				'may_terminate' => true,
//         				'child_routes' => array(
//         					'wildcard' => array(
//         						'type' => 'wildcard'
//         					)
//         				)
//         			)
//         		)
// 			),
        	'admrs' => array(
        		'type'    => 'literal',
        		'options' => array(
        			'route'    => '/admrs'
        		),
        		'may_terminate' => true,
        		'child_routes' => array (
					'restroutes' => array (
						'type' => 'segment',
						'options' => array (
							'route' => '[/:controller].json[/:id]',
							'constraints' => array (
								'controller' => '[a-z-]*',
								'id' => '[A-Za-z0-9-_]*'
							)
						)
					)
				)
            )
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'error/404'					=> __DIR__ . '/../view/error/404.phtml',
            'error/index'				=> __DIR__ . '/../view/error/index.phtml',
        	'layout/error'				=> __DIR__ . '/../view/layout/error.phtml',
        	'layout/layout'				=> __DIR__ . '/../view/layout/layout.phtml',
        	'application/index/index'	=> __DIR__ . '/../view/index/index.phtml',
        	'application/callback/index'=> __DIR__ . '/../view/callback/index.phtml'
        ),
    	'strategies' => array(
    		'ViewJsonStrategy'
    	),
    ),
	'view_helpers' => array(
		'invokables' => array(
			'path' => 'Application\View\Helper\Path',
		)
	)
);