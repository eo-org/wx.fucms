<?php
return array(
	'modules' => array(
		/**
		 * *****basic modules******
		 */
		'Application',
		//'Site',
		'DoctrineMongo'
	),
	'module_listener_options' => array(
		'config_glob_paths' => array(
			'config/autoload/{,*.}{global,local}.php'
		),
		'module_paths' => array(
			'./module'
		)
	)
);
