<?php

class TemporaryAuditController extends Controller
{
	public $function_id='TA02';
	
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
				'actions'=>array('edit','audit','reject'),
				'expression'=>array('TemporaryAuditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('TemporaryAuditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new TemporaryAuditList();
		if (isset($_POST['TemporaryAuditList'])) {
			$model->attributes = $_POST['TemporaryAuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['temporaryAudit_c01']) && !empty($session['temporaryAudit_c01'])) {
				$criteria = $session['temporaryAudit_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionAudit()
	{
		if (isset($_POST['TemporaryAuditForm'])) {
			$model = new TemporaryAuditForm("audit");
			$model->attributes = $_POST['TemporaryAuditForm'];
			if ($model->validate()) {
                $model->status_type=2;
				$bool = $model->saveData();
				$model->scenario = 'edit';
				if($bool){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Audit Done'));
                    $this->redirect(Yii::app()->createUrl('temporaryAudit/index'));
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("金蝶系统异常", $message);
                    $this->redirect(Yii::app()->createUrl('temporaryAudit/edit',array("index"=>$model->id)));
                }
			} else {
                $model->scenario = 'edit';
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('temporaryAudit/edit',array("index"=>$model->id)));
			}
		}
	}

	public function actionReject()
	{
		if (isset($_POST['TemporaryAuditForm'])) {
			$model = new TemporaryAuditForm("reject");
			$model->attributes = $_POST['TemporaryAuditForm'];
			if ($model->validate()) {
			    $model->status_type=3;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Reject Done'));
				$this->redirect(Yii::app()->createUrl('temporaryAudit/index'));
			} else {
                $model->scenario = 'edit';
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new TemporaryAuditForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new TemporaryAuditForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id from acc_expense where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'TemporaryAuditForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('TA02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('TA02');
	}
}
