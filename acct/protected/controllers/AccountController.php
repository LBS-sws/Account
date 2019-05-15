<?php

class AccountController extends Controller 
{
	public $function_id='XC02';
	
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
				'actions'=>array('new','edit','delete','save'),
				'expression'=>array('AccountController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('AccountController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new AccountList;
		if (isset($_POST['AccountList'])) {
			$model->attributes = $_POST['AccountList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xc02']) && !empty($session['criteria_xc02'])) {
				$criteria = $session['criteria_xc02'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['AccountForm'])) {
			$model = new AccountForm($_POST['AccountForm']['scenario']);
			$model->attributes = $_POST['AccountForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('account/edit',array('index'=>$model->id,'city'=>$model->trans_city)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index,$city)
	{
		$model = new AccountForm('view');
		$city = ($city='99999') ? Yii::app()->user->city() : $city;
		if (!$model->retrieveData($index,$city)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new AccountForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index,$city)
	{
		$model = new AccountForm('edit');
		$city = ($city='99999') ? Yii::app()->user->city() : $city;
		if (!$model->retrieveData($index,$city)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new AccountForm('delete');
		if (isset($_POST['AccountForm'])) {
			$model->attributes = $_POST['AccountForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('account/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
				$this->redirect(Yii::app()->createUrl('account/index'));
			}
		}
	}
	
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='account-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XC02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XC02');
	}
}
