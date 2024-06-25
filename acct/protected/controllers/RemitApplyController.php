<?php

class RemitApplyController extends Controller
{
	public $function_id='RT01';
	
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
				'actions'=>array('new','edit','delete','save','audit','fileupload','fileremove','print','ajaxPayee'),
				'expression'=>array('RemitApplyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('RemitApplyController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxPayee($group='',$city='')
    {
        echo ExpenseFun::AjaxPayee($group,$city);
    }

    public function actionPrint($index)
    {
        $model = new RemitApplyForm;
        if (!$model->retrievePrint($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->printOne();
            Yii::app()->end();
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new RemitApplyList();
		if (isset($_POST['RemitApplyList'])) {
			$model->attributes = $_POST['RemitApplyList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['remitApply_c01']) && !empty($session['remitApply_c01'])) {
				$criteria = $session['remitApply_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['RemitApplyForm'])) {
			$model = new RemitApplyForm($_POST['RemitApplyForm']['scenario']);
			$model->attributes = $_POST['RemitApplyForm'];
            $model->status_type=0;
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('remitApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionAudit()
	{
		if (isset($_POST['RemitApplyForm'])) {
			$model = new RemitApplyForm($_POST['RemitApplyForm']['scenario']);
			$model->attributes = $_POST['RemitApplyForm'];
            $model->status_type=2;
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('remitApply/edit',array('index'=>$model->id)));
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
		$model = new RemitApplyForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new RemitApplyForm('new');
        ExpenseFun::setModelEmployee($model,"employee_id");
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new RemitApplyForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new RemitApplyForm('delete');
		if (isset($_POST['RemitApplyForm'])) {
			$model->attributes = $_POST['RemitApplyForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('remitApply/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}

    public function actionFileupload($doctype) {
        $model = new RemitApplyForm();
        if (isset($_POST['RemitApplyForm'])) {
            $model->attributes = $_POST['RemitApplyForm'];

            $id = ($_POST['RemitApplyForm']['scenario']=='new') ? 0 : $model->id;
            $docman = new DocMan($model->docType,$id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
            $docman->fileUpload();
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileRemove($doctype) {
        $model = new RemitApplyForm();
        if (isset($_POST['RemitApplyForm'])) {
            $model->attributes = $_POST['RemitApplyForm'];

            $docman = new DocMan($model->docType,$model->id,'RemitApplyForm');
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id from acc_expense where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'RemitApplyForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('RT01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('RT01');
	}
}
