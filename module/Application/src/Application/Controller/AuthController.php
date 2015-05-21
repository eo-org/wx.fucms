<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\SiteInfo;
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
    	$pa = $sm->get('Application\Service\PublicityAuth');
    	$componentAccessToken = $pa->getComponentAccessToken();
    	
    	$post_data = json_encode($post_data);
    	$getAuthInfoUrl = $wx['path']['authInfo'].$componentAccessToken;

    	$authInfoResultStr = $this->curlPostResult($getAuthInfoUrl, $post_data);
    	$authInfoResult = json_decode($authInfoResultStr, true);
    	
    	$fucmsToken = $dm->createQueryBuilder('Application\Document\Token')	    					
				    		->field('websiteId')->equals($websiteId)
				    		->sort('created', -1)
				    		->getQuery()
				    		->getSingleResult();
    		
    	$redirecturi = $fucmsToken->getRedirecturi();
    	
    	$authDoc = $dm->getRepository('Application\Document\Auth')->findOneByWebsiteId($websiteId);
    	if(is_null($authDoc)){
    		$authDoc = new Auth();
    	}
    	$authInfoResult['authorization_info']['websiteId'] = $websiteId;
    	$authInfoResult['authorization_info']['msg'] = array('q'=>$q, 'authCode'=>$authCode);
    	$authDoc->exchangeArray($authInfoResult['authorization_info']);
    	$currentDateTime = new \DateTime();
    	$authDoc->setTokenModified($currentDateTime);
    	$authDoc->setCreated($currentDateTime);
    	$dm->persist($authDoc);
    	$dm->flush();
    	
    	SiteInfo::setWebsiteId($websiteId);
    	$componentAccessToken = $pa->getComponentAccessToken();
    	$getAuthorizerInfoUrl = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$componentAccessToken;
    	$postData = array(
    		'component_appid' => $wx['appId'],
    		'authorizer_appid' => $authInfoResult['authorization_info']['authorizer_appid']
    	);
    	$postStr = json_encode($postData);
    	$authorizerInfoResultStr = $this->curlPostResult($getAuthorizerInfoUrl, $postStr);
    	$authorizerInfoResult = json_decode($authorizerInfoResultStr, true);
    	
    	$cdm = $sm->get('CmsDocumentManager');
    	$settingDoc = $cdm->createQueryBuilder('WxDocument\Setting')->getQuery()->getSingleResult();
    	if(is_null($settingDoc)) {
    		$settingDoc = new Setting();
    	}
    	$settingDoc->exchangeArray($authorizerInfoResult);
    	
    	
    	$cdm->persist($settingDoc);
    	$cdm->flush();
    	return $this->redirect()->toUrl($redirecturi);
    }
}