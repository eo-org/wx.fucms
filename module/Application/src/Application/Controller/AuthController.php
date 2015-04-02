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
    	$dm = $this->getServiceLocator()->documentManager();
    	$q = $this->params()->fromQuery();
    	$authCode = $q['auth_code'];
    	
    	$post_data = array(
    		'component_appid' => $wx['appId'],
    		'authorization_code' => $authCode,
    	);
    	
    	$doc = $dm->createQueryBuilder('Application\Document\Admin')
			    	->field('appSecret')->equals($wx['appSecret'])
			    	->getQuery()
			    	->getSingleResult();
    	$ticket = $doc->getTicket();
    	if(!$doc->getAccessToken()){
    		$tokenFailed = true;
    	}else {
    		$modified = $doc->getModified()->format('y-m-d H:i:s');
    		$cTimestamp = strtotime (date("y-m-d H:i:s"));
    		$timestamp = strtotime ($modified);
    		if($cTimestamp - $timestamp > 7200){
    			$tokenFailed = true;
    		}else {
    			$accessToken = $doc->getAccessToken();
    		}
    	}
    	if($tokenFailed) {
    		$getTokenUrl = $wx['path']['accessToken'];
    		$post_data = array (
    			"component_appid" => $wx['appId'],
    			"component_appsecret" =>$wx['appSecret'],
    			'component_verify_ticket' => $ticket,
    		);
    		$post_data = json_encode($post_data);
    		$tokenResultStr = $this->curlPostResult($getTokenUrl, $post_data);
    	
    		$tokenResult = json_decode($tokenResultStr , true);
    	
    		$currentDateTime = new \DateTime();
    		$doc->setTokenModified($currentDateTime);
    		$doc->setData(array(
    			'tokenResult'=> $tokenResultStr,
    		));
    		$accessToken = $tokenResult['component_access_token'];
    		$doc->setAccessToken($accessToken);
    		$dm->persist($doc);
    		$dm->flush();
    	}
    	$post_data = json_encode($post_data);
    	$getAuthInfoUrl = $wx['path']['authInfo'].$accessToken;

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