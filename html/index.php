<?php
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

/**
 * Validate Site Domains!
 */
$requestHost = $_SERVER['HTTP_HOST'];
$localConfig = include '../config/autoload/local.php';

define("BASE_PATH", $localConfig['env']['base_path']);
define("HOST_NAME", $requestHost);

$host = $localConfig['env']['account_fucms_db']['host'];
// $username = $localConfig['env']['account_fucms_db']['username'];
// $password = $localConfig['env']['account_fucms_db']['password'];
// $m = new MongoClient($host, array(
// 	'username' => $username,
// 	'password' => $password,
// 	'db' => 'admin'
// ));

// $db = $m->selectDb('account_fucms');
// $siteArr = $db->website->findOne(array(
// 	'domains.domainName' => $requestHost
// ));

// $currentDomain = null;
// foreach($siteArr['domains'] as $domain) {
// 	if($domain['domainName'] == $requestHost) {
// 		$currentDomain = $domain;
// 		break;
// 	}
// }
// if(array_key_exists('redirect', $currentDomain) && $currentDomain['redirect'] != $requestHost) {
// 	header("HTTP/1.1 301 Moved Permanently");
// 	header("Location: http://".$currentDomain['redirect'].$_SERVER["REQUEST_URI"]);
// 	exit(0);
// }

/**
 * This makes our life easier when dealing with paths.
 * Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// define("CACHE_PATH", BASE_PATH.'/cms-misc/cache');

include BASE_PATH . '/inc/Zend/Loader/StandardAutoloader.php';
include BASE_PATH . '/fucms/module/Application/src/Cms/SiteConfig.php';
$autoLoader = new Zend\Loader\StandardAutoloader(array(
	'namespaces' => array(
		'Zend'		=> BASE_PATH . '/inc/Zend',
		'Mobile'	=> BASE_PATH . '/inc/Mobile',
		'Core'		=> BASE_PATH . '/inc/Core',
		'Doctrine'	=> BASE_PATH . '/inc/Doctrine',
		'Qiniu'		=> BASE_PATH . '/inc/Qiniu',
		// 'Symfony' => BASE_PATH.'/inc/Symfony',
		// 'Guzzle' => BASE_PATH.'/inc/Guzzle',
		// 'Aliyun' => BASE_PATH.'/inc/Aliyun',
		'Brick'		=> BASE_PATH . '/extension/Brick', // used in backend
		                                          // 'Cms' => 'module/Application/src/Cms',
		                                          // 'ServiceAccount' => 'module/Application/src/ServiceAccount',
	),
	'prefixes' => array(
		'Twig' => BASE_PATH . '/inc/Twig',
		'App' => BASE_PATH . '/inc/App',
// 		'Class' => BASE_PATH . '/library-cms/Class'
	)
));
$autoLoader->register();

// $websiteId = $siteArr['_id']->{'$id'};
// $globalId = $siteArr['globalSiteId'];
// $server = $db->server->findOne(array(
// 	'_id' => $siteArr['server']['$id']
// ));
// $internalIpAddress = $server['internalIpAddress'];

// Cms\SiteConfig::setId($websiteId, $globalId, $internalIpAddress);
// Cms\SiteConfig::setAuth($server['user'], $server['pass']);

// if(isset($siteArr['extraModule'])) {
// 	$extraModule = $siteArr['extraModule'];
// } else {
// 	$extraModule = array();
// }

// Cms\SiteConfig::setExtraModule($extraModule);

$extraModule = array();
$applicationArr = include 'config/application.config.php';
$applicationArr['modules'] = array_merge($applicationArr['modules'], $extraModule);
$application = Zend\Mvc\Application::init($applicationArr);
$application->run();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

if(! isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	// echo "<!--".$totaltime."-->";
}