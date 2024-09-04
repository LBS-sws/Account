<?php

class ExpenseApplyController extends Controller
{
	public $function_id='DE01';
	
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
				'actions'=>array('new','edit','delete','save','audit','fileupload','fileremove','print'),
				'expression'=>array('ExpenseApplyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','listFile'),
				'expression'=>array('ExpenseApplyController','allowReadOnly'),
			),
			array('allow',
				'actions'=>array('ajaxTrip'),
				'expression'=>array('ExpenseApplyController','allowAjax'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxTrip()
    {
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $index = key_exists("trip_id",$_POST)?$_POST["trip_id"]:0;
            $data =ExpenseFun::getAjaxTripDataForId($index);
            echo CJSON::encode($data);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('expenseApply/index'));
        }
    }

    public function actionPrint($index)
    {
        $model = new ExpenseApplyForm;
        if (!$model->retrievePrint($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->printOne();
            Yii::app()->end();
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpenseApplyList();
		if (isset($_POST['ExpenseApplyList'])) {
			$model->attributes = $_POST['ExpenseApplyList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expenseApply_c01']) && !empty($session['expenseApply_c01'])) {
				$criteria = $session['expenseApply_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['ExpenseApplyForm'])) {
			$model = new ExpenseApplyForm($_POST['ExpenseApplyForm']['scenario']);
			$model->attributes = $_POST['ExpenseApplyForm'];
            $model->status_type=0;
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('expenseApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionAudit()
	{
		if (isset($_POST['ExpenseApplyForm'])) {
			$model = new ExpenseApplyForm($_POST['ExpenseApplyForm']['scenario']);
			$model->attributes = $_POST['ExpenseApplyForm'];
            $model->status_type=2;
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('expenseApply/edit',array('index'=>$model->id)));
			} else {
                $model->status_type=0;
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ExpenseApplyForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new ExpenseApplyForm('new');
		ExpenseFun::setModelEmployee($model,"employee_id");
		if($model->validateEmployee("employee_id",'')){
            $this->render('form',array('model'=>$model,));
        }else{
            throw new CHttpException(404,'账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ExpenseApplyForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ExpenseApplyForm('delete');
		if (isset($_POST['ExpenseApplyForm'])) {
			$model->attributes = $_POST['ExpenseApplyForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('expenseApply/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}

    public function actionFileupload() {
        $model = new ExpenseApplyForm();
        if (isset($_POST['ExpenseApplyForm'])) {
            $model->attributes = $_POST['ExpenseApplyForm'];

            $docman = new DocMan($model->docType,$model->docId,get_class($model));
            $docman->setDocMasterId($model->docType,$model->docId,$model->docMasterId);
            if (isset($_FILES["attachmentEx"])) $docman->files = $_FILES["attachmentEx"];
            $docman->fileUpload();
            $result = $docman->genTableFileListEx($model->readonly());
            print json_encode($result);
        } else {
            echo "NIL";
        }
    }

    public function actionFileRemove() {
        $model = new ExpenseApplyForm();
        if (isset($_POST['ExpenseApplyForm'])) {
            $model->attributes = $_POST['ExpenseApplyForm'];

            $docman = new DocMan($model->docType,$model->docId,'ExpenseApplyForm');
            $docman->setDocMasterId($model->docType,$model->docId,$model->docMasterId);
            $docman->fileRemove($model->removeFileId);
            $result = $docman->genTableFileListEx($model->readonly());
            print json_encode($result);
        } else {
            echo "NIL";
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
            $docman = new DocMan($doctype,$docId,'ExpenseApplyForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public function actionListFile() {
        $model = new ExpenseApplyForm();
        if (isset($_POST['ExpenseApplyForm'])) {
            $model->attributes = $_POST['ExpenseApplyForm'];

            $docman = new DocMan($model->docType,$model->docId,'ExpenseApplyForm');
            $docman->setDocMasterId($model->docType,$model->docId,$model->docMasterId);
            $result = $docman->genTableFileListEx($model->readonly());
            print json_encode($result);
        } else {
            echo "NIL";
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE01');
	}

	public static function allowAjax() {
		return true;
	}
}
