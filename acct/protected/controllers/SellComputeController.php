<?php

class 
SellComputeController extends Controller
{
	private $con_bool=false;

	public $function_id='XS01';

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
                'actions'=>array('listSave','listClear'),
                'expression'=>array('SellComputeController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','list','downAll'),
				'expression'=>array('SellComputeController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SellComputeList();
		if (isset($_POST['SellComputeList'])) {
			$model->attributes = $_POST['SellComputeList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['sellCompute_c01']) && !empty($session['sellCompute_c01'])) {
				$criteria = $session['sellCompute_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$this->con_bool);
		$this->render('index',array('model'=>$model));
	}

    public function actionView($index)
    {
        $model = new SellComputeForm('view');
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionList($index,$type='')
    {
        $model = new SellComputeForm($type);
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('list_view',array('model'=>$model,'type'=>$type));
        }
    }

    public function actionListSave($index,$type='')
    {
        $model = new SellComputeForm($type);
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->listSave();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('sellCompute/list',array('index'=>$index,'type'=>$type)));
        }
    }

    public function actionListClear($index,$type='')
    {
        $model = new SellComputeForm($type);
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->listSave('clear');
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('sellCompute/list',array('index'=>$index,'type'=>$type)));
        }
    }

    public function actionDownAll()
    {
        $down_id = key_exists("down_id",$_POST)?$_POST["down_id"]:"";
        $idList = explode(",",$down_id);
        $model = new SellComputeForm('edit');
        if(!empty($idList)){
            $model->downExcelAll($idList);
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'), "列表为空，无法下载");
            $this->redirect(Yii::app()->createUrl('sellTable/index'));
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS01');
	}
}
