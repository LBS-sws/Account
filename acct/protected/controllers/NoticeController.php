<?php
class NoticeController extends Controller
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
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0, $type='') {
		$model = new NoticeList;
		if (isset($_POST['NoticeList'])) {
			$model->attributes = $_POST['NoticeList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_z101']) && !empty($session['criteria_z101'])) {
				$criteria = $session['criteria_z101'];
				$model->setCriteria($criteria);
			}
		}
		if (!empty($type)) {
			if ($type=='ACTN' || $type=='NOTI' || $type=='ALL') {
				$model->searchField = $type=='ALL' ? '' :'note_type';
				switch ($type) {
					case 'ACTN': $model->searchValue = Yii::t('queue','Action'); break;
					case 'NOTI': $model->searchValue = Yii::t('queue','Notify'); break;
					case 'ALL': $model->searchValue = ''; break;
				}
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionView($index) {
		$model = new NoticeForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
}
?>
