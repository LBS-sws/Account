<?php
class QueueController extends Controller
{
	public $function_id='XB01';

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
				'actions'=>array('index','view','download','downloadfile'),
				'expression'=>array('QueueController','allowExecute'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) {
		$model = new QueueList;
		if (isset($_POST['QueueList'])) {
			$model->attributes = $_POST['QueueList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xb01']) && !empty($session['criteria_xb01'])) {
				$criteria = $session['criteria_xb01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionView($index) {
		$uid = Yii::app()->user->id;

		$sql = "select rpt_content, rpt_type from acc_queue where id=:id and username=:uid";
		$queue = Queue::model()->findBySql($sql, array(':id'=>$index,':uid'=>$uid));
		if ($queue!==null) {
			$file = $queue->rpt_content;

			switch($queue->rpt_type) {
				case 'PDF': $ext = '.pdf'; $ctype = 'application/pdf'; break;
				case 'MTHRPT':
				case 'FEED':
//				case 'EXCEL': $ext = '.xlsx'; $ctype = 'application/vnd.ms-excel'; break;
				case 'EXCEL': $ext = '.xlsx'; $ctype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break;
				default: $ext = '.tmp'; $ctype = 'multipart/form-data';
			}
			
			$sql = "select param_value from acc_queue_param where queue_id=:qid and param_field='RPT_ID'";
			$qparam = QueueParam::model()->findBySql($sql, array(':qid'=>$index));
			$fname = ($qparam!==null) ? strtolower($qparam->param_value) : 'temp';

			$filename = $fname.$ext;
			header("Content-type:".$ctype); //for pdf or excel file
			//header('Content-Type:text/plain; charset=ISO-8859-15');
			header('Content-Disposition: attachment; filename="'.$filename.'"'); 
			header('Content-Length: ' . strlen($file));
			echo $file;
			Yii::app()->end();
		} else 
			Yii::app()->end();
	}

	public function actionDownload($index) {
		$url = Yii::app()->createAbsoluteUrl('queue/downloadfile',array('index'=>$index));
		$this->redirect(array('site/home','url'=>$url));
	}
	
	public function actionDownloadfile($index) {
		$uid = Yii::app()->user->id;

		$sql = "select a.rpt_content, a.rpt_type from acc_queue a where a.id=".$index." and (a.username='".$uid."'
				or exists(select b.username from acc_queue_user b where b.queue_id=a.id and b.username='".$uid."'))
			";
		$queue = Yii::app()->db->createCommand($sql)->queryAll();
		if (!empty($queue)) {
			foreach ($queue as $row) {
				$file = $row['rpt_content'];
				$type = $row['rpt_type'];
				break;
			}

			switch($type) {
				case 'PDF': $ext = '.pdf'; $ctype = 'application/pdf'; break;
				case 'MTHRPT':
				case 'FEED':
				case 'EXCEL': $ext = '.xlsx'; $ctype = 'application/vnd.ms-excel'; break;
				default: $ext = '.tmp'; $ctype = 'multipart/form-data';
			}
			
			$sql = "select param_value from acc_queue_param where queue_id=:qid and param_field='RPT_ID'";
			$qparam = QueueParam::model()->findBySql($sql, array(':qid'=>$index));
			$fname = ($qparam!==null) ? strtolower($qparam->param_value) : 'temp';

			$filename = $fname.$ext;
			header("Content-type:".$ctype); //for pdf or excel file
			//header('Content-Type:text/plain; charset=ISO-8859-15');
			header('Content-Disposition: attachment; filename="'.$filename.'"'); 
			header('Content-Length: ' . strlen($file));
			echo $file;
			Yii::app()->end();
		} else {
			throw new CHttpException(404,Yii::t('queue','Unable to download the file. Maybe access right problem or file link expired.'));
		}
	}

	public static function allowExecute() {
		Yii::app()->user->setUrlAfterLogin();
		return Yii::app()->user->validFunction('XB01');
	}

	public static function allowDownload() {
		$rtn = false;
		$uid = Yii::app()->user->id;
		$index = $_GET['index'];
		if (!empty($index) && !empty($uid)) {
			$sql = "select username from swo_queue where id=".$index;
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows)>0) {
				if ($rows['username']==$uid) {
					$rtn = true;
				} else {
					$sql = "select username from acc_queue_user where queue_id=".$index." and username='".$uid."'";
					$rows = Yii::app()->db->createCommand($sql)->queryAll();
					$rtn =(count($rows)>0);
				}
			}	
		}
		return (Yii::app()->user->validFunction('XB01') && $rtn);
	}
}
?>
