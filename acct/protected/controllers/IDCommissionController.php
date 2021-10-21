<?php

class IDCommissionController extends Controller
{
	public $function_id='XS11';
	public $year;
	public $month;
	public $type=0;//0:查询  1：计算

    public function init()
    {
        parent::init();
        $this->year = key_exists("year",$_GET)&&is_numeric($_GET["year"])?$_GET["year"]:date("Y");
        $this->month = key_exists("month",$_GET)&&is_numeric($_GET["month"])?$_GET["month"]:intval(date("m"));
        $this->type = key_exists("type",$_GET)&&is_numeric($_GET["type"])?$_GET["type"]:0;
        $this->function_id = $this->type==1?"XS10":"XS11";
    }
	
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
        //'New:新增','Renew:续约','Resume:恢复','Amend:更改','Suspend:暂停','Terminate:终止'
		return array(
			array('allow', 
				'actions'=>array('new','Renew','Amend'),
				'expression'=>array('IDCommissionController','allowReadWrite'),
			),
            array('allow',//超過兩個月後，不允許計算
                'actions'=>array('newsave','amendsave','renewSave'),
                'expression'=>array('IDCommissionController','allowEditDate'),
            ),
			array('allow', 
				'actions'=>array('index','index_s','view','downs'),
				'expression'=>array('IDCommissionController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
        $model = new IDCommissionForm();
        $model->year = $this->year;
        $model->month = $this->month;
        $model->city = Yii::app()->user->city();
        $this->render('index',array('model'=>$model));
	}


    public function actionIndex_s($pageNum=0)
    {
        $model = new IDCommissionList();
        if (isset($_POST['IDCommissionForm'])) {
            $model->attributes = $_POST['IDCommissionForm'];
            $model->YearAndMonthMinus();
        } else {
            if (isset($_POST['IDCommissionList'])) {
                $model->attributes = $_POST['IDCommissionList'];
            } else {
                $session = Yii::app()->session;
                if (isset($session['IDCommission_01']) && !empty($session['IDCommission_01'])) {
                    $criteria = $session['IDCommission_01'];
                    $model->setCriteria($criteria);
                }
            }
            $model->year = $this->year;
            $model->month = $this->month;
            $model->city = key_exists("city",$_GET)?$_GET["city"]:Yii::app()->user->city();
        }
        $model->type = $this->type;
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);

        $this->render('index_s',array('model'=>$model));
    }

    public function actionView($index,$year=2020,$month=1,$employee_id=0)
    {
        $model = new IDCommissionForm('view');
        $model->year = $year;
        $model->month = $month;
        $model->type = $this->type;
        $model->employee_id = $employee_id;
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('view',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
        }
    }

    //新增生意额
    public function actionNew($pageNum=0,$index)
    {
        $model = new IDCommissionList;
        if (isset($_POST['IDCommissionList'])) {
            $model->attributes = $_POST['IDCommissionList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['IDCommission_new']) && !empty($session['IDCommission_new'])) {
                $criteria = $session['IDCommission_new'];
                $model->setCriteria($criteria);
            }
        }
        $model->type = $this->type;
        $model->determinePageNum($pageNum);
        $model->newDataByPage($model->pageNum,$index);
        $this->render('new',array('model'=>$model));
    }

    //更改生意额
    public function actionAmend($pageNum=0,$index)
    {
        $model = new IDCommissionList;
        if (isset($_POST['IDCommissionList'])) {
            $model->attributes = $_POST['IDCommissionList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['IDCommission_amend']) && !empty($session['IDCommission_amend'])) {
                $criteria = $session['IDCommission_amend'];
                $model->setCriteria($criteria);
            }
        }
        $model->type = $this->type;
        $model->determinePageNum($pageNum);
        $model->amendDataByPage($model->pageNum,$index);
        $this->render('amend',array('model'=>$model));
    }

    //续约生意额
    public function actionRenew($pageNum=0,$index)
    {
        $model = new IDCommissionList;
        if (isset($_POST['IDCommissionList'])) {
            $model->attributes = $_POST['IDCommissionList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['IDCommission_renew']) && !empty($session['IDCommission_renew'])) {
                $criteria = $session['IDCommission_renew'];
                $model->setCriteria($criteria);
            }
        }
        $model->type = $this->type;
        $model->determinePageNum($pageNum);
        $model->renewDataByPage($model->pageNum,$index);
        $this->render('renew',array('model'=>$model));
    }

    //计算新增
    public function actionNewsave($index){
        $model = new IDCommissionBox('save');
        if (isset($_POST['IDCommissionBox'])) {
            $model->attributes = $_POST['IDCommissionBox'];
            if($model->validate()){
                $model->newSave();
                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
        }
        $this->redirect(Yii::app()->createUrl('IDCommission/new',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month)));
    }

    //计算更改
    public function actionAmendSave($index){
        $model = new IDCommissionBox('save');
        if (isset($_POST['IDCommissionBox'])) {
            $model->attributes = $_POST['IDCommissionBox'];
            if($model->validate()){
                $model->AmendSave();
                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
        }
        $this->redirect(Yii::app()->createUrl('IDCommission/amend',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month)));
    }

    //计算续约
    public function actionRenewSave($index){
        $model = new IDCommissionBox('save');
        if (isset($_POST['IDCommissionBox'])) {
            $model->attributes = $_POST['IDCommissionBox'];
            if($model->validate()){
                $model->RenewSave();
                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
        }
        $this->redirect(Yii::app()->createUrl('IDCommission/renew',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month)));
    }

	public function actionDowns($index)
	{
        $model = new IDCommissionForm('view');
        $model->retrieveData($index,true);
        $list = new IDCommissionList('view');
        $list->retrieveXiaZai($index,$model);
	}

	public static function allowReadWrite() {
        $type = key_exists("type",$_GET)&&is_numeric($_GET["type"])?$_GET["type"]:0;
        $function_id = $type==1?"XS10":"XS11";
		return Yii::app()->user->validRWFunction($function_id);
	}
	
	public static function allowEditDate() {
        $year = key_exists("year",$_GET)&&is_numeric($_GET["year"])?$_GET["year"]:0;
        $month = key_exists("month",$_GET)&&is_numeric($_GET["month"])?$_GET["month"]:0;
        $type = key_exists("type",$_GET)&&is_numeric($_GET["type"])?$_GET["type"]:0;
        $function_id = $type==1?"XS10":"XS11";
        if(date("Y/m/d",strtotime("$year-$month-01"))>=date("Y/m/01",strtotime("-1 months"))){
            return Yii::app()->user->validRWFunction($function_id);
        }else{
            return false;
        }
	}

	public static function allowReadOnly() {
        $type = key_exists("type",$_GET)&&is_numeric($_GET["type"])?$_GET["type"]:0;
        $function_id = $type==1?"XS10":"XS11";
		return Yii::app()->user->validFunction($function_id);
	}
}
