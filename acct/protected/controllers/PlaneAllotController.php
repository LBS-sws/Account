<?php

class 
PlaneAllotController extends Controller
{
	public $function_id='PS02';
	
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
				'actions'=>array('allotMore','allotOne'),
				'expression'=>array('PlaneAllotController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index'),
				'expression'=>array('PlaneAllotController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PlaneAllotList();
		if (isset($_POST['PlaneAllotList'])) {
			$model->attributes = $_POST['PlaneAllotList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['planeAllot_c01']) && !empty($session['planeAllot_c01'])) {
				$criteria = $session['planeAllot_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionAllotMore(){
		$model = new PlaneAllotList();
        if (isset($_POST['PlaneAllotList'])) {
            $model->attributes = $_POST['PlaneAllotList'];
        }
        $allotList = key_exists("allot",$_POST)?$_POST["allot"]:array();
        $bool = $model->allotMore($allotList);
        if ($bool) {
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
		$this->redirect(Yii::app()->createUrl('planeAllot/index'));
	}

	public function actionAllotOne(){
		$model = new PlaneAllotList();
        if (isset($_POST['PlaneAllotList'])) {
            $model->attributes = $_POST['PlaneAllotList'];
        }
        $id = $_POST["allotOne"]["id"];
        $job_id = $_POST["allotOne"]["job_id"];
        $bool = $model->allotOne($id,$job_id);
        if ($bool) {
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
		$this->redirect(Yii::app()->createUrl('planeAllot/index'));
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('PS02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('PS02');
	}
}
