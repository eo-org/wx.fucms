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
    	if(!$doc->getAccessToken()){
    		$tokenFailed = true;
    	}else {
	    	$modified = $doc->getModified()->format('y-m-d H:i:s');
	 		$cTimestamp = strtotime (date("y-m-d H:i:s"));
	 		$timestamp = strtotime ($modified);
	 		if($cTimestamp - $timestamp > 7200){
	 			$tokenFailed = true;
	 		}
    	}
 		if($tokenFailed){ 			
 			$url = $wx['path']['accessToken'];
	    	$post_data = array (
	    		"component_appid" => $wx['appId'],
	    		"component_appsecret" =>$wx['appSecret'],
	    		'component_verify_ticket' => $doc->getTicket(),
	    	);
	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_URL, $url);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt($ch, CURLOPT_POST, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    	$output = curl_exec($ch);
	    	curl_close($ch);
	    	$doc->setData(array('aa' => $output));
	    	$currentDateTime = new \DateTime();
	    	$doc->setTokenModified($currentDateTime);
	    	$dm->persist($doc);
	    	$dm->flush();
 		}
 		
    	
    	return new JsonModel();
    }
}