<?php
namespace Site;

use Zend\Mvc\MvcEvent;

class Module
{
	public function init($moduleManager)
	{
		$eventManager = $moduleManager->getEventManager();
		$sharedEventManager = $eventManager->getSharedManager();
		
		$sharedEventManager->attach('Zend\Mvc\Application', 'dispatch', array($this, 'userAuth'), 100);
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
					__NAMESPACE__	=> __DIR__ . '/src/' . __NAMESPACE__
				)
            ),
        );
    }
    
    public function userAuth(MvcEvent $e)
    {
    	$rm = $e->getRouteMatch();
    	$matchedRouteName = $rm->getMatchedRouteName();
    	
    	if($matchedRouteName == 'site') {
    		header("Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx2ce4babba45b702d&redirect_uri=http%3a%2f%2fwxs.fucmsweb.com%2fget-user-code&response_type=code&scope=snsapi_base&state=gavin&connect_redirect=1#wechat_redirect");
    		exit(0);
    	}
    }
}