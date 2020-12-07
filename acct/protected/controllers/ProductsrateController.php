<?php

class ProductsrateController extends Controller
{
	public $function_id='XS08';
	
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
				'expression'=>array('ProductsrateController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ProductsrateController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ProductsrateList;
		if (isset($_POST['ProductsrateList'])) {
			$model->attributes = $_POST['ProductsrateList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xs08']) && !empty($session['criteria_xs08'])) {
				$criteria = $session['criteria_xs08'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['ProductsrateForm'])) {
			$model = new ProductsrateForm($_POST['ProductsrateForm']['scenario']);
			$model->attributes = $_POST['ProductsrateForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('productsrate/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ProductsrateForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0,$copy=0)
	{
		$model = new ProductsrateForm('new');
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
		$model = new ProductsrateForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ProductsrateForm('delete');
		if (isset($_POST['ProductsrateForm'])) {
			$model->attributes = $_POST['ProductsrateForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('productsrate/index'));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS08');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS08');
	}
}
