<?php

class AjaxController extends Controller
{
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
				'actions'=>array('dummy','remotelogin'),
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

//	public function actionSystemDate()
//	{
//		echo CHtml::tag( date('Y-m-d H:i:s'));
//		Yii::app()->end();
//	}
}
