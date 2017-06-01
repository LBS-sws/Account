<?php

class TransenqController extends Controller 
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
				'actions'=>array('index','index2','view','listfile','filedownload','viewdetail'),
				'expression'=>array('TransenqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new TransEnq1List;
		if (isset($_POST['TransEnq1List'])) {
			$model->attributes = $_POST['TransEnq1List'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe02_1']) && !empty($session['criteria_xe02_1'])) {
				$criteria = $session['criteria_xe02_1'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionIndex2($index,$city,$pageNum=0)
	{
		$model = new TransEnq2List;
		if (isset($_POST['TransEnq2List'])) {
			$model->attributes = $_POST['TransEnq2List'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xe02_2']) && !empty($session['criteria_xe02_2'])) {
				$criteria = $session['criteria_xe02_2'];
				$model->setCriteria($criteria);
			}
		}
		$model->retrieveHeaderData($index,$city);
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index2',array('model'=>$model));
	}
	
	public function actionViewDetail($index,$type) {
		if ($type=='IN') {
			$model = new TransInForm;
			$view = 'viewi';
		} else {
			$model = new TransOutForm;
			$view = 'viewo';
		}
		$model->retrieveData($index);
		$this->renderPartial($view, array('model'=>$model));
	}
	
	public function actionListfile($docId) {
		$d = new DocMan('TRANS',$docId,'TransEnq2List');
		echo $d->genFileListView();
	}
	
	public function actionFileDownload($docId, $fileId) {
		$sql = "select city from acc_trans where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan('TRANS', $docId,'transenq');
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
			throw new CHttpException(404,'Record not found.');
		}
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='enquiry-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XE02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XE02');
	}
}
