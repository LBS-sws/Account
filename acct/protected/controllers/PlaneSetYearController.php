<?php

class 
PlaneSetYearController extends Controller
{
	public $function_id='PS05';
	
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
				'expression'=>array('PlaneSetYearController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('PlaneSetYearController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PlaneSetYearList();
		if (isset($_POST['PlaneSetYearList'])) {
			$model->attributes = $_POST['PlaneSetYearList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['planeSetYear_c01']) && !empty($session['planeSetYear_c01'])) {
				$criteria = $session['planeSetYear_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['PlaneSetYearForm'])) {
			$model = new PlaneSetYearForm($_POST['PlaneSetYearForm']['scenario']);
			$model->attributes = $_POST['PlaneSetYearForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('planeSetYear/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PlaneSetYearForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
        $index = is_numeric($index)?$index:0;
		$model = new PlaneSetYearForm('new');
        if($index>0){
            $model->retrieveData($index);
            $model->id = null;
            $model->set_name = "";
            $model->start_date = date("Y/m/d");
        }
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new PlaneSetYearForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new PlaneSetYearForm('delete');
		if (isset($_POST['PlaneSetYearForm'])) {
			$model->attributes = $_POST['PlaneSetYearForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('planeSetYear/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('PS05');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('PS05');
	}
}
