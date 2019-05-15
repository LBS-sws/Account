<?php
class ImportController extends Controller
{
	public $function_id='XF02';

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
				'actions'=>array('loadfile','submit','index','activate'),
				'expression'=>array('ImportController','allowExecute'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$model = new ImportForm();
		$this->render('form',array('model'=>$model,));
	}

	public function actionSubmit() {
		$model = new ImportForm();
		if (isset($_POST['ImportForm'])) {
			$model->attributes = $_POST['ImportForm'];
			if ($model->validate()) {
				if ($file = CUploadedFile::getInstance($model,'import_file')) {
					$model->file_type = $file->extensionName;
					$content = file_get_contents($file->tempName);
					$model->file_content = $content; //base64_encode($content);
					$qid = $model->addItemToQueue();
					$model->queue_id = $qid;
					$model->setMapping();
					$model->activateQueueItem();
					Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Job submitted. Please go to Import Manager to retrieve the result.'));
				} else {
					$message = Yii::t('import','Upload file error');
					Dialog::message(Yii::t('dialog','Error Message'), $message);
				}		
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
		}
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionLoadFile() {
		$rtn = '';
		$model = new ImportForm();
		if (isset($_POST['ImportForm'])) {
			$model->attributes = $_POST['ImportForm'];
			if ($file = CUploadedFile::getInstance($model,'import_file')) {
				$model->file_type = $file->extensionName;
				$content = file_get_contents($file->tempName);
				$model->file_content = $content;	//base64_encode($content);

				$readerType = strtolower($file->extensionName)=='xlsx' ? 'Excel2007' : 'Excel5';
				$filename = $file->tempName;

				$excel = new ExcelTool();
				$excel->start();
		
				$excel->readFileByType($filename, $readerType);
				$i = 0;
				$fileFields = array();
				$ws = $excel->setActiveSheet(0);
				do {
					$fldname = $excel->getCellValue($excel->getColumn($i),1); 
					if (!empty($fldname)) $fileFields[$i] = $fldname;
					$i++;
				} while (!empty($fldname));

				$excel->end();
				
				$qid = $model->addItemToQueue();
				$rtn = $model->genMappingList($fileFields, $qid);
			}
		}
		echo $rtn;	
	}
	
	public function actionActivate() {
		$model = new ImportForm();
		if (isset($_POST['ImportForm'])) {
			$model->attributes = $_POST['ImportForm'];
			if ($model->validate()) {
				$model->activateQueueItem();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Submit Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
		}
		$this->render('form',array('model'=>$model,));
	}
	
	public static function allowExecute() {
		return Yii::app()->user->validFunction('XF02');
	}
}
?>
