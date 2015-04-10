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
    
    public function componentAccessToken()
    {
    	$pa = $this->getServiceLocator()->get('PublicityAuth');
    	
    	$accessToken = $pa->getComponentAccessToken();
    	
    	return new JsonModel(array('componentAccessToken' => $accessToken));
    }
}