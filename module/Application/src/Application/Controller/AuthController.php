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
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$q = $this->params()->fromQuery();
    	$authCode = $q['auth_code'];
    	
    	$post_data = array(
    		'component_appid' => $wx['appId'],
    		'authorization_code' => $authCode,
    	);
    	
    	$tokenDoc = $dm->createQueryBuilder('Application\Document\Ticket')
			    		->field('label')->equals('token')
			    		->getQuery()
			    		->getSingleResult();
    	$tokenFailed = true;
    	if(!empty($tokenDoc)) {
    		    		
    		$modified = $tokenDoc->getModified()->format('y-m-d H:i:s');
    		$cTimestamp = strtotime (date("y-m-d H:i:s"));
    		$timestamp = strtotime ($modified);
    		if($cTimestamp - $timestamp < 6000){
    			$token = $tokenDoc->getValue();
    			$tokenFailed = false;
    		}
    	}else {
    		$tokenDoc = new Ticket();
    		$tokenDoc->setLabel('token');
    	}
    	if($tokenFailed) {
    		$ticketDoc = $dm->createQueryBuilder('Application\Document\Ticket')
				    		->field('label')->equals('ticket')
				    		->getQuery()
				    		->getSingleResult();
    		$ticket = $ticketDoc->getValue();
    		
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
    		$tokenDoc->setMsg(array(
    			'tokenResult' => $tokenResultStr,
    		));
    		
    		$token = $tokenResult['component_access_token'];
    		$tokenDoc->setValue($token);
    		$tokenDoc->setModified($currentDateTime);
    		$dm->persist($tokenDoc);
    		$dm->flush();
    	}
    	$post_data = json_encode($post_data);
    	$getAuthInfoUrl = $wx['path']['authInfo'].$token;

    	$authInfoResultStr = $this->curlPostResult($getAuthInfoUrl, $post_data);
    	$authInfoResult = json_decode($authInfoResultStr, true);
    	
    	$wxUserDoc = new WxUser();
    	$wxUserDoc->exchangeArray($authInfoResult['authorization_info']);
    	$currentDateTime = new \DateTime();
    	$wxUserDoc->setTokenModified($currentDateTime);
    	$wxUserDoc->setCreated($currentDateTime);
    	$dm->persist($wxUserDoc);
    	$dm->flush();
    	return $this->redirect()->toUrl('/#'.$authInfoResultStr);
    }
}