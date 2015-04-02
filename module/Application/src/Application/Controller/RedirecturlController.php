<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class RedirecturlController extends AbstractActionController
{
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
// 	    	$post_data = array (
// 	    		"component_appid" => $wx['appId'],
// 	    		"component_appsecret" =>$wx['appSecret'],
// 	    		'component_verify_ticket' => $ticket,
// 	    	);
	    	$post_data = '{"component_appid":"'.$wx['appId'].'", "component_appsecret":"'.$wx['appSecret'].'", "component_verify_ticket":"'.$ticket.'"}';
// 	    	print($post_data);
	    	$tokenCurl = curl_init();
	    	curl_setopt($tokenCurl, CURLOPT_URL, $getTokenUrl);
	    	curl_setopt($tokenCurl, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt($tokenCurl, CURLOPT_POST, 1);
	    	curl_setopt($tokenCurl, CURLOPT_POSTFIELDS, $post_data);
	    	
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    	
	    	$output = curl_exec($tokenCurl);
	    	curl_close($tokenCurl);
	    	
	    	
	    	
	    	
	    	$doc->setData(array('aa' => $output,'bb' => $post_data));
	    	$currentDateTime = new \DateTime();
	    	$doc->setTokenModified($currentDateTime);
// 	    	$accessToken = $output('component_access_token');
// 	    	$doc->setAccessToken();
	    	$dm->persist($doc);
	    	$dm->flush();
 		}
 		
//  		$preAuthCodeCurl = curl_init();
//  		curl_setopt($preAuthCodeCurl, CURLOPT_URL, $url);
//  		curl_setopt($preAuthCodeCurl, CURLOPT_RETURNTRANSFER, 1);
//  		curl_setopt($preAuthCodeCurl, CURLOPT_POST, 1);
//  		curl_setopt($preAuthCodeCurl, CURLOPT_POSTFIELDS, $post_data);
//  		$output = curl_exec($preAuthCodeCurl);
//  		curl_close($preAuthCodeCurl);
    	
    	return new JsonModel(array('aa' => $output,'bb' => $post_data));
    }
}