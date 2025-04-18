<?php

class AppraisalController extends Controller
{
	public $function_id='XS14';
	
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
				'actions'=>array('edit','back','save','batchSave','batchBack'),
				'expression'=>array('AppraisalController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downFixed'),
				'expression'=>array('AppraisalController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new AppraisalList;
		if (isset($_POST['AppraisalList'])) {
			$model->attributes = $_POST['AppraisalList'];
		} else {
			$session = Yii::app()->session;
            if (isset($session['appraisal_xs08']) && !empty($session['appraisal_xs08'])) {
                $criteria = $session['appraisal_xs08'];
                $model->setCriteria($criteria);
            }
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

    public function actionDownFixed()
    {
        $model = new AppraisalForm("edit");
        $model->downFixed();
    }

    public function actionBatchSave()
    {
        $model = new AppraisalForm("edit");
        if(!empty($_POST['checkList'])){
            //ini_set('memory_limit','500M');
            $checkList = $_POST['checkList'];
            $checkList = explode(",",$checkList);

            $arr = $model->batchSave($checkList);
            if($arr["bool"]){
                Dialog::message(Yii::t('dialog','Information'), "已批量固定");
            }else{
                Dialog::message("北森验证异常", $arr["message"]);
            }
        }else{
            Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','No Record Found'));
        }
        $this->redirect(Yii::app()->createUrl('appraisal/index'));
    }

    public function actionBatchBack()
    {
        $model = new AppraisalForm("back");
        if(!empty($_POST['checkList'])){
            //ini_set('memory_limit','500M');
            $checkList = $_POST['checkList'];
            $checkList = explode(",",$checkList);

            $arr = $model->batchBack($checkList);
            if($arr["bool"]){
                Dialog::message(Yii::t('dialog','Information'), "已批量取消");
            }else{
                Dialog::message("北森验证异常", $arr["message"]);
            }
        }else{
            Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','No Record Found'));
        }
        $this->redirect(Yii::app()->createUrl('appraisal/index'));
    }


	public function actionSave()
	{
		if (isset($_POST['AppraisalForm'])) {
			$model = new AppraisalForm($_POST['AppraisalForm']['scenario']);
			$model->attributes = $_POST['AppraisalForm'];
			if ($model->validate()) {
			    $model->status_type=1;
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), "销售顾问绩效考核已固定");
                }else{
                    Dialog::message("北森验证异常", $arr["message"]);
                }
				$this->redirect(Yii::app()->createUrl('appraisal/edit',array('index'=>$model->employee_id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new AppraisalForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new AppraisalForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionBack()
	{
		$model = new AppraisalForm('back');
		if (isset($_POST['AppraisalForm'])) {
			$model->attributes = $_POST['AppraisalForm'];
			$model->status_type=0;
			$model->saveData();
            Dialog::message(Yii::t('dialog','Information'), "季度奖金已取消固定");
            $this->redirect(Yii::app()->createUrl('appraisal/edit',array('index'=>$model->employee_id)));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS14');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS14');
	}
}
