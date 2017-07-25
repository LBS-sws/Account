<?php
class IqueueController extends Controller
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

	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('index','viewlog'),
				'expression'=>array('IqueueController','allowExecute'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) {
		$model = new IqueueList;
		if (isset($_POST['IqueueList'])) {
			$model->attributes = $_POST['IqueueList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xf01']) && !empty($session['criteria_xe01'])) {
				$criteria = $session['criteria_xf01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionViewlog($index) {
		$model = new IqueueList;
		echo $model->getLogContent($index);
	}
	
	public static function allowExecute() {
		return Yii::app()->user->validFunction('XF01');
	}
}
?>
