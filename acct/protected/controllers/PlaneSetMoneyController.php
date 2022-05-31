<?php

class 
PlaneSetMoneyController extends Controller
{
	public $function_id='PS03';
	
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
				'expression'=>array('PlaneSetMoneyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('PlaneSetMoneyController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PlaneSetMoneyList();
		if (isset($_POST['PlaneSetMoneyList'])) {
			$model->attributes = $_POST['PlaneSetMoneyList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['planeSetMoney_c01']) && !empty($session['planeSetMoney_c01'])) {
				$criteria = $session['planeSetMoney_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['PlaneSetMoneyForm'])) {
			$model = new PlaneSetMoneyForm($_POST['PlaneSetMoneyForm']['scenario']);
			$model->attributes = $_POST['PlaneSetMoneyForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('planeSetMoney/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PlaneSetMoneyForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
        $index = is_numeric($index)?$index:0;
		$model = new PlaneSetMoneyForm('new');
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
		$model = new PlaneSetMoneyForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new PlaneSetMoneyForm('delete');
		if (isset($_POST['PlaneSetMoneyForm'])) {
			$model->attributes = $_POST['PlaneSetMoneyForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('planeSetMoney/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('PS03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('PS03');
	}
}
