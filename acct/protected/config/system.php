<?php

return array(
	'drs'=>array(
		'webroot'=>'http://192.168.3.32/dr-new',
		'name'=>'Daily Report',
		'icon'=>'fa fa-pencil-square-o',
	),
	'acct'=>array(
		'webroot'=>'http://192.168.3.32/ac-new',
		'name'=>'Accounting',
		'icon'=>'fa fa-money',
	),
//	'ops'=>array(
//		'webroot'=>'http://192.168.3.22/op-new',
//		'name'=>'Operation',
//		'icon'=>'fa fa-gears',
//	),
	'hr'=>array(
		'webroot'=>'http://192.168.3.32/hr-new',
		'name'=>'Personnel',
		'icon'=>'fa fa-users',
	),
	'sal'=>array(
		'webroot'=>'http://192.168.3.32/sa-new',
		'name'=>'Sales',
		'icon'=>'fa fa-suitcase',
	),
	'nu'=>array(
		'webroot'=>'http://192.168.3.32/nu',
		'name'=>'New United',
		'icon'=>'fa fa-suitcase',
		'param'=>'/admin',
		'script'=>'goNewUnited',
	),
//	'quiz'=>array(
//		'webroot'=>'http://192.168.3.22/qz-new',
//		'name'=>'Quiz',
//		'icon'=>'fa fa-pencil',
//	),
//	'sp'=>array(
//		'webroot'=>'http://192.168.3.22/sp-new',
//		'name'=>'Academic Credit',
//		'icon'=>'fa fa-cube',
//	),
//	'onlib'=>array(
//		'webroot'=>'https://onlib.lbsapps.com/seeddms',
//		'script'=>'remoteLoginOnlib',
//		'name'=>'Online Library',
//		'icon'=>'fa fa-book',
//		'external'=>array(
//				'layout'=>'onlib',
//				'update'=>'saveOnlib',		//function defined in UserFormEx.php
//				'fields'=>'fieldsOnlib',
//			),
//	),
/*
	'apps'=>array(
		'webroot'=>'https://app.lbsgroup.com.tw/web',
		'script'=>'remoteLoginTwApp',
		'name'=>'Apps System',
		'icon'=>'fa fa-rocket',
		'external'=>true,
	),
*/
);

?>
