<?php
namespace Application\Service\Db;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Doctrine\Common\Persistence\PersistentObject, Doctrine\ODM\MongoDB\DocumentManager, Doctrine\ODM\MongoDB\Configuration, Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver, Doctrine\MongoDB\Connection;

use Application\SiteInfo;

class CmsDocumentManagerFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DocumentManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
    	
    	$config = $serviceLocator->get('Config');
    	
    	$host = $config['env']['account_fucms_db']['host'];
    	$username = $config['env']['account_fucms_db']['username'];
    	$password = $config['env']['account_fucms_db']['password'];
    	$m = new \MongoClient($host, array(
    		'username' => $username,
    		'password' => $password,
    		'db' => 'admin'
    	));
    	
    	$db = $m->selectDb('account_fucms');
    	$siteArr = $db->website->findOne(array(
    		'_id' => new \MongoId(SiteInfo::getWebsiteId())
    	));
    	
    	$globalId = $siteArr['globalSiteId'];
    	
    	$server = $db->server->findOne(array(
    		'_id' => $siteArr['server']['$id']
    	));
    	$internalIpAddress = $server['internalIpAddress'];
    	
		$sm = $serviceLocator;
		
		$fileConfig = $sm->get('Config');
		$env = $fileConfig['env'];
		
		$host = $internalIpAddress;
		$dbName = 'cms_' . $globalId;
		
		AnnotationDriver::registerAnnotationClasses();
		$config = new Configuration();
		$config->setDefaultDB($dbName);
		
		$config->setProxyDir(BASE_PATH . '/wx.fucms/doctrineCache');
		$config->setProxyNamespace('DoctrineMongoProxy');
		$config->setHydratorDir(BASE_PATH . '/wx.fucms/doctrineCache');
		$config->setHydratorNamespace('DoctrineMongoHydrator');
		$config->setMetadataDriverImpl(AnnotationDriver::create(BASE_PATH . '/class'));
		if($env['usage']['server'] == 'production' && false) {
			$config->setAutoGenerateHydratorClasses(false);
			$config->setAutoGenerateProxyClasses(false);
		}
		$connection = new Connection($host, array(
			'username' => $username,
			'password' => $password,
			'db' => 'admin'
		));
		$connection->initialize();
		$dm = DocumentManager::create($connection, $config);
		//PersistentObject::setObjectManager($dm);
		
		return $dm;
    }
}
