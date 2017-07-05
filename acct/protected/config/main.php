<?php

// uncomment the following to define a path alias
//Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'id'=>'swoper',
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'charset'=>'UTF-8',
	'name'=>'LBS Daily Management - UAT',
	'timeZone'=>'Asia/Hong_Kong',
	'sourceLanguage'=> 'en',
	'language'=>'zh_cn',

	'aliases'=>array(
		'bootstrap'=>realpath(__DIR__.'/../extensions/bootstrap'),
		),

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.YiiMailer.YiiMailer',
		'bootstrap.helpers.*',
		'bootstrap.widgets.*',
		'bootstrap.components.*',
		'bootstrap.form.*',
		'bootstrap.behaviors.*',
	),

	'modules'=>array(
//		'gii'=>array(
//			'class'=>'system.gii.GiiModule',
//			'password'=>'123456',
//			// If removed, Gii defaults to localhost only. Edit carefully to taste.
//			'ipFilters'=>array('192.168.1.104','::1'),
//
//		),
//		'gii'=>array(
//			'generatorPaths'=>array('bootstrap.gii'),
//		),
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'class'=>'WebUser',
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format

		'urlManager'=>array(
			'urlFormat'=>'path',
//			'showScriptName'=>false,
//			'caseSensitive'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),

		'bootstrap'=>array(
//			'class'=>'bootstrap.components.TbApi',
			'class'=>'TbApiEx',
		),

		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=account',
			'emulatePrepare' => true,
			'username' => 'swuser',
			'password' => 'swisher168',
			'charset' => 'utf8',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				array(
					'class'=>'CWebLogRoute',
				//	'levels'=>'trace',
				//	'categories'=>'vardump',
				//	'showInFireBug'=>true
				),
			),
		),
		
		'session'=>array(
			'class'=>'CHttpSession',
			'cookieMode'=>'allow',
			'cookieParams'=>array(
				'domain'=>'test.lbsgroup.com.cn',
			),
		),
		
		// Cache module only if memcached installed
		/*
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array(
					'host'=>'127.0.0.1',
					'port'=>11211,
					'weight'=>100,
				),
			),
		),
		*/
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'adminEmail'=>'it@lbsgroup.com.hk',
		'checkStation'=>false,
		'validRegDuration'=>'3 hours',
//		'cookieDomain'=>'swoper',
//		'cookiePath'=>'/',
		'concurrentLogin'=>false,
		'noOfLoginRetry'=>5,
		'sessionIdleTime'=>'1 hour',
		'feedbackCcBoss'=>array('boss1','boss2'),
		'bossEmail'=>array('kcleepercy@gmail.com','kcleepercy@yahoo.com.hk'),
		'version'=>'1.1.0',
		'docmanPath'=>'/docman/dev',
		'systemId'=>'acct',
		'envSuffix'=>'dev',
		'systemMapping'=>array(
				'drs'=>array(
						'webroot'=>'http://test.lbsgroup.com.cn/swoper-web',
						'name'=>'Daily Report',
						'icon'=>'fa fa-pencil-square-o',
					),	
				'acct'=>array(
						'webroot'=>'http:///test.lbsgroup.com.cn/acct',
						'name'=>'Accounting',
						'icon'=>'fa fa-money',
					),
				'ops'=>array(
						'webroot'=>'http://test.lbsgroup.com.cn/operation',
						'name'=>'Operation',
						'icon'=>'fa fa-gears',
					),
				'hr'=>array(
						'webroot'=>'http://test.lbsgroup.com.cn/hr',
						'name'=>'Personnel',
						'icon'=>'fa fa-users',
					),
			),
	),
);
