<?php

class AcctfileController extends Controller 
{
	public $function_id='XE07';

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
				'actions'=>array('edit','save','send','fileupload','fileremove'),
				'expression'=>array('AcctfileController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('AcctfileController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new AcctfileList;
		if (isset($_POST['AcctfileList'])) {
			$model->attributes = $_POST['AcctfileList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe07']) && !empty($session['criteria_xe07'])) {
				$criteria = $session['criteria_Xe07'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionSend()
	{
		if (isset($_POST['AcctfileForm'])) {
			$model = new AcctfileForm($_POST['AcctfileForm']['scenario']);
			$model->attributes = $_POST['AcctfileForm'];
			$model->send();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Email Sent'));
			$this->redirect(Yii::app()->createUrl('acctfile/edit',array('index'=>$model->id)));
		}
	}

	public function actionSave()
	{
		if (isset($_POST['AcctfileForm'])) {
			$model = new AcctfileForm($_POST['AcctfileForm']['scenario']);
			$model->attributes = $_POST['AcctfileForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('acctfile/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index, $rtn='index')
	{
		$model = new AcctfileForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index, $rtn='index')
	{
		$model = new AcctfileForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionFileupload($doctype) {
		$model = new AcctfileForm();
		if (isset($_POST['AcctfileForm'])) {
			$model->attributes = $_POST['AcctfileForm'];
			
			$id = $model->id;
			$docman = new DocMan($doctype,$id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
			$docman->fileUpload();
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileRemove($doctype) {
		$model = new AcctfileForm();
		if (isset($_POST['AcctfileForm'])) {
			$model->attributes = $_POST['AcctfileForm'];
			$docman = new DocMan($doctype,$model->id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_account_file_hdr where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'AcctfileForm');
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
		return Yii::app()->user->validRWFunction('XE07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XE07');
	}

}
