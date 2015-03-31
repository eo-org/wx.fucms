<?php
return array(
	'env' => array(
		'usage' => 'production',
		'base_path' => '/var/www',
		'domain' => array(
			'idp' => 'idp.fucms.com',
			'lib' => 'misc.fucms.com'
		),
		'path' => array(
			'fucms' => 'http://lib-media.qiniudn.com/fucms',
			'src'	=> 'http://lib-media.qiniudn.com/src',
			'compact' => 'http://lib-media.qiniudn.com/compact',
			'aliyun' => 'http://en-developer.oss-cn-hangzhou.aliyuncs.com',
			'qiniu' => 'http://en-developer.qiniudn.com',
			'upyun' => 'http://en-developer.b0.upaiyun.com'
		),
		// developer aliyun key and bucket
		'aliyun' => array(
			'keyId' => 'FUrG5NHTIlcMvp2a',
			'keySecret' => 'PMjYlbIbTJ5ak2zp9nTHOFid4aCfaJ',
			'bucket' => 'en-developer'
		),
		// developer qiniu key and bucket
		'qiniu' => array(
			'keyId' => 'xo6Ap0TOfDWSNQtSYCdeb4nSg-1oilUgJ4i27GsK',
			'keySecret' => 'K2j2Vq9uizQcjemFkRBTOlTAQ5v1rkZYnVaXWyog',
			'bucket' => 'en-developer'
		),
		'upyun' => array(
			'keyId' => 'kingavin',
			'keySecret' => 'K2j2Vq9uizQcjemFkRBTOlTAQ5v1rkZYnVaXWyog',
			'bucket' => 'en-developer'
		)
	),
	'minlib-version' => '20150325',
	'version' => '1.0.0'
);