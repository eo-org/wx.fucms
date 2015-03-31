<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'Cms\ApplicationController'				=> 'Cms\ApplicationController',
			'Cms\Controller\IndexController'		=> 'Cms\Controller\IndexController',
			'Cms\Controller\BookController'			=> 'Cms\Controller\BookController',
			'Cms\Controller\ArticleController'		=> 'Cms\Controller\ArticleController',
			'Cms\Controller\ProductController'		=> 'Cms\Controller\ProductController',
			'Cms\Controller\SearchController'		=> 'Cms\Controller\SearchController',
			'Cms\Controller\SitemapController'		=> 'Cms\Controller\SitemapController'
// 			'Application\Controller\Error' => 'Application\Controller\ErrorController'
		)
	),
	'router' => array(
		'routes' => array(
			'rest' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/rest'
				),
				'may_terminate' => true,
				'child_routes' => array(
					'rest-childroutes' => array(
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
			'application' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/',
					'defaults' => array(
						'controller' => 'Cms\Controller\IndexController',
						'provide-cache' => 'database'
					)
				),
				'may_terminate' => true,
				'child_routes' => array(
					'article' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => 'article[-:id].shtml',
							'defaults' => array(
								'controller' => 'Cms\Controller\ArticleController',
								'action' => 'index'
							),
							'constraints' => array(
								'id' => '[a-z0-9]*'
							)
						)
					),
					'list' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => 'list-[:id]/page[:page].shtml',
							'defaults' => array(
								'controller' => 'Cms\Controller\ArticleController',
								'action' => 'list'
							),
							'constraints' => array(
								'id' => '[a-z0-9]*',
								'page' => '[0-9]*'
							)
						)
					),
					'product' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => 'product-[:id].shtml',
							'defaults' => array(
								'controller' => 'Cms\Controller\ProductController',
								'action' => 'index'
							),
							'constraints' => array(
								'id' => '[a-z0-9]*'
							)
						)
					),
					'product-list' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => 'product-list-[:id]/page[:page].shtml',
							'defaults' => array(
								'controller' => 'Cms\Controller\ProductController',
								'action' => 'list'
							),
							'constraints' => array(
								'id' => '[a-z0-9]*',
								'page' => '[0-9]*'
							)
						)
					),
					'search' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => 'search.shtml',
							'defaults' => array(
								'controller'	=> 'Cms\Controller\SearchController',
								'action'	=> 'index'
							),
						)
					),
					'frontpage' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '[:id].htm',
							'constraints' => array(
								'id' => '[a-z0-9-]*'
							)
						)
					),
					'sitemap' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => 'sitemap[-:id].xml',
							'constraints' => array(
								'id' => '[0-9]*'
							),
							'defaults' => array(
								'controller' => 'Cms\Controller\SitemapController',
								'action' => 'index',
								'id' => 'default'
							)
						)
					)
// 					'layout' => array(
// 						'type' => 'Segment',
// 						'options' => array(
// 							'route' => '[:id].layout',
// 							'constraints' => array(
// 								'id' => '[a-z0-9]*'
// 							)
// 						)
// 					)
				)
			),
			'error' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/error-[:id].shtml',
					'defaults' => array(
						'controller' => 'Cms\ApplicationController',
						'action' => 'index'
					),
					'constraints' => array(
						'id' => '(401|404)'
					)
				),
				'may_terminate' => true
			)
		)
	),
	'default_layout_type' => array(
		'index' => array(
			'label' => '首页'
		),
		'book' => array(
			'label' => '手册详情'
		),
		'article' => array(
			'label' => '文章详情'
		),
		'list' => array(
			'label' => '文章列表'
		),
		'product' => array(
			'label' => '产品详情'
		),
		'product-list' => array(
			'label' => '产品列表'
		),
		'search' => array(
			'label' => '搜索结果'
		)
	),
	'controller_plugins' => array(
		'invokables' => array(
			//commented on 2015-3-1
// 			'dbFactory' => 'Core\Controller\Plugin\DbFactory',
			'documentManager' => 'Core\Controller\Plugin\DocumentManager',
// 			'switchContext' => 'Core\Controller\Plugin\SwitchContext',
			'formatData' => 'Core\Controller\Plugin\FormatData'
		)
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array(
			'layout/console' => __DIR__ . '/../view/layout/console.phtml',
			'layout/error' => __DIR__ . '/../view/error/error.phtml',
			'error/404' => __DIR__ . '/../view/error/404.phtml',
			'error/index' => __DIR__ . '/../view/error/index.phtml',
			'cms/sitemap/index'		=> __DIR__ . '/../view/sitemap/index.phtml',
			'cms/phtml/file-upload'	=> __DIR__ . '/../view/layout/phtml/file-upload.phtml'
		),
		'strategies' => array(
			'Twig\ViewStrategy',
			'ViewJsonStrategy',
		)
	),
// 	'view_helpers' => array(
// 		'invokables' => array(
// 			'singleForm' => 'Core\View\Helper\SingleForm',
// 			'brickConfigForm' => 'Core\View\Helper\BrickConfigForm',
// 			'tabForm' => 'Admin\View\Helper\TabForm',
// 			'bootstrapRow' => 'Admin\View\Helper\BootstrapRow',
// 			'bootstrapCollection' => 'Admin\View\Helper\BootstrapCollection',
// 			'outputImage' => 'Core\View\Helper\OutputImage',
// 			'selectOptions' => 'Core\View\Helper\SelectOptions',
			
// 			'usage' => 'Cms\View\Helper\Usage',
// 			'path' => 'Cms\View\Helper\Path',
// 			'minlibVer' => 'Cms\View\Helper\MinlibVer',
// 			'siteConfig' => 'Cms\View\Helper\SiteConfig',
			
// 			'hookView' => 'Cms\View\Helper\HookView'
// 		)
// 	),
// 	'service_manager' => array(
// 		'invokables' => array(
// 			//'Cms\Db\Factory' => 'Cms\Db\Factory',
// 			'Cms\Layout\Front' => 'Cms\Layout\Front',
// 			'Cms\Layout\Manager' => 'Cms\Layout\Manager',
// 			'Cms\NestedSet\Manager' => 'Cms\NestedSet\Manager',
			
// 			'Cms\Service\DocumentEvents'	=> 'Cms\Service\DocumentEvents',
// 			'Cms\Service\ResourceEvents'	=> 'Cms\Service\ResourceEvents',
// 			'Cms\Service\FrontEvents'		=> 'Cms\Service\FrontEvents',
// 			'Cms\Service\OptsEvents'		=> 'Cms\Service\OptsEvents',
			
// 		),
// 		'factories' => array(
// 			'DocumentManager' => 'Cms\Db\Service\DocumentManagerFactory',
// 			'ConfigObject\EnvironmentConfig' => function ($serviceManager) {
// 				$config = $serviceManager->get('Config');
// 				$env = $config['env'];
// 				$siteConfig = new \Cms\SiteConfig($env);
// 				$dm = $serviceManager->get('DocumentManager');
// 				$nDoc = $dm->getRepository('Cms\Document\N')->findOneByName('cdnStamp');
// 				if(is_null($nDoc)) {
// 					$cdnStamp = '1';
// 				} else {
// 					$cdnStamp = $nDoc->getValue();
// 				}
// 				$siteConfig->setCdnStamp($cdnStamp);
// 				return $siteConfig;
// 			},
// 			//'Cms\Db\Adapter'		=> 'Cms\Db\AdapterServiceFactory',
// 			'Twig\Environment' 		=> 'Cms\Twig\Service\EnvironmentFactory',
// 			'Twig\ViewStrategy'		=> 'Cms\Twig\Service\StrategyFactory',
// 			'Twig\ViewRenderer'		=> 'Cms\Twig\Service\RendererFactory',
			
// 			'Cms\Setting'			=> 'Cms\Service\SettingFactory',
// 		)
// 	),
// 	'twig' => array(
// 		'tpl_map_path' => array(
// 			'layout/head-client' => __DIR__ . '/../view/layout/head-client.tpl',
// 			'layout/head-admin' => __DIR__ . '/../view/layout/head-admin.tpl',
// 			'layout/toolbar' => __DIR__ . '/../view/layout/toolbar.tpl',
// 			'layout/toolbar-tail' => __DIR__ . '/../view/layout/toolbar-tail.tpl',
// 			'layout/bg-wrapper' => __DIR__ . '/../view/layout/bg-wrapper.tpl',
// 			'layout/body-head' => __DIR__ . '/../view/layout/body-head.tpl',
// 			'layout/body-main' => __DIR__ . '/../view/layout/body-main-frame.tpl',
// 			'layout/body-tail' => __DIR__ . '/../view/layout/body-tail.tpl',
// 			'layout/region' => __DIR__ . '/../view/layout/region.tpl',
// 			'layout/section' => __DIR__ . '/../view/layout/section.tpl',
// 			'layout/macro' => __DIR__ . '/../view/layout/macro.tpl',
// 			'@map_ext' => __DIR__ . '/../view/ext/brick.tpl',
// 			'@map_cms_layout' => __DIR__ . '/../view/layout/layout.tpl',
// 			'@map_cms_layout_desktop.slim'		=> __DIR__ . '/../view/layout/layout-desktop.slim.tpl',
// 			'@map_cms_layout_mobile.foundation'	=> __DIR__ . '/../view/layout/layout-mobile.foundation.tpl',
// 		),
// 		'filters' => array(
// 			'outputImage',
// 			'graphicDataJson',
// 			'substr',
// 			'url',
// 			'contentUrl',
// 			'pageLink',
// 			'translate',
// 			'query',
// 			'imgtag'
// 		),
// 		'functions' => array(
// 			'headMeta' => function ()
// 			{
// 				return "";
// 			},
// 			'headTitle' => function ()
// 			{
// 				return "";
// 			},
// 			'pageMeta' => function ($name, $content = "")
// 			{
// 				if(is_string($name)) {
// 					return "<meta name=\"$name\" content=\"$content\" />";
// 				} elseif(is_array($name)) {
// 					$metaStr = "";
// 					foreach($name as $meta) {
// 						$metaStr.= '<meta name="'.$meta["name"].'" content="'.$meta["content"].'" />';				
// 					}
// 					return $metaStr;
// 				}
				
// 			},
// 			'pageTitle' => function ($title)
// 			{
// 				return "<title>$title</title>";
// 			},
// 			'pageHeadLink' => function ($headlinks)
// 			{
// 				$linkHTML = "";
// 				if(is_array($headlinks)) {
// 					foreach($headlinks as $link) {
// 						$linkHTML .= "<link href='$link' media='screen' rel='stylesheet' type='text/css'>";
// 					}
// 				}
// 				return $linkHTML;
// 			},
// 			'pageHeadScript' => function ($headscripts)
// 			{
// 				$scriptHTML = "";
// 				if(is_array($headscripts)) {
// 					foreach($headscripts as $script) {
// 						$scriptHTML .= "<script type='text/javascript' src='$script'></script>";
// 					}
// 				}
// 				return $scriptHTML;
// 			},
// 			'getArrayValue' => function ($arr, $key, $default = null)
// 			{
// 				if(isset($arr[$key])) {
// 					return $arr[$key];
// 				}
// 				return $default;
// 			}
// 		)
// 	),
	'sitemapProvider' => array('Cms\Sitemap\Provider\Article', 'Cms\Sitemap\Provider\IdxPage')
);
