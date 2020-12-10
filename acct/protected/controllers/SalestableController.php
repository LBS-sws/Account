<?php

class SalestableController extends Controller
{
	public $function_id='XS07';

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
				'actions'=>array('new','edit','delete','save','fileupload','fileremove','examine','reject','audit'),
				'expression'=>array('SalestableController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('SalestableController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SalesTableList;
		if (isset($_POST['SalesTabeListl'])) {
			$model->attributes = $_POST['SalesTabeListl'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['SalesTableForm'])) {
			$model = new SalesTableForm($_POST['SalesTableForm']['scenario']);

			$model->attributes = $_POST['SalesTableForm'];
       //    print_r($model->attributes);exit();
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','SalesTableForm'), Yii::t('dialog','Save Done'));

				$this->redirect(Yii::app()->createUrl('salestable/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new SalesTableForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionExamine()
	{
        if (isset($_POST['SalesTableForm'])) {
            $model = new SalesTableForm($_POST['SalesTableForm']['scenario']);
            $model->attributes = $_POST['SalesTableForm'];
            //    print_r($model->attributes);exit();
            if ($model->validate()) {
                $model->saveExamine();
                Dialog::message(Yii::t('dialog','SalesTableForm'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('salestable/edit',array('index'=>$model->id)));
            } else {
                Dialog::message(Yii::t('dialog','SalesTableForm'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('salestable/edit',array('index'=>$model->id)));
            }
        }
	}
	
	public function actionEdit($index)
	{
		$model = new SalesTableForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionReject()
    {
        if (isset($_POST['SalesTableForm'])) {
            $model = new SalesTableForm('reject');
            $model->attributes = $_POST['SalesTableForm'];
            if ($model->validate()) {
                $model->saveReject();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Denied'));
                $this->redirect(Yii::app()->createUrl('salestable/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }
    public function actionAudit()
    {
        if (isset($_POST['SalesTableForm'])) {
            $model = new SalesTableForm('audit');
            $model->attributes = $_POST['SalesTableForm'];
//           / print_r(  $model->attributes);exit();
            if ($model->validate()) {
                $model->saveAudit();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('salestable/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }
	
//	public function actionDelete()
//	{
//		$model = new TransInForm('delete');
//		if (isset($_POST['TransInForm'])) {
//			$model->attributes = $_POST['TransInForm'];
//			$model->saveData();
//			Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Record Voided'));
//			$this->redirect(Yii::app()->createUrl('transin/edit',array('index'=>$model->id)));
//		}
//	}
	
//	public function actionFileupload($doctype) {
//		$model = new TransInForm();
//		if (isset($_POST['TransInForm'])) {
//			$model->attributes = $_POST['TransInForm'];
//
//			$id = ($_POST['TransInForm']['scenario']=='new') ? 0 : $model->id;
//			$docman = new DocMan($model->docType,$id,get_class($model));
//			$docman->masterId = $model->docMasterId[strtolower($doctype)];
//			if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
//			$docman->fileUpload();
//			echo $docman->genTableFileList(false);
//		} else {
//			echo "NIL";
//		}
//	}
//
//	public function actionFileRemove($doctype) {
//		$model = new TransInForm();
//		if (isset($_POST['TransInForm'])) {
//			$model->attributes = $_POST['TransInForm'];
//
//			$docman = new DocMan($model->docType,$model->id,'TransInForm');
//			$docman->masterId = $model->docMasterId[strtolower($doctype)];
//			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
//			echo $docman->genTableFileList(false);
//		} else {
//			echo "NIL";
//		}
//	}
	
//	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
//		$sql = "select city from acc_trans where id = $docId";
//		$row = Yii::app()->db->createCommand($sql)->queryRow();
//		if ($row!==false) {
//			$citylist = Yii::app()->user->city_allow();
//			if (strpos($citylist, $row['city']) !== false) {
//				$docman = new DocMan($doctype,$docId,'TransInForm');
//				$docman->masterId = $mastId;
//				$docman->fileDownload($fileId);
//			} else {
//				throw new CHttpException(404,'Access right not match.');
//			}
//		} else {
//				throw new CHttpException(404,'Record not found.');
//		}
//	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
//	protected function performAjaxValidation($model)
//	{
//		if(isset($_POST['ajax']) && $_POST['ajax']==='account-form')
//		{
//			echo CActiveForm::validate($model);
//			Yii::app()->end();
//		}
//	}


	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS07');
	}
}
