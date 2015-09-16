<?php
namespace DoctrineMongo;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use Doctrine\Common\Persistence\PersistentObject,
Doctrine\ODM\MongoDB\DocumentManager,
Doctrine\ODM\MongoDB\Configuration,
Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver,
Doctrine\MongoDB\Connection;

class Module implements BootstrapListenerInterface
{
	public function onBootstrap(EventInterface $event)
	{
		$application = $event->getTarget();
		$sm = $application->getServiceManager();
		
		$fileConfig = $sm->get('Config');
		$env = $fileConfig['env'];
		$auth = $fileConfig['auth'];
		
		AnnotationDriver::registerAnnotationClasses();
		$config = new Configuration();
		$config->setDefaultDB('wx_fucms');
		
		$config->setProxyDir(__DIR__ . '/../../doctrineCache');
		$config->setProxyNamespace('DoctrineMongoProxy');
		$config->setHydratorDir(__DIR__ . '/../../doctrineCache');
		$config->setHydratorNamespace('DoctrineMongoHydrator');
		$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/../../doctrineCache/class'));
// 		die($env['usage']['server']);
		if($env['usage']['server'] == 'production' && false) {
			$config->setAutoGenerateHydratorClasses(false);
			$config->setAutoGenerateProxyClasses(false);
		}
		$connection = new Connection('127.0.0.1', array(
			'username' => $auth['db']['username'],
			'password' => $auth['db']['password'],
			'db' => 'admin'
		));
		$connection->initialize();
		
		$dm = DocumentManager::create($connection, $config);
		PersistentObject::setObjectManager($dm);
		$sm->setService('DocumentManager', $dm);
	}
}