<?php
namespace Application\Service;

use Application\Document\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Logger implements ServiceLocatorAwareInterface
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
	
	public function write($msg)
	{
		$wxDm = $this->getServiceLocator()->get('DocumentManager');
		
		$logDoc = new Logger();
		$logDoc->setMsg($msg);
		
		$wxDm->clear();
		$wxDm->persist($logDoc);
		$wxDm->flush();
	}
}