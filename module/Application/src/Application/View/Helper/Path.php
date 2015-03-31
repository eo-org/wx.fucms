<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Path extends AbstractHelper implements ServiceLocatorAwareInterface
{
	public $helperPluginManager;
	public $path;
	public function setServiceLocator(ServiceLocatorInterface $serviceManager)
	{
		$this->helperPluginManager = $serviceManager;
		$serviceManager = $this->helperPluginManager->getServiceLocator();
		$config = $serviceManager->get('Config');
		$this->path = $config['env']['path'];
	}
	public function getServiceLocator()
	{
		return $this->helperPluginManager;
	}
	public function __invoke($pathKey)
	{
		return $this->path[$pathKey];
	}
}
