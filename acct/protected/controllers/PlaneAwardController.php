<?php

class 
PlaneAwardController extends Controller
{
	public $function_id='PS01';
	
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
                'actions'=>array('new','edit','delete','save','paste','ajaxPaste','pasteSave'),
                'expression'=>array('PlaneAwardController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','down'),
				'expression'=>array('PlaneAwardController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionPaste()
    {
        $model = new PlaneAwardForm('view');
        $model->plane_year=date("Y",strtotime("- 1 months"));
        $model->plane_month=date("n",strtotime("- 1 months"));
        $this->render('paste',array('model'=>$model,));
    }

    public function actionAjaxPaste() {
        $data = key_exists("list",$_POST)?$_POST["list"]:array();
        $year = key_exists("year",$_POST)?$_POST["year"]:2022;
        $month = key_exists("month",$_POST)?$_POST["month"]:12;
        $data = PlaneAwardForm::validateAjaxPayer($data,$year,$month);
        echo json_encode($data);
    }

    public function actionPasteSave() {
        $list = key_exists("test",$_POST)?$_POST["test"]:array();
        $paste = key_exists("PlaneAwardForm",$_POST)?$_POST["PlaneAwardForm"]:"";
        if(!empty($list)&&!empty($paste)){
            $model = new PlaneAwardForm('view');
            $model->pasteSave($list,$paste);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            $this->redirect(Yii::app()->createUrl('planeAward/index'));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'), "数据异常，保存失败");
            $this->redirect(Yii::app()->createUrl('planeAward/paste'));
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new PlaneAwardList();
		if (isset($_POST['PlaneAwardList'])) {
			$model->attributes = $_POST['PlaneAwardList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['planeAward_c01']) && !empty($session['planeAward_c01'])) {
				$criteria = $session['planeAward_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


    public function actionSave()
    {
        if (isset($_POST['PlaneAwardForm'])) {
            $model = new PlaneAwardForm($_POST['PlaneAwardForm']['scenario']);
            $model->attributes = $_POST['PlaneAwardForm'];
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
            $this->redirect(Yii::app()->createUrl('planeAward/edit',array("index"=>$model->id)));
        }
    }

    public function actionView($index)
    {
        $model = new PlaneAwardForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionDown()
    {
        $model = new PlaneAwardList();
        if (isset($_POST['PlaneAwardList'])) {
            $model->attributes = $_POST['PlaneAwardList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['planeAward_c01']) && !empty($session['planeAward_c01'])) {
                $criteria = $session['planeAward_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->downExcel();
    }

    public function actionEdit($index)
    {
        $model = new PlaneAwardForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionDelete()
    {
        $model = new PlaneAwardForm('delete');
        if (isset($_POST['PlaneAwardForm'])) {
            $model->attributes = $_POST['PlaneAwardForm'];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('planeAward/index'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('planeAward/edit',array("index"=>$model->id)));
            }
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('PS01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('PS01');
	}
}
