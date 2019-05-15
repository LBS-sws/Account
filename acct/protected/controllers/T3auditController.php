<?php

class T3auditController extends Controller 
{
	public $function_id='XE05';

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
				'actions'=>array('new','edit','confirm','save','fileupload','fileremove'),
				'expression'=>array('T3auditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','adjust','back'),
				'expression'=>array('T3auditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new T3AuditList();
		if (isset($_POST['T3AuditList'])) {
			$model->attributes = $_POST['T3AuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe05']) && !empty($session['criteria_xe05'])) {
				$criteria = $session['criteria_xe05'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionConfirm() {
		if (isset($_POST['T3AuditForm'])) {
			$model = new T3AuditForm($_POST['T3AuditForm']['scenario']);
			$model->attributes = $_POST['T3AuditForm'];
			$model->audit_dt = date("Y/m/d");
			if ($model->validate()) {
				$model->confirm();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Confirmation Done'));
				$this->redirect(Yii::app()->createUrl('t3audit/view',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$model->audit_user = '';
				$model->audit_user_pwd = '';
				$model->audit_dt = '';
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSave() {
		if (isset($_POST['T3AuditForm'])) {
			$model = new T3AuditForm($_POST['T3AuditForm']['scenario']);
			$model->attributes = $_POST['T3AuditForm'];
			if ($model->validate()) {
				$model->save();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('t3audit/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index) {
		$model = new T3AuditForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index) {
		$model = new T3AuditForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew() {
		$model = new T3AuditForm('new');
		$model->newData();
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionAdjust($index) {
		if (isset($_POST['T3AuditForm'])) {
			$model = new T3AuditForm($_POST['T3AuditForm']['scenario']);
			$model->attributes = $_POST['T3AuditForm'];
			$this->render('formadj',array('model'=>$model,'index'=>$index));
		}
	}
	
	public function actionBack($index) {
		if (isset($_POST['T3AuditForm'])) {
			$model = new T3AuditForm($_POST['T3AuditForm']['scenario']);
			$model->attributes = $_POST['T3AuditForm'];
			if (!$model->isReadOnly()) {
				if ($model->validateAdjustRecord($index)) {
					$this->render('form',array('model'=>$model));
				} else {
					$message = CHtml::errorSummary($model);
					Dialog::message(Yii::t('dialog','Validation Message'), $message);
					$this->render('formadj',array('model'=>$model,'index'=>$index));
				}
			} else {
				$this->render('form',array('model'=>$model));
			}
		}
	}

	public function actionFileupload($doctype) {
		$model = new T3AuditForm();
		if (isset($_POST['T3AuditForm'])) {
			$model->attributes = $_POST['T3AuditForm'];
			
			$id = ($_POST['T3AuditForm']['scenario']=='new') ? 0 : $model->id;
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
		$model = new T3AuditForm();
		if (isset($_POST['T3AuditForm'])) {
			$model->attributes = $_POST['T3AuditForm'];
			$docman = new DocMan($doctype,$model->id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_t3_audit_hdr where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'T3AuditForm');
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
		return Yii::app()->user->validRWFunction('XE05');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XE05');
	}
}
