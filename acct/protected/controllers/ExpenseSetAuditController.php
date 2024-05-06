<?php

class ExpenseSetAuditController extends Controller 
{
	public $function_id='DE06';

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
				'actions'=>array('new','edit','delete','save','copySet'),
				'expression'=>array('ExpenseSetAuditController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ExpenseSetAuditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpenseSetAuditList;
		if (isset($_POST['ExpenseSetAuditList'])) {
			$model->attributes = $_POST['ExpenseSetAuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expenseSetAudit_c01']) && !empty($session['expenseSetAudit_c01'])) {
				$criteria = $session['expenseSetAudit_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['ExpenseSetAuditForm'])) {
			$model = new ExpenseSetAuditForm($_POST['ExpenseSetAuditForm']['scenario']);
			$model->attributes = $_POST['ExpenseSetAuditForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('expenseSetAudit/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ExpenseSetAuditForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new ExpenseSetAuditForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new ExpenseSetAuditForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ExpenseSetAuditForm('delete');
		if (isset($_POST['ExpenseSetAuditForm'])) {
			$model->attributes = $_POST['ExpenseSetAuditForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), "该账户存在待审核的加班、请假单，无法删除");
				$this->redirect(Yii::app()->createUrl('expenseSetAudit/edit',array('index'=>$model->id)));
			} else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('expenseSetAudit/index'));
			}
		}
	}

	public function actionCopySet($index)
	{
		$model = new ExpenseSetAuditForm('new');
        if (!$model->copyData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE06');
	}
}
