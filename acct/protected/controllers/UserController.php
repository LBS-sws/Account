<?php

class UserController extends Controller 
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
				'actions'=>array('new','edit','delete','save','applytemplate'),
				'expression'=>array('UserController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('UserController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new UserList;
		if (isset($_POST['UserList'])) {
			$model->attributes = $_POST['UserList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_u1']) && !empty($session['criteria_u1'])) {
				$criteria = $session['criteria_u1'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['UserForm'])) {
			$model = new UserForm($_POST['UserForm']['scenario']);
			$model->attributes = $_POST['UserForm'];
			if ($model->validate()) {
				if ($file = CUploadedFile::getInstance($model,'signature')) {
					$model->signature_file_type = $file->type;
					$content = file_get_contents($file->tempName);
					$model->signature = base64_encode($content);
				} else {
					$model->signature_file_type = '';
					$model->signature = '';
				}
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('user/edit',array('index'=>$model->username)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new UserForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new UserForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new UserForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new UserForm('delete');
		if (isset($_POST['UserForm'])) {
			$model->attributes = $_POST['UserForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		}
//		$this->actionIndex();
		$this->redirect(Yii::app()->createUrl('user/index'));
	}

	public function actionApplytemplate($id) {
		$model = new UserForm();
		$result = $model->getTemplateData($id);
		print json_encode($result);
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
/*
	 public function loadModel($id)
	{
		$model = new UserForm;
		if (!$model->retrieveData($id))
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
*/

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('D01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('D01');
	}
}
