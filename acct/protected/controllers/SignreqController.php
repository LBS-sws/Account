<?php

class SignreqController extends Controller 
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
				'actions'=>array('edit','sign'),
				'expression'=>array('SignreqController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload'),
				'expression'=>array('SignreqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SignReqList;
		if (isset($_POST['SignReqList'])) {
			$model->attributes = $_POST['SignReqList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xa07']) && !empty($session['criteria_xa07'])) {
				$criteria = $session['criteria_xa07'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionEdit($index)
	{
		$model = new SignReqForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

	public function actionSign()
	{
		if (isset($_POST['SignReqForm'])) {
			$model = new SignReqForm($_POST['SignReqForm']['scenario']);
			$model->attributes = $_POST['SignReqForm'];
			$model->sign();
//			$buttons = array(
//					TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
//					TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
//				);
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Reimbursement Approved'));
			$this->redirect(Yii::app()->createUrl('signreq/index'));
		}
	}

	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_request where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'SignReqForm');
				$docman->masterId = $mastId;
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='sign-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XA07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XA07');
	}
}
