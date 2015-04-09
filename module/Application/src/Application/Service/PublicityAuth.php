<?php
namespace Application\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Application\Document\Ticket;

class PublicityAuth implements ServiceLocatorAwareInterface
{
	protected $sm;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}
	
	public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function getComponentAccessToken()
	{
		$dm = $this->sm->get('DocumentManager');
		$config = $this->sm->get('Config');
		$wx = $config['env']['wx'];
		
		$tokenDoc = $dm->getRepository('Application\Document\Ticket')->findOneByLabel('componentAccessToken');
		
		$regenerateToken = false;
		
		if(is_null($tokenDoc)) {
			$tokenDoc = new Ticket();
			$tokenDoc->setLabel('componentAccessToken');
			$regenerateToken = true;
		} else {
			$modified = $tokenDoc->getModified()->format('y-m-d H:i:s');
			$currentTimestamp = time();
			$timestamp = strtotime ($modified);
			if($currentTimestamp - $timestamp > 6000) {
				$regenerateToken = true;
			}
		}
		
		if($regenerateToken) {
			$ticketDoc = $dm->getRepository('Application\Document\Ticket')->findOneByLabel('ticket');
			
			$ticket = $ticketDoc->getValue();
			$getTokenUrl = $wx['path']['accessToken'];
			$postData = array (
				"component_appid" => $wx['appId'],
				"component_appsecret" =>$wx['appSecret'],
				'component_verify_ticket' => $ticket,
			);
			$postData = json_encode($postData);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $getTokenUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			$output = curl_exec($ch);
			curl_close($ch);
			
			$tokenResultStr = $output;
			
			
			
			$tokenResultArr = json_decode($tokenResultStr , true);
			
			
			$currentDateTime = new \DateTime();
			
			$tokenDoc->setMsg(array(
				'tokenResult' => $tokenResultArr,
			));
			
			$tokenValue = $tokenResultArr['component_access_token'];
			$tokenDoc->setValue($tokenValue);
			
			$tokenDoc->setModified($currentDateTime);
			$dm->persist($tokenDoc);
			$dm->flush();
		}
		
		
		
		
		return $tokenDoc->getValue();
	}
}