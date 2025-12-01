<?php

class 
SalesGroupSetController extends Controller
{
	public $function_id='SG02';
	
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
				'expression'=>array('SalesGroupSetController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('SalesGroupSetController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SalesGroupSetList();
		if (isset($_POST['SalesGroupSetList'])) {
			$model->attributes = $_POST['SalesGroupSetList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['salesGroupSet_c01']) && !empty($session['salesGroupSet_c01'])) {
				$criteria = $session['salesGroupSet_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['SalesGroupSetForm'])) {
			$model = new SalesGroupSetForm($_POST['SalesGroupSetForm']['scenario']);
			$model->attributes = $_POST['SalesGroupSetForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('salesGroupSet/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new SalesGroupSetForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
        $index = is_numeric($index)?$index:0;
		$model = new SalesGroupSetForm('new');
        if($index>0){
            $model->retrieveData($index);
            $model->id = null;
            $model->start_date = date("Y/m/d");
            $model->end_date = null;
        }
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new SalesGroupSetForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new SalesGroupSetForm('delete');
		if (isset($_POST['SalesGroupSetForm'])) {
			$model->attributes = $_POST['SalesGroupSetForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('salesGroupSet/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SG02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SG02');
	}
}
