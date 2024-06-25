<?php

class ExpensePaymentController extends Controller
{
	public $function_id='DE07';
	
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
				'actions'=>array('edit','audit','reject','shift','fileupload'),
				'expression'=>array('ExpensePaymentController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('ExpensePaymentController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpensePaymentList();
		if (isset($_POST['ExpensePaymentList'])) {
			$model->attributes = $_POST['ExpensePaymentList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expensePayment_c01']) && !empty($session['expensePayment_c01'])) {
				$criteria = $session['expensePayment_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionAudit()
	{
		if (isset($_POST['ExpensePaymentForm'])) {
			$model = new ExpensePaymentForm("audit");
			$model->attributes = $_POST['ExpensePaymentForm'];
			if ($model->validate()) {
                $model->status_type=6;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Confirm Done'));
                $this->redirect(Yii::app()->createUrl('expensePayment/index'));
			} else {
                $model->scenario = 'edit';
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionReject()
	{
		if (isset($_POST['ExpensePaymentForm'])) {
			$model = new ExpensePaymentForm("reject");
			$model->attributes = $_POST['ExpensePaymentForm'];
			if ($model->validate()) {
                $model->status_type=3;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Reject Done'));
				$this->redirect(Yii::app()->createUrl('expensePayment/index'));
			} else {
                $model->scenario = 'edit';
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionShift()
	{
		if (isset($_POST['ExpensePaymentForm'])) {
			$model = new ExpensePaymentForm("shift");
			$model->attributes = $_POST['ExpensePaymentForm'];
			if ($model->validate()) {
			    $model->status_type=11;//转移的临时状态
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('give','Shift Done'));
				$this->redirect(Yii::app()->createUrl('expensePayment/index'));
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
		$model = new ExpensePaymentForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new ExpensePaymentForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionFileupload($doctype) {
        $model = new ExpensePaymentForm();
        if (isset($_POST['ExpensePaymentForm'])) {
            $model->attributes = $_POST['ExpensePaymentForm'];

            $id = ($_POST['ExpensePaymentForm']['scenario']=='new') ? 0 : $model->id;
            $docman = new DocMan($model->docType,$id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
            $docman->fileUpload();
            echo $docman->genTableFileList(false,true);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id from acc_expense where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ExpensePaymentForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE07');
	}
}
