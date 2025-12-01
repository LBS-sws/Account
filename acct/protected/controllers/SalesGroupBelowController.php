<?php

class SalesGroupBelowController extends Controller
{
	public $function_id='SG01';
	
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
				'actions'=>array('edit'),
				'expression'=>array('SalesGroupBelowController','allowReadWrite'),
			),
			array('allow',
				'actions'=>array('sendBsByOne'),
				'expression'=>array('SalesGroupBelowController','allowVivienne'),
			),
			array('allow', 
				'actions'=>array('index','view','downFixed'),
				'expression'=>array('SalesGroupBelowController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SalesGroupBelowList;
		if (isset($_POST['SalesGroupBelowList'])) {
			$model->attributes = $_POST['SalesGroupBelowList'];
		} else {
			$session = Yii::app()->session;
            if (isset($session['salesGroupBelow_xs08']) && !empty($session['salesGroupBelow_xs08'])) {
                $criteria = $session['salesGroupBelow_xs08'];
                $model->setCriteria($criteria);
            }
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

    public function actionDownFixed()
    {
        $model = new SalesGroupBelowForm("edit");
        $model->downFixed();
    }

    public function actionSendBsByOne($index)
    {
        $model = new SalesGroupBelowForm("edit");
        $bool = $model->sendBsByOne($index);
        if($bool){
            Dialog::message(Yii::t('dialog','Information'), "成功");
        }else{
            Dialog::message(Yii::t('dialog','Information'), "失败");
        }
        $this->redirect(Yii::app()->createUrl('salesGroupBelow/edit',array('index'=>$model->employee_id)));
    }

	public function actionView($index)
	{
		$model = new SalesGroupBelowForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new SalesGroupBelowForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SG01');
	}

	public static function allowVivienne() {
		return SellComputeForm::isVivienne();
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SG01');
	}
}
