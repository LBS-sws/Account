<?php

class ExpenseApplyController extends Controller
{
	public $function_id='DE01';
	
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
				'actions'=>array('new','edit','delete','save','audit'),
				'expression'=>array('ExpenseApplyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ExpenseApplyController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpenseApplyList();
		if (isset($_POST['ExpenseApplyList'])) {
			$model->attributes = $_POST['ExpenseApplyList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expenseApply_c01']) && !empty($session['expenseApply_c01'])) {
				$criteria = $session['expenseApply_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['ExpenseApplyForm'])) {
			$model = new ExpenseApplyForm($_POST['ExpenseApplyForm']['scenario']);
			$model->attributes = $_POST['ExpenseApplyForm'];
			if ($model->validate()) {
			    $model->status_type=0;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('expenseApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionAudit()
	{
		if (isset($_POST['ExpenseApplyForm'])) {
			$model = new ExpenseApplyForm($_POST['ExpenseApplyForm']['scenario']);
			$model->attributes = $_POST['ExpenseApplyForm'];
			if ($model->validate()) {
			    $model->status_type=1;
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('expenseApply/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ExpenseApplyForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new ExpenseApplyForm('new');
		ExpenseApplyForm::setModelEmployee($model,"employee_id");
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new ExpenseApplyForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ExpenseApplyForm('delete');
		if (isset($_POST['ExpenseApplyForm'])) {
			$model->attributes = $_POST['ExpenseApplyForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('expenseApply/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE01');
	}
}
