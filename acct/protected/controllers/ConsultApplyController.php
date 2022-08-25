<?php

class ConsultApplyController extends Controller
{
	public $function_id='CF01';
	
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
				'actions'=>array('new','edit','delete','draft','send','fileupload','fileremove'),
				'expression'=>array('ConsultApplyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('ConsultApplyController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ConsultApplyList();
        if (isset($_POST['ConsultApplyList'])) {
            $model->attributes = $_POST['ConsultApplyList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['consultApply_c01']) && !empty($session['consultApply_c01'])) {
                $criteria = $session['consultApply_c01'];
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


	public function actionDraft()
	{
		if (isset($_POST['ConsultApplyForm'])) {
			$model = new ConsultApplyForm($_POST['ConsultApplyForm']['scenario']);
			$model->attributes = $_POST['ConsultApplyForm'];
			if ($model->validate()) {
                $model->status=0;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('consultApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSend()
	{
		if (isset($_POST['ConsultApplyForm'])) {
			$model = new ConsultApplyForm($_POST['ConsultApplyForm']['scenario']);
			$model->attributes = $_POST['ConsultApplyForm'];
			if ($model->validate()) {
                $model->status=1;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('consultApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ConsultApplyForm('view');
        if(ConsultApplyList::staffCompanyForUsername($model)){
            $model->staff_city = $model->apply_city;
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionNew()
	{
		$model = new ConsultApplyForm('new');
        if(ConsultApplyList::staffCompanyForUsername($model)){
            $model->staff_city = $model->apply_city;
            $this->render('form',array('model'=>$model,));
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ConsultApplyForm('edit');
        if(ConsultApplyList::staffCompanyForUsername($model)){
            $model->staff_city = $model->apply_city;
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionDelete()
	{
		$model = new ConsultApplyForm('delete');
		if (isset($_POST['ConsultApplyForm'])) {
			$model->attributes = $_POST['ConsultApplyForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('consultApply/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}

    public function actionFileupload($doctype) {
        $model = new ConsultApplyForm();
        if (isset($_POST['ConsultApplyForm'])) {
            $model->attributes = $_POST['ConsultApplyForm'];

            $id = ($_POST['ConsultApplyForm']['scenario']=='new') ? 0 : $model->id;
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
        $model = new ConsultApplyForm();
        if (isset($_POST['ConsultApplyForm'])) {
            $model->attributes = $_POST['ConsultApplyForm'];

            $docman = new DocMan($model->docType,$model->id,'ConsultApplyForm');
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select city from acc_consult where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ConsultApplyForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CF01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CF01');
	}
}
