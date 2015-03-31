<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use Application\Session\User;
use Application\Document\Admin as Admin;

class CallbackController extends AbstractActionController
{
    public function indexAction()
    {
    	
//     	$q = $this->params()->fromQuery();
    	
//     	print_r($q);
    	
    	
    	$postArr = $this->params()->fromPost();
//     	if($this->getRequest()->isPost()) {
//     		$postData = $this->getRequest()->getPost();
//     		print_r($postData);
    		
//     		print_r($postArr);
    		
//     		die();
//     	}
//     	print_r($postArr);
//     	die();
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$doc = $dm->createQueryBuilder('Application\Document\Admin')
    					->field('appSecret')->equals('0c79e1fa963cd80cc0be99b20a18faeb')
    					->getQuery()
    					->getSingleResult();
    	if($doc){
//     		if($postArr){
    			$doc->setAppId('demo');
    			$doc->setData($postArr);
    			if(isset($postArr['ComponentVerifyTicket'])){
    				$doc->seTticket($postArr['ComponentVerifyTicket']);
    			}
//     		}    		
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
    	return new ConsoleModel();
    }
    
    public function loginAction()
    {
    	
    }
}