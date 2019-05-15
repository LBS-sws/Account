<?php

class AcctitemController extends Controller 
{
	public $function_id='XC06';

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
				'expression'=>array('AcctitemController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('AcctitemController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new AcctItemList;
		if (isset($_POST['AcctItemList'])) {
			$model->attributes = $_POST['AcctItemList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xc06']) && !empty($session['criteria_xc06'])) {
				$criteria = $session['criteria_xc06'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['AcctItemForm'])) {
			$model = new AcctItemForm($_POST['AcctItemForm']['scenario']);
			$model->attributes = $_POST['AcctItemForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('acctitem/edit',array('index'=>$model->code)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new AcctItemForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new AcctItemForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new AcctItemForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new AcctItemForm('delete');
		if (isset($_POST['AcctItemForm'])) {
			$model->attributes = $_POST['AcctItemForm'];
//			if ($model->isOccupied($model->id)) {
//				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
//				$this->redirect(Yii::app()->createUrl('acctitem/edit',array('index'=>$model->id)));
//			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
				$this->redirect(Yii::app()->createUrl('acctitem/index'));
//			}
		}
	}
	
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='code-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XC06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XC06');
	}
}
