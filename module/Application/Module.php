<?php
namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;
use Zend\EventManager\StaticEventManager;

class Module
{

	public function init($moduleManager)
	{
		$eventManager = $moduleManager->getEventManager();
		$sharedEventManager = $eventManager->getSharedManager();
		
		$sharedEventManager->attach('Zend\Mvc\Application', 'dispatch.error', array(
			$this,
			'onError'
		), 100);
		
		// listen to Cms\ApplicationController.dispatch 1000 & Zend\Mvc\Application.finish -1000
		// $cacheListener = new \Cms\EventListener\CacheListener();
		// $sharedEvents->attach(array('Zend\Mvc\Application', 'Cms\ApplicationController'), $cacheListener, null);
		
		// listen to Cms\ApplicationController.dispatch 100
		// $twigListener = new \Cms\EventListener\TwigListener();
		// $sharedEvents->attach('Cms\ApplicationController', $twigListener, null);
		
		// $sharedEvents->attach('Zend\Mvc\Application', 'dispatch', array($this, 'setDesignLayout'), -100);
		
// 		$layoutListener = new \Cms\EventListener\DispatchListener();
// 		$sharedEventManager->attach('Cms', $layoutListener, null);
		
		//$twigListener = new \Cms\EventListener\TwigListener();
		//$sharedEventManager->attach('Cms', $twigListener, null);
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
					'Weixin' => __DIR__ . '/src/Weixin'
				)
			)
		);
	}

	public function onError(MvcEvent $e)
	{
		$target = $e->getTarget();
		if($target instanceof Application) {
			echo "handled in onError Event<br />";
			echo $e->getError();
			echo "<br />";
			die('END');
		} else {
			$target->layout('layout/error');
		}
	}
}