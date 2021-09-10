<?php

class SrateController extends Controller 
{
	public $function_id='XS03';
	
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
				'actions'=>array('new','edit','delete','save'),
				'expression'=>array('SrateController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('SrateController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SRateList;
		if (isset($_POST['SRateList'])) {
			$model->attributes = $_POST['SRateList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xg01']) && !empty($session['criteria_xg01'])) {
				$criteria = $session['criteria_xg01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['SRateForm'])) {
			$model = new SRateForm($_POST['SRateForm']['scenario']);
			$model->attributes = $_POST['SRateForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('srate/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new SRateForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
        $index = is_numeric($index)?$index:0;
		$model = new SRateForm('new');
        if($index>0){
            $model->retrieveData($index);
            $model->id = null;
        }
		$model->city = Yii::app()->user->city();
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new SRateForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new SRateForm('delete');
		if (isset($_POST['SRateForm'])) {
			$model->attributes = $_POST['SRateForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('srate/index'));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS03');
	}
}
