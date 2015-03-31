<?php
namespace Admin;

use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Core\Session\SsoAuth;
use Cms\Session\Admin as SessionAdmin;
use Ext\Register;
use Admin\Ext\RegisterConfig;
use Admin\Service\NaviService;
use Cms\SiteConfig;
use Admin\EventListener\NaviListener;
use Admin\EventListener\DesignListener;
/**
 *
 * @author Gavin
 * @todo config different backend here ??
 */
class Module
{
	public function init($moduleManager)
	{
		
// 		$eventManager = $moduleManager->getEventManager();
// 		$sharedEventManager = $eventManager->getSharedManager();
		
// 		$naviListener = new NaviListener(array(
// 			'article' => array(
// 				'title' => '新闻管理',
// 				'children' => array(
// 					'article.create'	=> array(
// 						'title'	=> '发布新闻',
// 						'router' => 'admin/actionroutes',
// 						'url' => array('controller' => 'article', 'action' => 'edit'),
// 					),
// 					'article.index'	=> array(
// 						'title'	=> '查询全部新闻',
// 						'router' => 'admin/actionroutes',
// 						'url' => array('controller' => 'article', 'action' => 'index'),
// 					),
// 					'article.group'	=> array(
// 						'title'	=> '管理新闻分组',
// 						'router' => 'admin/actionroutes/wildcard',
// 						'url' => array('controller' => 'group', 'action' => 'edit', 'type'	=> 'article'),
// 					),
// 					'article.trash'	=> array(
// 						'title'	=> '回收站',
// 						'router' => 'admin/actionroutes',
// 						'url' => array('controller' => 'article', 'action' => 'trash'),
// 					),
// 				),
// 				'subsiteReady' => true
// 			),
// 			'product' => array(
// 				'title' => '产品管理',
// 				'children'	=> array(
// 					'product.create'	=> array(
// 						'title'	=> '创建产品',
// 						'router' => 'admin/actionroutes',
// 						'url' => array('controller' => 'product', 'action' => 'create'),
// 					),
// 					'product.index'	=> array(
// 						'title'	=> '产品列表',
// 						'router' => 'admin/actionroutes',
// 						'url' => array('controller' => 'product', 'action' => 'index'),
// 					),
// 					'product.group'	=> array(
// 						'title'	=> '产品分组',
// 						'router' => 'admin/actionroutes/wildcard',
// 						'url' => array('controller' => 'group', 'action' => 'edit', 'type'	=> 'product'),
// 					),
// 					'product.type'	=> array(
// 						'title'	=> '产品类型',
// 						'router' => 'admin/actionroutes/wildcard',
// 						'url' => array('controller' => 'type', 'action' => 'index', 'group'	=> 'product'),
// 					),
// 				),
// 			),
// 			'navi' => array(
// 				'title' => '目录导航',
// 				'router' => 'admin/actionroutes',
// 				'url' => array('controller' => 'navi', 'action' => 'index'),
// 				'subsiteReady' => true
// 			),
// 		));
// 		$sharedEventManager->attach('Admin\Service\NaviService', $naviListener, null, 100);
// 		$sharedEventManager->attach('Design', 'dispatch', array($this, 'attachDesignEvents'), 100);
// 		$sharedEventManager->attach('Zend\Mvc\Application', 'dispatch', array($this, 'setLayout'), -100);
// 		$sharedEventManager->attach('Zend\Mvc\Application', 'dispatch', array($this, 'validatePermission'), 100);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					'Admin' => __DIR__ . '/src/Admin',
					'Rest' => __DIR__ . '/src/Rest'
				)
			)
		);
	}
	
	public function validatePermission(MvcEvent $e)
	{
		$rm = $e->getRouteMatch();
		$matchedRouteName = $rm->getMatchedRouteName();
		$matchedRouteNameParts = explode('/', $matchedRouteName);
		if($matchedRouteNameParts[0] == 'admin') {
			$rm = $e->getRouteMatch();
			$controllerName = $rm->getParam('controller');
			
			$sm = $e->getApplication()->getServiceManager();
			$adminUser = $sm->get('Sp\User');
			
			if(!$adminUser->hasPrivilege($controllerName)) {
				throw new \Cms\Exception\NotAllowedException($controllerName);
			}
		}
	}
	
	public function setLayout(MvcEvent $e)
	{
		$rm = $e->getRouteMatch();
		$matchedRouteName = $rm->getMatchedRouteName();
		$matchedRouteNameParts = explode('/', $matchedRouteName);
		if($matchedRouteNameParts[0] == 'admin') {
			$disableLayout = false;
			$controller = $e->getTarget();
			$format = $controller->params()->fromRoute('format');
			if($format == 'ajax') {
				$controller->layout('layout-admin/ajax');
			} else if($format == 'json') {
				$controller->layout('layout-admin/json');
				$disableLayout = true;
			} else if($format == 'iframe') {
				$controller->layout('layout-admin/iframe');
			} else {
				$controller->layout('layout-admin/layout');
			}
			
			if(! $disableLayout) {
				$routeMatch = $e->getRouteMatch();
				$sm = $e->getApplication()->getServiceManager();
				$user = $sm->get('Sp\User');
				
				$brickRegister = $sm->get('Ext\Register');
				$brickRegister->setController($controller);
				
				//$config = $sm->get('Config');
				$permissions = $user->getUserData('permissions');
				
				$naviService = new NaviService($sm, $e);
				//$naviService->injectPreEvent();
				$naviService->injectEvent();
				
				$naviHeader = $naviService->getNaviHeader();
				$navi = $naviService->getNavi();
				
				$dm = $sm->get('DocumentManager');
				//$siteSettings = $dm->getRepository('Cms\Document\Setting')->findAll()->getNext();
				$setting = $sm->get('Cms\Setting');
				//if($siteSettings){
				$webSiteName = $setting->getWebSiteName();
				//}else {
				//	$webSiteName = '<a href="/admin/site">设置网站信息</a>';
				//}
				$viewModel = $e->getViewModel();
				$viewModel->setVariables(array(
					'webSiteName' => $webSiteName,
					'loginName' => $user->getUserData('loginName'),
					'loginId' => $user->getUserData('id'),
					'naviHeader' => $naviHeader,
					'navi' => $navi,
					'remoteSiteId' => SiteConfig::getWebsiteId(),
				));
			}
		}
	}

	public function attachDesignEvents(MvcEvent $e)
	{
		$sm = $e->getApplication()->getServiceManager();
	
		$designListener = new DesignListener();
		$designListener->setDocumentManager($sm->get('DocumentManager'));
		$designEvents = $sm->get('Design\Service\Events');
		$designEvents->getEventManager()->attach($designListener);
	}
	
	protected function guardNavi($navi, $permissions)
	{
		
	}

	protected function guardChild(&$childNavi, $permissions)
	{
		foreach($childNavi as $key => $c) {
			if(in_array($key, $permissions)) {
				continue;
			}
			if(isset($c['children'])) {
				$this->guardChild($c['children'], $permissions);
				if(count($c['children']) == 0) {
					unset($childNavi[$key]);
				}
			} else {
				unset($childNavi[$key]);
			}
		}
	}

}