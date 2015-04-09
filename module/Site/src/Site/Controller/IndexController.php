<?php
namespace Site\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	
    }
    
    public function getUserCodeAction()
    {
    	$code = $this->params()->fromQuery('code');
    	
    	
    	
    	
    	$pAuth = $this->getServiceLocator()->get('Application\Service\PublicityAuth');
    	$componentAccessToken = $pAuth->getComponentAccessToken();
    	
    	
    	$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=wx536a9272e58807e7&code=".$code."&grant_type=authorization_code&component_appid=wx2ce4babba45b702d&component_access_token=".$componentAccessToken;
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// //    	curl_setopt($ch, CURLOPT_POST, 1);
//     	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	
    	print_r($output);
    	
    }
}