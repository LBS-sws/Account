<?php

class ExpenseAuditController extends Controller
{
	public $function_id='DE03';
	
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
				'expression'=>array('ExpenseAuditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','listFile'),
				'expression'=>array('ExpenseAuditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpenseAuditList();
		if (isset($_POST['ExpenseAuditList'])) {
			$model->attributes = $_POST['ExpenseAuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expenseAudit_c01']) && !empty($session['expenseAudit_c01'])) {
				$criteria = $session['expenseAudit_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionAudit()
	{
		if (isset($_POST['ExpenseAuditForm'])) {
			$model = new ExpenseAuditForm("audit");
			$model->attributes = $_POST['ExpenseAuditForm'];
			if ($model->validate()) {
                $model->status_type=2;
                $bool = $model->saveData();
				$model->scenario = 'edit';
                if($bool){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Audit Done'));
                    $this->redirect(Yii::app()->createUrl('expenseAudit/index'));
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("金蝶系统异常", $message);
                    $this->redirect(Yii::app()->createUrl('expenseAudit/edit',array("index"=>$model->id)));
                }
			} else {
                $model->scenario = 'edit';
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('expenseAudit/edit',array("index"=>$model->id)));
			}
		}
	}

	public function actionReject()
	{
		if (isset($_POST['ExpenseAuditForm'])) {
			$model = new ExpenseAuditForm("reject");
			$model->attributes = $_POST['ExpenseAuditForm'];
			if ($model->validate()) {
			    $model->status_type=3;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Reject Done'));
				$this->redirect(Yii::app()->createUrl('expenseAudit/index'));
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
		$model = new ExpenseAuditForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new ExpenseAuditForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        if($doctype==="EXINFO"){
            $sql = "select id from acc_expense_info where id = $docId";
        }else{
            $sql = "select id from acc_expense where id = $docId";
        }
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ExpenseAuditForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public function actionListFile() {
        $model = new ExpenseAuditForm();
        if (isset($_POST['ExpenseAuditForm'])) {
            $model->attributes = $_POST['ExpenseAuditForm'];
            $docman = new DocMan($model->docType,$model->docId,'ExpenseAuditForm');
            $docman->setDocMasterId($model->docType,$model->docId,$model->docMasterId);
            $result = $docman->genTableFileListEx(true);
            print json_encode($result);
        } else {
            echo "NIL";
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE03');
	}
}
