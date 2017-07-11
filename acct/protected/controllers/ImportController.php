<?php
class ImportController extends Controller
{
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
		return array(
			array('allow', 
				'actions'=>array('upload','submit'),
				'expression'=>array('ImportController','allowExecute'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionLoadFile() {
		if (isset($_POST['ImportForm'])) {
			$model = new ImportForm();
			$model->attributes = $_POST['ImportForm'];
			if ($model->validate()) {
				if ($file = CUploadedFile::getInstance($model,'import_file')) {
					$model->signature_file_type = $file->type;
					$content = file_get_contents($file->tempName);
					$model->signature = base64_encode($content);
				} else {
					$model->signature_file_type = '';
					$model->signature = '';
				}
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('user/edit',array('index'=>$model->username)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}
	
	public function actionReimburse() {
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
