<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
	
	public $displayname;
	
	public function init()
	{
		parent::init();
		$session = Yii::app()->session;
		if (isset($session['lang']))
			Yii::app()->language = $session['lang'];
	}
	
	public function filterEnforceRegisteredStation($filterChain) {
		$rtn = true;
		if (Yii::app()->params['checkStation']) {
			if (!isset(Yii::app()->session['station'])) {
				if (Cookie::hasCookie('station_key')) {
					$key = Cookie::getCookie('station_key');
					$station = Station::model()->find("station_id=? and status='Y'",array($key));
					if ($station!=null)
						Yii::app()->session['station'] = $key;
					else
						$rtn = false;
				} else {
					$rtn = false;
				}
			}
		}
		if ($rtn)
			$filterChain->run();
		else {
//			Yii::app()->user->logout();
			throw new CHttpException(403,Yii::t('misc',"Invalid Station. Please register first.")); 
		}
	}

	public function filterEnforceNoConcurrentLogin($filterChain) {
		$rtn = true;
		if (!Yii::app()->user->isGuest && !Yii::app()->params['concurrentLogin']) {
			if (isset(Yii::app()->session['session_key'])) {
				$uid = Yii::app()->user->id;
				$key = Yii::app()->session['session_key'];
				$user = User::model()->find("username=? and session_key=?",array($uid,$key));
				if ($user===null) $rtn = false;
			} else {
				$rtn = false;
			}
		}
		if ($rtn)
			$filterChain->run();
		else {
			Yii::app()->user->logout();
//			Dialog::message('Warning Message', Yii::t('misc',"User ID has been logged in more than one station."));
//			$this->redirect(Yii::app()->createUrl('site/login'));
			throw new CHttpException(999,Yii::t('misc',"User ID has been logged in more than one station.")); 
		}
	}

	public function filterEnforceSessionExpiration($filterChain) {
		$rtn = true;
		if (!Yii::app()->user->isGuest && Yii::app()->params['sessionIdleTime']!=='') {
			if (isset(Yii::app()->session['session_time'])) {
				$time = Yii::app()->session['session_time'];
				$timelimit = "-".Yii::app()->params['sessionIdleTime'];
				if (strtotime($timelimit) < strtotime($time))
					Yii::app()->session['session_time'] = date("Y-m-d H:i:s");
				else
					$rtn = false;
			} else {
				$rtn = false;
			}
		}
		if ($rtn)
			$filterChain->run();
		else {
			$returl = Yii::app()->user->returnUrl;
			Yii::app()->user->logout();
//			Dialog::message('Warning Message', Yii::t('misc',"Session expired."));
//			$this->redirect(Yii::app()->createUrl('site/login'));
			throw new CHttpException(999,Yii::t('misc',"Session expired.")); 
		}
	}
}