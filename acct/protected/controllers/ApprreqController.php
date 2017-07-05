<?php

class ApprreqController extends Controller 
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
				'actions'=>array('edit','approve','deny'),
				'expression'=>array('ApprreqController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload'),
				'expression'=>array('ApprreqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0, $type='P') 
	{
		$session = Yii::app()->session;

		$model = new ApprReqList;
		if (isset($session['criteria_xa05']) && !empty($session['criteria_xa05'])) {
			$criteria = $session['criteria_xa05'];
			$model->setCriteria($criteria);
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$type);

		$this->render('index',array('model'=>$model,'type'=>$type));
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
