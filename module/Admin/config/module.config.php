<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'index'					=> 'Admin\Controller\IndexController',
			/**
			 * rest controller *************************************
			 */
			/**
			 * new rest controller with subsite support
			 */
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'admin' => __DIR__ . '/../view'
		),
		'template_map' => array(
			'layout-admin/layout' => __DIR__ . '/../view/layout/layout.phtml',
			'layout-admin/ajax' => __DIR__ . '/../view/layout/layout.ajax.phtml',
			'layout-admin/json' => __DIR__ . '/../view/layout/layout.json.phtml',
			'layout-admin/iframe' => __DIR__ . '/../view/layout/layout.iframe.phtml',
			'layout/head' => __DIR__ . '/../view/layout/head.phtml',
			'layout/admin-toolbar' => __DIR__ . '/../view/layout/toolbar.phtml',
			'layout-admin/stabar' => __DIR__ . '/../view/layout/stabar.phtml',
			'admin/index/index'					=> __DIR__ . '/../view/admin/index/index.phtml',
			'dashboard-admin/article-pending' 	=> __DIR__ . '/../view/dashboard/article-pending.phtml'
		)
	),
	'router' => array(
		'routes' => array(
			'admin' => array(
				'type' => 'literal',
				'options' => array(
					'route' => '/admin',
					'defaults' => array(
						'controller' => 'index',
						'action' => 'index'
					)
				),
				'may_terminate' => true,
				'child_routes' => array(
					'actionroutes' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '[/:controller][.:format][/:action]',
							'constraints' => array(
								'controller' => '[a-z-]*',
								'format' => '(ajax|json|iframe|html)',
								'action' => '[a-z-]*'
							),
							'defaults' => array(
								'format' => 'html'
							)
						),
						'may_terminate' => true,
						'child_routes' => array(
							'wildcard' => array(
								'type' => 'wildcard'
							)
						)
					)
				)
			),
			'adminrest' => array(
				'type' => 'literal',
				'options' => array(
					'route' => '/adminrest'
				),
				'may_terminate' => true,
				'child_routes' => array(
					'adminrest-childroutes' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '[/:controller].[:format][/:id]',
							'constraints' => array(
								'controller' => '[a-z-]*',
								'format' => '(json|html)',
								'id' => '[a-z0-9]*'
							)
						)
					)
				)
			),
			'admrs' => array (
				'type' => 'literal',
				'options' => array (
					'route' => '/admrs',
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
			),
		)
	),
	'view_helpers' => array(
		'invokables' => array(
			'actionTitle' => 'Admin\View\Helper\ActionTitle',
			'actionButton' => 'Admin\View\Helper\ActionButton'
		)
	),
	'service_manager' => array (
		'invokables' => array (
			'Admin\Service\SubsiteService' => 'Admin\Service\SubsiteService',
		)
	),
	'dashboard_widget' => array(
		'Admin_Dashboard_ArticlePending' => array(
			'label' => '寰呭鏍告枃绔�',
			'class' => 'Admin_Dashboard_ArticlePending'
		)
	),
	'navi_resource_provider' => array(
		'article' => array(
			'storageKey' => 'list.article',
			'resourceUrl' => array('controller' => 'ar-group', 'id' => 'article'),
			'frontendRouter' => 'application/list',
			'frontendUrl' => array('id' => '@replace', 'page' => 1)
		),
		'product' => array(
			'storageKey' => 'list.product',
			'resourceUrl' => array('controller' => 'ar-group', 'id' => 'product'),
			'frontendRouter' => 'application/product-list',
			'frontendUrl' => array('id' => '@replace', 'page' => 1)
		),
	),
	'rbac' => array(
		'permissions' => array(
			'navi' => array(
				'label' => '鐩綍瀵艰埅',
			),
			'article' => array(
				'label' => '鏂囩珷绠＄悊',
				'actions' => array(
					'status' => '鏂囩珷瀹℃牳'
				)
			),
			'admin' => array(
				'label' => '绠＄悊鍛�'
			),
			'system' => array(
				'label' => '绯荤粺绠＄悊',
				'parent' => '#'
			),
			'file' => array(
				'label' => '鏂囦欢绠＄悊',
				'parent' => '#'
			),
		),
		'navi' => array(
			'video' => array(
				'title' => '瑙嗛鏂囦欢绠＄悊',
				'url' => '/admin/videoadmin-index.html'
			)
		)
	)
);