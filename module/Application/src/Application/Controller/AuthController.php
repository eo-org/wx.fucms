<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Document\WxUser;

class AuthController extends AbstractActionController
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
    	$config = $this->getServiceLocator()->get('Config');
    	$wx = $config['env']['wx'];
    	
    	$q = $this->params()->fromQuery();
    	$authCode = $q['auth_code'];
    	
    	$post_data = array(
    		'component_appid' => $wx['appId'],
    		'authorization_code' => $authCode,
    	);
    	$post_data = json_encode($post_data);
    	$getAuthInfoUrl = $wx['path']['authInfo'];

    	$authInfoResultStr = $this->curlPostResult($getAuthInfoUrl, $post_data);
    	$authInfoResult = json_decode($authInfoResultStr, true);
    	
    	$wxUserDoc = new WxUser();
    	$wxUserDoc->exchangeArray($authInfoResult['authorization_info']);
    	$currentDateTime = new \DateTime();
    	$wxUserDoc->setTokenModified($currentDateTime);
    	$wxUserDoc->setCreated($currentDateTime);
    	$dm->persist($doc);
    	$dm->flush();
    	return $this->redirect()->toUrl('/');
    }
}