<?php
class ReportController extends Controller
{
	protected static $actions = array(
						'reimburse'=>'XB02',
						'translist'=>'XB03',
					);
	
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules() {
		$act = array();
		foreach ($this->action as $key=>$value) { $act[] = $key; }
		return array(
			array('allow', 
				'actions'=>$act,
				'expression'=>array('ReportController','allowExecute'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionReimburse() {
		$this->function_id = 'XB02';
		Yii::app()->session['active_func'] = $this->function_id;
		$model = new Report01Form;
		if (isset($_POST['Report01Form'])) {
			$model->attributes = $_POST['Report01Form'];
			if ($model->validate()) {
				$model->addQueueItem();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Report submitted. Please go to Report Manager to retrieve the output.'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
		}
		$this->render('form_reimb',array('model'=>$model));
	}

	public function actionTranslist() {
		$this->function_id = 'XB03';
		Yii::app()->session['active_func'] = $this->function_id;
		$model = new Report02Form;
		if (isset($_POST['Report02Form'])) {
			$model->attributes = $_POST['Report02Form'];
			if ($model->validate()) {
				$model->addQueueItem();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Report submitted. Please go to Report Manager to retrieve the output.'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
		}
		$this->render('form_trans',array('model'=>$model));
	}

	public static function allowExecute() {
		return Yii::app()->user->validFunction(self::$actions[Yii::app()->controller->action->id]);
	}
}
?>
