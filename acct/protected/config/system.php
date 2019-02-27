<?php

return array(
	'drs'=>array(
		'webroot'=>'http://118.89.46.224/dr-uat',
		'name'=>'Daily Report',
		'icon'=>'fa fa-pencil-square-o',
	),
	'acct'=>array(
		'webroot'=>'http://118.89.46.224/ac-uat',
		'name'=>'Accounting',
		'icon'=>'fa fa-money',
	),
	'ops'=>array(
		'webroot'=>'http://118.89.46.224/op-uat',
		'name'=>'Operation',
		'icon'=>'fa fa-gears',
	),
	'hr'=>array(
		'webroot'=>'http://118.89.46.224/hr-uat',
		'name'=>'Personnel',
		'icon'=>'fa fa-users',
	),
	'sal'=>array(
		'webroot'=>'http://118.89.46.224/sa-uat',
		'name'=>'Sales',
		'icon'=>'fa fa-suitcase',
	),
	'quiz'=>array(
		'webroot'=>'http://118.89.46.224/qz-uat',
		'name'=>'Quiz',
		'icon'=>'fa fa-pencil',
	),
	'sp'=>array(
		'webroot'=>'http://118.89.46.224/sp-uat',
		'name'=>'Academic Credit',
		'icon'=>'fa fa-cube',
	),
	'onlib'=>array(
		'webroot'=>'https://onlib.lbsapps.com/seeddms',
		'script'=>'remoteLoginOnlib',
		'name'=>'Online Library',
		'icon'=>'fa fa-book',
		'external'=>array(
				'layout'=>'onlib',
				'update'=>'saveOnlib',		//function defined in UserFormEx.php
				'fields'=>'fieldsOnlib',
			),
	),
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
