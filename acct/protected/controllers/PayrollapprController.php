<?php

class PayrollapprController extends Controller 
{
	public $function_id='XS06';

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
				'actions'=>array('edit','accept','reject'),
				'expression'=>array('PayrollapprController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload'),
				'expression'=>array('PayrollapprController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PayrollApprList;
		if (isset($_POST['PayrollApprList'])) {
			$model->attributes = $_POST['PayrollApprList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xs06']) && !empty($session['criteria_xs06'])) {
				$criteria = $session['criteria_xs06'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionAccept()
	{
		if (isset($_POST['PayrollForm'])) {
			$model = new PayrollForm($_POST['PayrollForm']['scenario']);
			$model->attributes = $_POST['PayrollForm'];
			$model->scenario = 'accept';
//			if ($model->validate()) {
				$model->accept();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Acceptance Done'));
				$this->redirect(Yii::app()->createUrl('payrollappr/index'));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model));
//			}
		}
	}

	public function actionReject()
	{
		if (isset($_POST['PayrollForm'])) {
			$model = new PayrollForm($_POST['PayrollForm']['scenario']);
			$model->attributes = $_POST['PayrollForm'];
//			if ($model->validate()) {
				$model->reject();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Rejection Done'));
				$this->redirect(Yii::app()->createUrl('payrollappr/index'));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model,));
//			}
		}
	}

	public function actionEdit($index)
	{
		$model = new PayrollForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_payroll_file_hdr where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'PayrollForm');
				$docman->masterId = $mastId;
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
				throw new CHttpException(404,'Record not found.');
		}
	}

	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS06');
	}

}
