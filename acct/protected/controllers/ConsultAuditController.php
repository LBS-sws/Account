<?php

class ConsultAuditController extends Controller
{
	public $function_id='CF02';
	
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
				'expression'=>array('ConsultAuditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('ConsultAuditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ConsultAuditList();
        if (isset($_POST['ConsultAuditList'])) {
            $model->attributes = $_POST['ConsultAuditList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['consultAudit_c01']) && !empty($session['consultAudit_c01'])) {
                $criteria = $session['consultAudit_c01'];
                $model->setCriteria($criteria);
            }
        }
		if(ConsultApplyList::staffCompanyForUsername($model)){
            $model->determinePageNum($pageNum);
            $model->retrieveDataByPage($model->pageNum);
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}


	public function actionAudit()
	{
		if (isset($_POST['ConsultAuditForm'])) {
			$model = new ConsultAuditForm($_POST['ConsultAuditForm']['scenario']);
			$model->attributes = $_POST['ConsultAuditForm'];
            $model->status=2;
			if ($model->validate()) {
			    $model->reject_remark="";
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('consultAudit/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('consultAudit/edit',array("index"=>$model->id)));
			}
		}
	}

	public function actionReject()
	{
		if (isset($_POST['ConsultAuditForm'])) {
			$model = new ConsultAuditForm($_POST['ConsultAuditForm']['scenario']);
			$model->attributes = $_POST['ConsultAuditForm'];
            $model->status=3;
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('consultAudit/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('consultAudit/edit',array("index"=>$model->id)));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ConsultAuditForm('view');
        if(ConsultApplyList::staffCompanyForUsername($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ConsultAuditForm('edit');
        if(ConsultApplyList::staffCompanyForUsername($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select city from acc_consult where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ConsultAuditForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CF02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CF02');
	}
}
