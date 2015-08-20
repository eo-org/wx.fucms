<?php
namespace Application\Service\Db;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Doctrine\ODM\MongoDB\DocumentManager, Doctrine\ODM\MongoDB\Configuration, Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver, Doctrine\MongoDB\Connection;

use Application\SiteInfo;

class WxDocumentManagerFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DocumentManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$application = $event->getTarget();
		$sm = $application->getServiceManager();
		
		$fileConfig = $sm->get('Config');
		$env = $fileConfig['env'];
		
		AnnotationDriver::registerAnnotationClasses();
		$config = new Configuration();
		$config->setDefaultDB('wx_fucms');
		
		$config->setProxyDir(__DIR__ . '/../../doctrineCache');
		$config->setProxyNamespace('DoctrineMongoProxy');
		$config->setHydratorDir(__DIR__ . '/../../doctrineCache');
		$config->setHydratorNamespace('DoctrineMongoHydrator');
		$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/../../doctrineCache/class'));
		
		if($env['usage']['server'] == 'production' && false) {
			$config->setAutoGenerateHydratorClasses(false);
			$config->setAutoGenerateProxyClasses(false);
		}
		$connection = new Connection('127.0.0.1', array(
			'username' => 'craftgavin',
			'password' => 'whothirstformagic?',
			'db' => 'admin'
		));
		$connection->initialize();
		
		$wxDm = DocumentManager::create($connection, $config);
		return $wxDm;
    }
}