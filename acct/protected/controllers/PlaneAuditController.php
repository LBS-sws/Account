<?php

class 
PlaneAuditController extends Controller
{
	public $function_id='PS07';
	
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
                'actions'=>array('edit','finish','reject'),
                'expression'=>array('PlaneAuditController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
				'expression'=>array('PlaneAuditController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PlaneAuditList();
		if (isset($_POST['PlaneAuditList'])) {
			$model->attributes = $_POST['PlaneAuditList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['planeAudit_c01']) && !empty($session['planeAudit_c01'])) {
				$criteria = $session['planeAudit_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

    public function actionFinish()
    {
        if (isset($_POST['PlaneAuditForm'])) {
            $model = new PlaneAuditForm('finish');
            $model->attributes = $_POST['PlaneAuditForm'];
            if ($model->validate()) {
                $model->plane_status=2;
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    $this->redirect(Yii::app()->createUrl('planeAudit/index'));
                }else{
                    Dialog::message("北森验证异常", $arr["message"]);
                    $this->redirect(Yii::app()->createUrl('planeAudit/edit',array("index"=>$model->id)));
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('planeAudit/edit',array("index"=>$model->id)));
            }
        }
    }

    public function actionReject()
    {
        if (isset($_POST['PlaneAuditForm'])) {
            $model = new PlaneAuditForm('reject');
            $model->attributes = $_POST['PlaneAuditForm'];
            if ($model->validate()) {
                $model->plane_status=3;
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    $this->redirect(Yii::app()->createUrl('planeAudit/index'));
                }else{
                    Dialog::message("北森验证异常", $arr["message"]);
                    $this->redirect(Yii::app()->createUrl('planeAudit/edit',array("index"=>$model->id)));
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('planeAudit/edit',array("index"=>$model->id)));
            }
        }
    }

    public function actionView($index)
    {
        $model = new PlaneAuditForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionEdit($index)
    {
        $model = new PlaneAuditForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('PS07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('PS07');
	}
}
