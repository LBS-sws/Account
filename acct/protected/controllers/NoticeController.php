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
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
		if (!empty($type)) {
			if ($type=='ACTN' || $type=='NOTI' || $type=='ALL') {
				$model->searchField = $type=='ALL' ? '' :'ready_bool';
				switch ($type) {
					case 'ACTN': $model->searchValue = "未执行"; break;
					case 'NOTI': $model->searchValue = "未读"; break;
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

	public function actionMarkread() {
		$model = new NoticeList;
		$session = Yii::app()->session;
		if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
			$criteria = $session[$model->criteriaName()];
			$model->setCriteria($criteria);
		}
		$model->markRead();
		Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Mark Read Done'));
		$this->redirect(Yii::app()->createUrl('notice/index'));
	}
}
?>
