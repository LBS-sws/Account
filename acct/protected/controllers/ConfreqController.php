<?php

class ConfreqController extends Controller 
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
				'actions'=>array('edit','approve','deny','batchapprove'),
				'expression'=>array('ConfreqController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload','listfile','listtax'),
				'expression'=>array('ConfreqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0, $type='P') 
	{
		$session = Yii::app()->session;

		$model = new ConfReqList;
		if (isset($session['criteria_xa08']) && !empty($session['criteria_xa08'])) {
			$criteria = $session['criteria_xa08'];
			$model->setCriteria($criteria);
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$type);

		$this->render('index',array('model'=>$model,'type'=>$type));
	}

	public function actionEdit($index, $type='P')
	{
		$model = new ConfReqForm('edit');
		if (!$model->retrieveData($index, $type)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model));
		}
	}

	public function actionBatchapprove() {
		$model = new ConfReqList;
		if (isset($_POST['ConfReqList'])) {
			$model->attributes = $_POST['ConfReqList'];
//			if ($model->validate()) {
				$model->batchApprove();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Confirmed'));
				$this->redirect(Yii::app()->createUrl('confreq/index',array('type'=>$model->type,)));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model,));
//			}
		}
	}
	
	public function actionApprove()
	{
		if (isset($_POST['ConfReqForm'])) {
			$model = new ConfReqForm($_POST['ConfReqForm']['scenario']);
			$model->attributes = $_POST['ConfReqForm'];
			$model->approve();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Confirmed'));
			$this->redirect(Yii::app()->createUrl('confreq/index',array('type'=>$model->type,)));
		}
	}

	public function actionDeny()
	{
		if (isset($_POST['ConfReqForm'])) {
			$model = new ConfReqForm($_POST['ConfReqForm']['scenario']);
			$model->attributes = $_POST['ConfReqForm'];
			$model->deny();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Denied'));
			$this->redirect(Yii::app()->createUrl('confreq/index',array('type'=>$model->type,)));
		}
	}

	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_request where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'PayReqForm');
				$docman->masterId = $mastId;
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
				throw new CHttpException(404,'Record not found.');
		}
	}

	public function actionListfile($docId) {
		$d = new DocMan('PAYREQ',$docId,'ConfReqList');
		echo $d->genFileListView();
	}
	
	public function actionListtax($docId) {
		$d = new DocMan('TAX',$docId,'ConfReqList');
		echo $d->genFileListView();
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='approve-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XA08');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XA08');
	}
}
