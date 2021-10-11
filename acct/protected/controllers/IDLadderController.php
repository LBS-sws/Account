<?php

class IDLadderController extends Controller
{
	public $function_id='XS09';
	
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
				'expression'=>array('IDLadderController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('IDLadderController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new IDLadderList;
		if (isset($_POST['IDLadderList'])) {
			$model->attributes = $_POST['IDLadderList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['IDLadderList_xg01']) && !empty($session['IDLadderList_xg01'])) {
				$criteria = $session['cIDLadderList_xg01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['IDLadderForm'])) {
			$model = new IDLadderForm($_POST['IDLadderForm']['scenario']);
			$model->attributes = $_POST['IDLadderForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('IDLadder/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new IDLadderForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
        $index = is_numeric($index)?$index:0;
		$model = new IDLadderForm('new');
        if($index>0){
            $model->retrieveData($index);
            $model->id = null;
        }
		$model->city = Yii::app()->user->city();
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new IDLadderForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new IDLadderForm('delete');
		if (isset($_POST['IDLadderForm'])) {
			$model->attributes = $_POST['IDLadderForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('IDLadder/index'));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS09');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS09');
	}
}
