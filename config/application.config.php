<?php
return array(
	'modules' => array(
		/**
		 * *****basic modules******
		 */
		//'DoctrineMongo',
// 		'Application',
// 		'Sp',
		// Admin session required//
		'Admin',
	),
	'module_listener_options' => array(
		'config_glob_paths' => array(
			'config/autoload/{,*.}{global,local}.php'
		),
		'module_paths' => array(
			'./module',
			'./extra',
			'./obsolete'
			//BASE_PATH . '/fucms.panel'
		)
	)
);
