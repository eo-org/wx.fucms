<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ApiController extends AbstractActionController
{
    public function indexAction()
    {
    	return new JsonModel(array('msg' => 'api action required'));
    }
    
    public function openIdAction()
    {
    	
    }
    
    public function userInfoAction()
    {
    	$appId = $this->params()->fromQuery('appId');
    	$code = $this->params()->fromQuery('code');
    	 
    	$sm = $this->getServiceLocator();
    	 
    	$pa = $sm->get('Application\Service\PublicityAuth');
    	$componentAppId = $pa->getComponentAppId();
    	$componentAccessToken = $pa->getComponentAccessToken();
    	 
    	 
    	$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=".$appId."&code=".$code."&grant_type=authorization_code&component_appid=".$componentAppId."&component_access_token=".$componentAccessToken;
    	 
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	 
    	$accessTokenObj = json_decode($output);
		
    	$accessToken = $accessTokenObj->access_token;
    	$openid = $accessTokenObj->openid;
    	
    	$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken.'&openid='.$openid.'&lang=zh_CN';
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	curl_close($ch);
    		
    	$userInfo = json_decode($output, true);
    	
    	return new JsonModel($userInfo);
    }
    
    public function userBaseAction()
    {
    	$appId = $this->params()->fromQuery('appId');
    	$code = $this->params()->fromQuery('code');
    	
    	$sm = $this->getServiceLocator();
    	
    	$pa = $sm->get('Application\Service\PublicityAuth');
    	$componentAppId = $pa->getComponentAppId();
    	$componentAccessToken = $pa->getComponentAccessToken();
    	
    	
    	$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=".$appId."&code=".$code."&grant_type=authorization_code&component_appid=".$componentAppId."&component_access_token=".$componentAccessToken;
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	
    	$userObj = json_decode($output);
    	
//     	$pa = $sm->get('Application\Service\PublicityAuth');
//     	$authorizerAccessToken = $pa->getAuthorizerAccessToken($websiteId);
//     	$ch = curl_init();
//     	curl_setopt($ch, CURLOPT_URL, 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$authorizerAccessToken.'&type=jsapi');
//     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     	curl_setopt($ch, CURLOPT_HEADER, 0);
//     	$output = curl_exec($ch);
//     	curl_close($ch);
    	
//     	$ticketObj = json_decode($output);
//     	$ticket = $ticketObj->ticket;
//     	$currentDateTime = new \DateTime();
//     	$data = array(
//     		'jsApiTicket' => $ticket,
//     		'jsApiTicketModified' => $currentDateTime,
//     	);
//     	$authDoc->exchangeArray($data);
//     	$dm->persist($authDoc);
//     	$dm->flush();

    	
    	return new JsonModel(array(
    		'openId' => $userObj->openid,
    		//'scope' => $userObj->scope
    	));
    }
    
    public function componentAccessTokenAction()
    {
    	$pa = $this->getServiceLocator()->get('Application\Service\PublicityAuth');
    	
    	$accessToken = $pa->getComponentAccessToken();
    	
    	return new JsonModel(array('componentAccessToken' => $accessToken));
    }
    
    public function authorizerAccessTokenAction()
    {
    	$websiteId = $this->params()->fromRoute('websiteId');
    	
    	$pa = $this->getServiceLocator()->get('Application\Service\PublicityAuth');
    	$authorizerAccessToken = $pa->getAuthorizerAccessToken($websiteId);
    	
    	return new JsonModel(array('authorizerAccessToken' => $authorizerAccessToken));
    }
    
    
    public function jsApiTicketAction()
    {
    	$websiteId = $this->params()->fromRoute('websiteId');
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByWebsiteId($websiteId);
    	$regenerateJsApiTicket = $authDoc->jsApiTicketExpired();
    	
    	if($regenerateJsApiTicket){
    		$pa = $sm->get('Application\Service\PublicityAuth');
    		$authorizerAccessToken = $pa->getAuthorizerAccessToken($websiteId);
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$authorizerAccessToken.'&type=jsapi');
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_HEADER, 0);
    		$output = curl_exec($ch);
    		curl_close($ch);
    		
    		$ticketObj = json_decode($output);    		 
    		$ticket = $ticketObj->ticket;
    		$currentDateTime = new \DateTime();
    		$data = array(
    			'jsApiTicket' => $ticket,
    			'jsApiTicketModified' => $currentDateTime,
    		);
    		$authDoc->exchangeArray($data);
    		$dm->persist($authDoc);
    		$dm->flush();
    	}else {
    		$authData = $authDoc->getArrayCopy();
    		$ticket = $authData['jsApiTicket'];
    	}    	
    	return new JsonModel(array('jsApiTicket' => $ticket));
    	
    }
    
    public function getTestOpenIdAction()
    {
    	//针对全网发布所用的action，全网发布后，无用
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$content = 'QUERY_AUTH_CODE:$query_auth_code$';
    	$messageDoc = $dm->getRepository('Application\Document\Message')->findOneByContent($content);
    	$openId = $messageDoc->getOpenId();
    	return new JsonModel(array(
    		'openId' => $openId,
    		)
    	);
    }
    
    public function getAuthCodeAction()
    {
    	//针对全网发布所用的action，全网发布后，无用
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByWebsiteId('test');
    	$msg = $authDoc->getMsg();
    	
    	return new JsonModel($msg);
    }
    
}