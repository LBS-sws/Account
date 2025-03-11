<?php

class PerformanceBonusController extends Controller
{
	public $function_id='XS12';
	
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
				'actions'=>array('edit','back','save','batchSave'),
				'expression'=>array('PerformanceBonusController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downFixed'),
				'expression'=>array('PerformanceBonusController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PerformanceBonusList;
		if (isset($_POST['PerformanceBonusList'])) {
			$model->attributes = $_POST['PerformanceBonusList'];
		} else {
			$session = Yii::app()->session;
            if (isset($session['performanceBonus_xs08']) && !empty($session['performanceBonus_xs08'])) {
                $criteria = $session['performanceBonus_xs08'];
                $model->setCriteria($criteria);
            }
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

    public function actionDownFixed()
    {
        $model = new PerformanceBonusForm("edit");
        $model->downFixed();
    }

    public function actionBatchSave()
    {
        $model = new PerformanceBonusForm("edit");
        if(!empty($_POST['checkList'])){
            //ini_set('memory_limit','500M');
            $checkList = $_POST['checkList'];
            $checkList = explode(",",$checkList);
            $model->batchSave($checkList);
            Dialog::message(Yii::t('dialog','Information'), "已批量固定");
        }else{
            Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','No Record Found'));
        }
        $this->redirect(Yii::app()->createUrl('performanceBonus/index'));
    }

	public function actionSave()
	{
		if (isset($_POST['PerformanceBonusForm'])) {
			$model = new PerformanceBonusForm($_POST['PerformanceBonusForm']['scenario']);
			$model->attributes = $_POST['PerformanceBonusForm'];
			if ($model->validate()) {
			    $model->status_type=1;
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), "季度奖金已固定");
				$this->redirect(Yii::app()->createUrl('performanceBonus/edit',array('index'=>$model->employee_id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PerformanceBonusForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new PerformanceBonusForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionBack()
	{
		$model = new PerformanceBonusForm('back');
		if (isset($_POST['PerformanceBonusForm'])) {
			$model->attributes = $_POST['PerformanceBonusForm'];
			$model->status_type=0;
			$model->saveData();
            Dialog::message(Yii::t('dialog','Information'), "季度奖金已取消固定");
            $this->redirect(Yii::app()->createUrl('performanceBonus/edit',array('index'=>$model->employee_id)));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS12');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS12');
	}
}
