<?php
return array(
	'env' => array(
		'usage' => array(
			'server' => 'production',
		),
		'base_path' => '/var/www',
		'path' => array(
			'fucms' => 'http://lib-media.qiniudn.com/fucms',
			'src'	=> 'http://lib-media.qiniudn.com/src',
			'compact' => 'http://lib-media.qiniudn.com/compact',
			'aliyun' => 'http://en-developer.oss-cn-hangzhou.aliyuncs.com',
			'qiniu' => 'http://en-developer.qiniudn.com',
			'upyun' => 'http://en-developer.b0.upaiyun.com'
		),
		'wx' => array(
			'appId' => 'wx2ce4babba45b702d',
			'appSecret' => '0c79e1fa963cd80cc0be99b20a18faeb',
			'token' => 'fucmsweb2015weixinopen888',
			'encryptKey' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqco4',
			'path' => array(
				'accessToken' => 'https://api.weixin.qq.com/cgi-bin/component/api_component_token',
				'preAuthCode' => 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=',
				'redirectUri' => 'http://wx.fucmsweb.com/callback'
			),
		),
	),
);