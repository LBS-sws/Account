<?php

class 
SellTableController extends Controller
{
	public $function_id='XS07';

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
                'actions'=>array('audit','ject','break'),
                'expression'=>array('SellTableController','allowAudit'),
            ),
            array('allow',
                'actions'=>array('edit','save','examine'),
                'expression'=>array('SellTableController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','down','downAll','sendAllBS'),
				'expression'=>array('SellTableController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SellTableList();
		if (isset($_POST['SellTableList'])) {
			$model->attributes = $_POST['SellTableList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['sellTable_c01']) && !empty($session['sellTable_c01'])) {
				$criteria = $session['sellTable_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

    public function actionSendAllBS($year=2025,$month=2)
    {
        $model = new SellTableForm('view');
        $model->sendAllBS($year,$month);
    }

    public function actionView($index)
    {
        $model = new SellTableForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionEdit($index)
    {
        $model = new SellTableForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionDown($index)
    {
        $model = new SellTableForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->downExcel();
        }
    }

    public function actionDownAll()
    {
        $down_id = key_exists("down_id",$_POST)?$_POST["down_id"]:"";
        $idList = explode(",",$down_id);
        $model = new SellTableForm('edit');
        if(!empty($idList)){
            $model->downExcelAll($idList);
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'), "列表为空，无法下载");
            $this->redirect(Yii::app()->createUrl('sellTable/index'));
        }
    }

    public function actionSave()
    {
        if (isset($_POST['SellTableForm'])) {
            $model = new SellTableForm("save");
            $model->attributes = $_POST['SellTableForm'];
            if ($model->validate()) {
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                }else{
                    Dialog::message(Yii::t('dialog','Validation Message'), $arr["message"]);
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('sellTable/edit',array('index'=>$model->id)));
        }
    }

    public function actionExamine()
    {
        if (isset($_POST['SellTableForm'])) {
            $model = new SellTableForm("examine");
            $model->attributes = $_POST['SellTableForm'];
            if ($model->validate()) {
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                }else{
                    Dialog::message(Yii::t('dialog','Validation Message'), $arr["message"]);
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('sellTable/edit',array('index'=>$model->id)));
        }
    }

    public function actionAudit()
    {
        if (isset($_POST['SellTableForm'])) {
            $model = new SellTableForm("audit");
            //$model->setAttributes($_POST['SellTableForm']);
            $model->attributes = $_POST['SellTableForm'];
            if ($model->validate()) {
                $arr = $model->saveData();
                if($arr["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                }else{
                    Dialog::message("北森验证异常", $arr["message"]);
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('sellTable/edit',array('index'=>$model->id)));
        }
    }

    public function actionJect()
    {
        if (isset($_POST['SellTableForm'])) {
            $model = new SellTableForm("ject");
            $model->attributes = $_POST['SellTableForm'];
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('sellTable/edit',array('index'=>$model->id)));
        }
    }

    public function actionBreak()
    {
        if (isset($_POST['SellTableForm'])) {
            $model = new SellTableForm("break");
            $model->attributes = $_POST['SellTableForm'];
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('sellTable/edit',array('index'=>$model->id)));
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS07');
	}

	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS07');
	}

	public static function allowAudit() {
		return Yii::app()->user->validFunction('CN12');
	}
}
