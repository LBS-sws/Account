<?php

class 
SellSearchController extends Controller
{
	private $con_bool=true;

	public $function_id='XS02';

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
                'actions'=>array('index','view','list','downAll'),
				'expression'=>array('SellSearchController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new SellComputeList();
        SellComputeList::onlySearch($model);//驗證是否只能查看自己的提成
		if (isset($_POST['SellComputeList'])) {
			$model->attributes = $_POST['SellComputeList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
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
        SellComputeList::onlySearch($model);//驗證是否只能查看自己的提成
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionList($index,$type='')
    {
        $model = new SellComputeForm($type);
        SellComputeList::onlySearch($model);//驗證是否只能查看自己的提成
        if (!$model->retrieveData($index,$this->con_bool)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('list_view',array('model'=>$model,'type'=>$type));
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
            $this->redirect(Yii::app()->createUrl('sellSearch/index'));
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS02');
	}
}
