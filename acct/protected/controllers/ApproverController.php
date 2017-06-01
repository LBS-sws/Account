<?php

class ApproverController extends Controller 
{
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
				'actions'=>array('edit','save'),
				'expression'=>array('ApproverController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('view','index'),
				'expression'=>array('ApproverController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() 
	{
		$url = ($this->allowReadWrite()) ? 'approver/edit' : 'approver/view';
		$this->redirect(Yii::app()->createUrl($url));
	}


	public function actionSave()
	{
		if (isset($_POST['ApproverForm'])) {
			$model = new ApproverForm($_POST['ApproverForm']['scenario']);
			$model->attributes = $_POST['ApproverForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('approver/edit'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView()
	{
		$model = new ApproverForm('view');
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit()
	{
		$model = new ApproverForm('edit');
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XC04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XC04');
	}
}
