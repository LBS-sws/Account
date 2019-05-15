<?php

class BaladjController extends Controller 
{
	public $function_id='XE06';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('new','edit','save'),
				'expression'=>array('BaladjController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('BaladjController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new BalAdjList();
		if (isset($_POST['BalAdjList'])) {
			$model->attributes = $_POST['BalAdjList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe06']) && !empty($session['criteria_xe06'])) {
				$criteria = $session['criteria_xe06'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionSave() {
		if (isset($_POST['BalAdjForm'])) {
			$model = new BalAdjForm($_POST['BalAdjForm']['scenario']);
			$model->attributes = $_POST['BalAdjForm'];
			if ($model->validate()) {
				$model->save();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('baladj/edit',
						array(
							'city'=>$model->city,
							'year'=>$model->audit_year,
							'month'=>$model->audit_month,
							'acct_id'=>$model->acct_id,
						)
					));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($city, $year, $month, $acct_id) {
		$model = new BalAdjForm('view');
		if (!$model->retrieveData($city, $year, $month, $acct_id)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($city, $year, $month, $acct_id) {
		$model = new BalAdjForm('edit');
		if (!$model->retrieveData($city, $year, $month, $acct_id)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XE06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XE06');
	}
}
