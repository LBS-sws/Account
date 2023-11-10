<?php

class ConsultSearchController extends Controller
{
	public $function_id='CF04';
	
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
				'actions'=>array('edit','back'),
				'expression'=>array('ConsultSearchController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','listfile'),
				'expression'=>array('ConsultSearchController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ConsultSearchList();
        if (isset($_POST['ConsultSearchList'])) {
            $model->attributes = $_POST['ConsultSearchList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['consultSearch_c01']) && !empty($session['consultSearch_c01'])) {
                $criteria = $session['consultSearch_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionBack()
	{
		if (isset($_POST['ConsultSearchForm'])) {
			$model = new ConsultSearchForm($_POST['ConsultSearchForm']['scenario']);
			$model->attributes = $_POST['ConsultSearchForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('consultSearch/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('consultSearch/edit',array("index"=>$model->id)));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ConsultSearchForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ConsultSearchForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select city from acc_consult where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ConsultSearchForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public function actionListfile($docId) {
        $d = new DocMan('CONSU',$docId,'ConsultSearchForm');
        echo $d->genFileListView();
    }

    public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CF04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CF04');
	}
}
