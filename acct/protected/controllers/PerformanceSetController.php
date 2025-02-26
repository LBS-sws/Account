<?php

class PerformanceSetController extends Controller
{
	public $function_id='XS13';
	
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
				'expression'=>array('PerformanceSetController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('PerformanceSetController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PerformanceSetList;
		if (isset($_POST['PerformanceSetList'])) {
			$model->attributes = $_POST['PerformanceSetList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['performanceSet_xs08']) && !empty($session['performanceSet_xs08'])) {
				$criteria = $session['performanceSet_xs08'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['PerformanceSetForm'])) {
			$model = new PerformanceSetForm($_POST['PerformanceSetForm']['scenario']);
			$model->attributes = $_POST['PerformanceSetForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('performanceSet/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PerformanceSetForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0,$copy=0)
	{
		$model = new PerformanceSetForm('new');
		$model->city = Yii::app()->user->city();
        if ($index!==0 && $model->retrieveData($index)) {
            $model->id = 0;
            $model->copy = $copy;
            $model->start_dt = date('Y/m/d');
        }
//        print_r($model);
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new PerformanceSetForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new PerformanceSetForm('delete');
		if (isset($_POST['PerformanceSetForm'])) {
			$model->attributes = $_POST['PerformanceSetForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('performanceSet/index'));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS13');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS13');
	}
}
