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
}