<?php

class TranstypedefController extends Controller 
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
				'expression'=>array('TranstypedefController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('TranstypedefController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new TransTypeDefList;
		if (isset($_POST['TransTypeDefList'])) {
			$model->attributes = $_POST['TransTypeDefList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xc05']) && !empty($session['criteria_xc05'])) {
				$criteria = $session['criteria_xc05'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionSave() {
		if (isset($_POST['TransTypeDefForm'])) {
			$model = new TransTypeDefForm($_POST['TransTypeDefForm']['scenario']);
			$model->attributes = $_POST['TransTypeDefForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('transtypedef/edit',array('code'=>$model->trans_type_code,'city'=>Yii::app()->user->city())));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($code, $city) {
		$model = new TransTypeDefForm('view');
		if (!$model->retrieveData($code, $city)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($code, $city) {
		$model = new TransTypeDefForm('edit');
		if (!$model->retrieveData($code, $city)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='code-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XC05');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XC05');
	}
}
