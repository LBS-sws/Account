<?php

class ApprreqController extends Controller 
{
	public $function_id='XA05';

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
				'actions'=>array('edit','approve','deny','batchapprove','batchsign','sign'),
				'expression'=>array('ApprreqController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload','listfile','listtax'),
				'expression'=>array('ApprreqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0, $type='', $tax='') 
	{
		$session = Yii::app()->session;

		$model = new ApprReqList;
		if (isset($session['criteria_xa05']) && !empty($session['criteria_xa05'])) {
			$criteria = $session['criteria_xa05'];
			$model->setCriteria($criteria);
		}
		$model->type = empty($type) ? (empty($model->type) ? 'P' : $model->type) : $type;
		$model->showtaxonly = empty($tax) ? $model->showtaxonly : $tax=='Y';
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$model->type,$model->showtaxonly);

		$this->render('index',array('model'=>$model));
	}

	public function actionEdit($index, $type='P')
	{
		$model = new ApprReqForm('edit');
		if (!$model->retrieveData($index, $type)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model));
		}
	}

	public function actionBatchapprove() {
		$model = new ApprReqList;
		if (isset($_POST['ApprReqList'])) {
			$model->attributes = $_POST['ApprReqList'];
			$model->batchApprove();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Approved'));
			$this->redirect(Yii::app()->createUrl('apprreq/index',array('type'=>$model->type,)));
		}
	}
	
	public function actionBatchsign() {
		$model = new ApprReqList;
		if (isset($_POST['ApprReqList'])) {
			$model->attributes = $_POST['ApprReqList'];
			if ($model->validate()) {
				$model->batchSign();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Approved and Signed'));
				$this->redirect(Yii::app()->createUrl('apprreq/index',array('type'=>$model->type,)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('index',array('model'=>$model));
			}
		}
	}
	
	public function actionApprove()
	{
		if (isset($_POST['ApprReqForm'])) {
			$model = new ApprReqForm($_POST['ApprReqForm']['scenario']);
			$model->attributes = $_POST['ApprReqForm'];
			$model->approve();
//			$buttons = array(
//					TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
//					TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
//				);
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Approved'));
			$this->redirect(Yii::app()->createUrl('apprreq/index',array('type'=>$model->type,)));
		}
	}

	public function actionDeny()
	{
		if (isset($_POST['ApprReqForm'])) {
			$model = new ApprReqForm($_POST['ApprReqForm']['scenario']);
			$model->attributes = $_POST['ApprReqForm'];
			$model->deny();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Denied'));
			$this->redirect(Yii::app()->createUrl('apprreq/index',array('type'=>$model->type,)));
		}
	}

	public function actionSign()
	{
		if (isset($_POST['ApprReqForm'])) {
			$model = new ApprReqForm($_POST['ApprReqForm']['scenario']);
			$model->attributes = $_POST['ApprReqForm'];
			$model->scenario = 'sign';
			if ($model->validate()) {
				$model->sign();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Signed'));
				$this->redirect(Yii::app()->createUrl('apprreq/index',array('type'=>$model->type,)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
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
		$d = new DocMan('PAYREQ',$docId,'ApprReqList');
		echo $d->genFileListView();
	}
	
	public function actionListtax($docId) {
		$d = new DocMan('TAX',$docId,'ApprReqList');
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
		return Yii::app()->user->validRWFunction('XA05');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XA05');
	}
}
