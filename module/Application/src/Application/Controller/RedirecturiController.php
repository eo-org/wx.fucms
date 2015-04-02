<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

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
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	
    	$config = $this->getServiceLocator()->get('Config');
    	$wx = $config['env']['wx'];
    	$tokenFailed = false;
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
 		$preAuthCodePostData = array(
 			'component_appid' => $wx['appId'],
 		);
 		$preAuthCodePostData = json_encode($preAuthCodePostData);
 		$getPreAuthCodeUrl = $wx['path']['preAuthCode'].$accessToken;
 		
 		$preAuthCodeResult = $this->curlPostResult($getPreAuthCodeUrl, $preAuthCodePostData);
 		$preAuthCodeResult = json_decode($preAuthCodeResult , true);
 		
 		$result = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$wx['appId'].'&pre_auth_code='.$preAuthCodeResult['pre_auth_code'].'&redirect_uri='.$wx['path']['redirectUri'];
//  		$result = $tokenResult;
    	return new JsonModel(array('redirectUri' => $result));
    }
}