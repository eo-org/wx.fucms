<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Session\User;
use Application\Document\Admin as Admin;

class CallbackController extends AbstractActionController
{
    public function indexAction()
    {
    	$postArr = $this->params()->fromPost();
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$doc = $dm->createQueryBuilder('Application\Document\Admin')
    					->field('appSecret')->equals('0c79e1fa963cd80cc0be99b20a18faeb')
    					->getQuery()
    					->getSingleResult();
    	if($doc){
    		if($postArr){
    			$doc->setAppId('demo');
    			$doc->setData($postArr);
    			if(isset($postArr['ComponentVerifyTicket'])){
    				$doc->seTticket($postArr['ComponentVerifyTicket']);
    			}
    		}    		
    	}else {
    		$doc = new Admin();
    		$data = array(
    			'appId'=>'wx2ce4babba45b702d',
    			'appSecret' => '0c79e1fa963cd80cc0be99b20a18faeb',
    			'data' => $postArr,
    		);
    		$doc->exchangeArray($data);
    	}    	
    	
    	$dm->persist($doc);
    	$dm->flush();
    	return new JsonModel(array('id' => $doc->getId()));
    }
    
    public function loginAction()
    {
    	
    }
}