<?php

class CashinauditController extends Controller 
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
				'actions'=>array('new','view','confirm','listfile','filedownload','viewdetail'),
				'expression'=>array('CashinauditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('CashinauditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new CashinAuditList();
		if (isset($_POST['CashinAuditList'])) {
			$model->attributes = $_POST['CashinAuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe04']) && !empty($session['criteria_xe04'])) {
				$criteria = $session['criteria_xe04'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionConfirm()
	{
		if (isset($_POST['CashinAuditForm'])) {
			$model = new CashinAuditForm($_POST['CashinAuditForm']['scenario']);
			$model->attributes = $_POST['CashinAuditForm'];
			if ($model->validate()) {
				$model->confirm();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Confirmation Done'));
				$this->redirect(Yii::app()->createUrl('cashinaudit/view',array('index'=>$model->hdr_id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$model->newData();
				$model->audit_user = '';
				$model->audit_user_pwd = '';
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new CashinAuditForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new CashinAuditForm('new');
		$model->newData();
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionViewDetail($index,$type) {
		if ($type=='IN') {
			$model = new TransInForm;
			$view = 'viewi';
		} else {
			$model = new TransOutForm;
			$view = 'viewo';
		}
		$model->retrieveData($index);
		$this->renderPartial($view, array('model'=>$model));
	}
	
	public function actionListfile($docId) {
		$d = new DocMan('TRANS',$docId,'transenq');
		echo $d->genFileListView();
	}
	
	public function actionFileDownload($docId, $fileId) {
		$sql = "select city from acc_trans where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan('TRANS', $docId,'transenq');
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
			throw new CHttpException(404,'Record not found.');
		}
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='request-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XE04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XE04');
	}
}
