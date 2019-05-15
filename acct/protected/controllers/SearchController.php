<?php

class SearchController extends Controller
{
	public $interactive = false;
	
	public function filters()
	{
		return array(
			'enforceSessionExpiration', 
			'accessControl', 
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionStorecriteria($model) {
		if (isset($_POST[$model])) {
			$criteria = array();
			if (isset($_POST[$model]['searchField'])) $criteria['searchField'] = $_POST[$model]['searchField'];
			if (isset($_POST[$model]['searchValue'])) $criteria['searchValue'] = $_POST[$model]['searchValue'];
			if (isset($_POST[$model]['orderField'])) $criteria['orderField'] = $_POST[$model]['orderField'];
			if (isset($_POST[$model]['orderType'])) $criteria['orderType'] = $_POST[$model]['orderType'];
			if (isset($_POST[$model]['noOfItem'])) $criteria['noOfItem'] = $_POST[$model]['noOfItem'];
			if (isset($_POST[$model]['pageNum'])) $criteria['pageNum'] = $_POST[$model]['pageNum'];
			if (isset($_POST[$model]['filter'])) $criteria['filter'] = $_POST[$model]['filter'];

			$session = Yii::app()->session;
			$session['criteria_'.$model] = $criteria;
			echo 'success';
		} 
	}
	
	public function actionClearcriteria($model) {
		$session = Yii::app()->session;
		$criteria = $session['criteria_'.$model];
		$criteria['filter']='';
		$criteria['searchField']='';
		$criteria['searchValue']='';
		$session['criteria_'.$model] = $criteria;
		echo 'success';
	}
	
}