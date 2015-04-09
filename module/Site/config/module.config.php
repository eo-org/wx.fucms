<?php
return array(
	'controllers' => array(
        'invokables' => array(
        	'Site\Controller\IndexController' => 'Site\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'site' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/test',
                    'defaults' => array(
                        'controller'    => 'Site\Controller\IndexController',
                        'action'        => 'index',
                    ),
                ),
            	'may_terminate' => true,
            ),
        	'get-user-code' => array(
        		'type'		=> 'literal',
	        	'options'	=> array(
	            	'route' 	=> '/get-user-code',
	        		'defaults'	=> array(
	        			'controller'    => 'Site\Controller\IndexController',
	        			'action'        => 'get-user-code',
	        		)
	            )
        	)
        ),
    ),
	'view_manager' => array(
		'template_map' => array(
			'site/index/index'	=> __DIR__ . '/../view/index/index.phtml',
		)
	),
);