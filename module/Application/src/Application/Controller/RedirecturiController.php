<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Document\Ticket;

class RedirecturiController extends AbstractActionController
{
	protected function curlPostResult($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		curl_close($ch);
		 
		return $output;
	}
	
    public function indexAction()
    {
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$config = $this->getServiceLocator()->get('Config');
    	$wx = $config['env']['wx'];
    	$pa = $sm->get('Application\Service\PublicityAuth');
    	$accessToken = $pa->getComponentAccessToken();
 		$preAuthCodePostData = array(
 			'component_appid' => $wx['appId'],
 		);
 		$preAuthCodePostDataStr = json_encode($preAuthCodePostData);
 		$getPreAuthCodeUrl = $wx['path']['preAuthCode'].$accessToken;
 		
 		$preAuthCodeResultStr = $this->curlPostResult($getPreAuthCodeUrl, $preAuthCodePostDataStr);
 		$preAuthCodeResult = json_decode($preAuthCodeResultStr , true);
 		
 		$result = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$wx['appId'].'&pre_auth_code='.$preAuthCodeResult['pre_auth_code'].'&redirect_uri='.$wx['path']['redirectUri'];
 		
    	return new JsonModel(array('redirectUri' => $result));
    }
}