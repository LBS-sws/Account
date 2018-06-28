<?php

class RealizeController extends Controller 
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
				'actions'=>array('edit','submit','batchsubmit','trans','cancel','fileupload','fileremove'),
				'expression'=>array('RealizeController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','filedownload','listfile','listtax','listpayreal'),
				'expression'=>array('RealizeController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new RealizeList;
		if (isset($_POST['RealizeList'])) {
			$model->attributes = $_POST['RealizeList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xa06']) && !empty($session['criteria_xa06'])) {
				$criteria = $session['criteria_xa06'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionSubmit()
	{
		if (isset($_POST['RealizeForm'])) {
			$model = new RealizeForm($_POST['RealizeForm']['scenario']);
			$model->attributes = $_POST['RealizeForm'];
			$model->scenario = 'submit';
			if ($model->validate()) {
				$model->submit();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Submission Done'));
				$this->redirect(Yii::app()->createUrl('realize/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionBatchsubmit()
	{
		$model = new RealizeList;
		if (isset($_POST['RealizeList'])) {
			$model->attributes = $_POST['RealizeList'];
			if ($model->validate()) {
				$model->batchSubmit();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Submission Done'));
				$this->redirect(Yii::app()->createUrl('realize/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('index',array('model'=>$model,));
			}
		}
	}

	public function actionTrans()
	{
		if (isset($_POST['RealizeForm'])) {
			$model = new RealizeForm($_POST['RealizeForm']['scenario']);
			$model->attributes = $_POST['RealizeForm'];
			$model->scenario = 'trans';
			if ($model->validate()) {
				$model->gentrans();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Transaction Creation Done'));
				$this->redirect(Yii::app()->createUrl('realize/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionCancel()
	{
		if (isset($_POST['RealizeForm'])) {
			$model = new RealizeForm($_POST['RealizeForm']['scenario']);
			$model->attributes = $_POST['RealizeForm'];
			$model->cancel();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Cancel Done'));
			$this->redirect(Yii::app()->createUrl('realize/index'));
		}
	}

	public function actionEdit($index)
	{
		$model = new RealizeForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionFileupload($doctype) {
		$model = new RealizeForm();
		if (isset($_POST['RealizeForm'])) {
			$model->attributes = $_POST['RealizeForm'];
			
			$id = ($_POST['RealizeForm']['scenario']=='new') ? 0 : $model->id;
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
		$model = new RealizeForm();
		if (isset($_POST['RealizeForm'])) {
			$model->attributes = $_POST['RealizeForm'];
			$docman = new DocMan($doctype,$model->id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_request where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'RealizeForm');
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
		$d = new DocMan('PAYREQ',$docId,'RealizeList');
		echo $d->genFileListView();
	}
	
	public function actionListtax($docId) {
		$d = new DocMan('TAX',$docId,'RealizeList');
		echo $d->genFileListView();
	}

	public function actionListpayreal($docId) {
		$d = new DocMan('PAYREAL',$docId,'RealizeList');
		echo $d->genFileListView();
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
		return Yii::app()->user->validRWFunction('XA06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XA06');
	}
}
