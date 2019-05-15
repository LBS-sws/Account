<?php

class AjaxController extends Controller
{
	public $interactive = false;
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl - checksession', // perform access control for CRUD operations
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('dummy','remotelogin','remoteloginonlib','notify','notifybadge'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Lists all models.
	 */
	public function actionDummy()
	{
		Yii::app()->end();
	}

	public function actionRemotelogin() {
		$rtn = '';
		if (!Yii::app()->user->isGuest) {
			$id = Yii::app()->user->staffid(); 
			if (!empty($id)) {
				$suffix = Yii::app()->params['envSuffix'];
				$sql = "select code,code_old from hr$suffix.hr_employee where id=$id";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				if ($row !== false) {
					$staffcode = $row['code'];
					$staffocode = empty($row['code_old']) ? $row['code'] : $row['code_old'];
					$lang = Yii::app()->language;
					$lang = ($lang=='zh_cn' ? 'zhcn' : ($lang=='zh_tw' ? 'zhtw' : 'en'));
					$sesskey = Yii::app()->user->sessionkey();
					$salt = 'lbscorp168';
					$key = md5($staffcode.$salt.$sesskey.$staffocode);
					$temp = array(
							'id'=>$staffcode.':'.$staffocode,
							'sk'=>$sesskey,
							'ky'=>$key,
							'lang'=>$lang,
						);
					$rtn = json_encode($temp);
				}
			}
		}
		echo $rtn;
		Yii::app()->end();
	}

	public function actionRemoteloginonlib() {
		$rtn = '';
		if (!Yii::app()->user->isGuest) {
			$id = Yii::app()->user->id;
			if (!empty($id)) {
				$suffix = Yii::app()->params['envSuffix'];
				$sql = "select field_value from security$suffix.sec_user_info where username='$id' and 
						field_id='onlibuser'
					";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				if ($row !== false && !empty($row['field_value'])) {
					$temp = array(
						'id'=>$row['field_value'],
						'pwd'=>$row['field_value'].'$1688',
					);
					$rtn = json_encode($temp);
				}
			}
		}
		echo $rtn;
		Yii::app()->end();
	}
	
	public function actionChecksession() {
		$rtn = true;
		if (!Yii::app()->user->isGuest && Yii::app()->params['sessionIdleTime']!=='') {
			if (isset(Yii::app()->session['session_time'])) {
				$time = Yii::app()->session['session_time'];
				$timelimit = "-".Yii::app()->params['sessionIdleTime'];
				$rtn = (strtotime($timelimit) < strtotime($time));
			} else {
				$rtn = false;
			}
		}
		echo '{"loggedin":'.($rtn?'true':'false').'}';
		Yii::app()->end();
	}

	public function actionNotify() {
		$rtn = array();
		if (!Yii::app()->user->isGuest) {
			$uid = Yii::app()->user->id;
			$sysid = Yii::app()->params['systemId'];
			$suffix = Yii::app()->params['envSuffix'];
			$suffix = $suffix=='dev' ? '_w' : $suffix;

			$sql = "select a.note_type, count(a.id) as num
				from swoper$suffix.swo_notification a, swoper$suffix.swo_notification_user b 
				where b.username='$uid' and a.system_id='$sysid'
				and a.id=b.note_id and b.status<>'C'
				group by a.note_type
			";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			foreach ($rows as $row) {
				$rtn[] = array('type'=>$row['note_type'],'count'=>$row['num']);
			}
		}
		echo json_encode($rtn);
		Yii::app()->end();
	}
	
	public function actionNotifybadge($param='') {
		$rtn = array();
		$items = empty($param) ? array() : json_decode($param);
		foreach ($items as $item) {
//			if (isset($item->code) && isset($item->function) && isset($this->color)) {
			if (Yii::app()->user->validFunction($item->code)) {
				$result = call_user_func($item->function);
				$rtn[] = array('code'=>$item->code,'count'=>$result,'color'=>$item->color);
			}
//			} 
		}
		echo json_encode($rtn);
		Yii::app()->end();
	}
}
