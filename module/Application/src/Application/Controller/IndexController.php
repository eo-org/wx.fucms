<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Redirecturi\Redirecturi;

use Application\Document\Token;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$q = $this->getRequest()->getQuery();
    	$timestamp = $q['timestamp'];
    	$signature = $q['signature'];
    	$websiteId = $q['id'];
    	$redirectUri = $q['redirectUri'];
    	
    	$md5Value = md5('weixin'.$redirectUri.'timestamp'.$timestamp.'websiteId'.$websiteId);
    	$cTimestamp = strtotime(date("y-m-d H:i:s"));
    	if(($cTimestamp - $timestamp < 600) &&  $md5Value == $signature){
    		$tokenDoc = new Token();
    		$tokenDoc->setWebsiteId($websiteId);
    		$tokenDoc->setRedirecturi($redirectUri);
    		
    		$currentDateTime = new \DateTime();
    		$tokenDoc->setCreated($currentDateTime);
    		
    		$dm->persist($tokenDoc);
    		$dm->flush();
    		
    	return array(
    		'websiteId' => $websiteId,
    	);
    	}else {
    		die('error');
    	}
    }
    
    public function loginAction()
    {
    	return array();
    }
}