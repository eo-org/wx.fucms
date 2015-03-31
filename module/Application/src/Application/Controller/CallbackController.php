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
//     	$postArr = $this->params()->fromPost();
    	$dm = $this->getServiceLocator()->get('DocumentManager');
    	$doc = new Admin();
    	$data = array(
    		'appId'=>'wx2ce4babba45b702d',
    		'appSecret' => '0c79e1fa963cd80cc0be99b20a18faeb',
    	);
    	$doc->exchangeArray($data);
    	$dm->persist($doc);
    	$dm->flush();
    	return new JsonModel(array('id' => $doc->getId()));
    }
    
    public function loginAction()
    {
    	
    }
}