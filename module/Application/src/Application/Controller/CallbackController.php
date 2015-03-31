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
    	$q = $this->params()->fromQuery();
    	$postArr = file_get_contents('php://input');
    	
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$doc = $dm->createQueryBuilder('Application\Document\Admin')
    					->field('appSecret')->equals('0c79e1fa963cd80cc0be99b20a18faeb')
    					->getQuery()
    					->getSingleResult();
    	if($doc){
    		$doc->setAppId('demo1111');
    		if($postArr){
    			$doc->setData($postArr);
   			}else {
   				$doc->setData(array('q'=>$q));
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
    	$currentDateTime = new \DateTime();
    	$doc->setModified($currentDateTime);
    	$dm->persist($doc);
    	$dm->flush();
    	return new ConsoleModel();
    }
    
    public function loginAction()
    {
    	
    }
}