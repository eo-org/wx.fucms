<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Document\Auth;
use WxDocument\Setting;

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
    	$websiteId = $this->params()->fromRoute('websiteId');
    	SiteInfo::setWebsiteId($websiteId);
    	$sm = $this->getServiceLocator();
    	$config = $sm->get('Config');
    	$wx = $config['env']['wx'];
    	$dm = $sm->get('DocumentManager');
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
    	
    	$fucmsToken = $dm->createQueryBuilder('Application\Document\Token')	    					
				    		->field('websiteId')->equals($websiteId)
				    		->sort('created', -1)
				    		->getQuery()
				    		->getSingleResult();
    		
    	$redirecturi = $fucmsToken->getRedirecturi();
    	
    	$authDoc = new Auth();
    	$authInfoResult['authorization_info']['websiteId'] = $websiteId;
    	$authInfoResult['authorization_info']['msg'] = array('q'=>$q, 'authCode'=>$authCode);
    	$authDoc->exchangeArray($authInfoResult['authorization_info']);
    	$currentDateTime = new \DateTime();
    	$authDoc->setTokenModified($currentDateTime);
    	$authDoc->setCreated($currentDateTime);
    	$dm->persist($authDoc);
    	$dm->flush();
    	$pa = $sm->get('Application\Service\PublicityAuth');
    	$componentAccessToken = $pa->getComponentAccessToken();
    	
    	$getAuthorizerInfoUrl = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$componentAccessToken;
    	$postData = array(
    		'component_appid' => $wx['appId'],
    		'authorizer_appid' => $authInfoResult['authorization_info']['authorizer_appid']
    	);
    	$postStr = json_encode($postData);
    	$authorizerInfoResultStr = $this->curlPostResult($getAuthorizerInfoUrl, $postStr);
    	$authorizerInfoResult = json_decode($authorizerInfoResultStr, true);
    	
    	$settingDoc = new Setting();
    	$settingDoc->exchangeArray($authorizerInfoResult);
    	$cdm = $sm->get('CmsDocumentManager');
    	
    	$cdm->persist($settingDoc);
    	$cdm->flush();
    	return $this->redirect()->toUrl($redirecturi);
    }
}