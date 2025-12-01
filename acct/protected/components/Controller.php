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
	
	public $function_id = '';
	
	public $interactive = true;
	
	public function init()
	{
		parent::init();
		$session = Yii::app()->session;
		if (isset($session['lang']))
			Yii::app()->language = $session['lang'];
        if (!Yii::app()->user->isGuest) {//切換系統
            $uname = Yii::app()->user->name;
            if(!empty($uname)&&$session['system']!=Yii::app()->params['systemId']){
                $session['system'] = Yii::app()->params['systemId'];
                Yii::app()->user->saveUserOption($uname, 'system', Yii::app()->params['systemId']);
            }
            if(isset($_GET['portalnumber'])){
                $staffCode=$this->getStaffCodeForUsername();
                if($_GET['portalnumber']!=base64_encode($staffCode)){
                    Yii::app()->user->logout();
                    $this->redirect(Yii::app()->createUrl('site/login'));
                }
            }
        }else{//由于找不到框架的过滤器在哪，所以写在了这里
            if(isset($_GET['ticket'])){
                $lbsUrl = Yii::app()->getBaseUrl(true);
                if(isset($_GET["user_id"])){
                    $lbsUrl.="?user_id=".$_GET["user_id"];//多用户登录
                }
                //$lbsUrl = urlencode($lbsUrl);
                $url = Yii::app()->params['MHCurlRootURL']."/cas/p3/serviceValidate?";
                $queryArr = array(
                    "ticket"=>$_GET['ticket'],
                    "service"=>$lbsUrl,
                    "format"=>"json",
                );
                $url.= http_build_query($queryArr);
                $result = file_get_contents($url);
                $resultJson = json_decode($result,true);
                if(is_array($resultJson)){
                    if(isset($resultJson["serviceResponse"]["authenticationSuccess"]["user"])){
                        $user_id = isset($_GET['user_id'])?$_GET['user_id']:"";
                        $userCode = $resultJson["serviceResponse"]["authenticationSuccess"]["user"];
                        $model=new LoginForm;
                        $bool = $model->MHLogin($userCode,$user_id);
                        if($bool){
                            $this->redirect(Yii::app()->user->returnUrl);
                        }elseif (!empty($model->selectUser)){//多个账号
                            $userRows = $model->getUserRows();
                            $this->layout=false;
                            $url = Yii::app()->getBaseUrl(true)."/site/login?";
                            $this->render('//site/selectUser',array('selectUser'=>$userRows,'selectUrl'=>$url));
                            Yii::app()->end();
                        }
                    }else{
                        Dialog::message("ticket异常", $result);
                    }
                }else{
                    Dialog::message("ticket异常", $result);
                }
                $this->redirect(Yii::app()->createUrl('site/loginOld'));//账号异常跳转本页登录（防止死循环）
            }elseif(isset($_GET["state"])&&strpos($_GET["state"],'LBSWeChatApp')!==false){//微信自动登录
                $weChatModel = new CWeChatModel();
                $code = isset($_GET["code"])?$_GET["code"]:"000";
                $weChatList = $weChatModel->getWeChatUserList($code);
                if($weChatList["status"]){
                    $user_id = str_replace("LBSWeChatApp", "", $_GET["state"]);;
                    $model=new LoginForm;
                    $bool = $model->WeChatLogin($weChatList,$user_id);
                    if($bool){
                        $this->redirect(Yii::app()->user->returnUrl);
                    }elseif (!empty($model->selectUser)){//多个账号
                        $userRows = $model->getUserRows();
                        $this->layout=false;
                        $url = Yii::app()->getBaseUrl(true)."/site/login?loginType=mobile";
                        $this->render('//site/selectUser',array('selectUser'=>$userRows,'selectUrl'=>$url));
                        Yii::app()->end();
                    }
                }else{
                    Dialog::message("微信接口异常", $weChatList["message"]);
                }
                $this->redirect(Yii::app()->createUrl('site/loginOld'));//账号异常跳转本页登录（防止死循环）
            }
        }
	}

	protected function getStaffCodeForUsername(){
        $suffix = Yii::app()->params['envSuffix'];
        $staffRow = Yii::app()->db->createCommand()->select("b.code")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.user_id=:user_id",array(":user_id"=>Yii::app()->user->id))->queryRow();
        if($staffRow){
            return $staffRow["code"];
        }else{
            return "0";
        }
    }

    public function beforeAction($action) {
	    $actionId = Yii::app()->getController()->getAction()->id;
        $actionId = strtolower($actionId);
        //ajax請求並且是ajaxController內的方法不需要驗證
        if(Yii::app()->request->isAjaxRequest&&Yii::app()->controller->id=="ajax"){
            return true;
        }
        if (!Yii::app()->user->isGuest) {
            General::includeDrsSysBlock();
            $obj = new SysBlock();
            $url = $obj->blockNRoute($this->id, $this->function_id);
            if ($url!==false) $this->redirect($url);
        }elseif(!in_array($actionId,array("loginold","mhview","password","resetloginpassword"))){
            $userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
            //if (strpos($userAgent, 'MicroMessenger') !== false) {//可能是企微
            if (strpos($userAgent, 'wxwork') !== false) {//可能是企微
                $weChatModel = new CWeChatModel();
                $weChatUrl = $weChatModel->getWeChatLoginUrl();
                $this->redirect($weChatUrl);
            }
        }
        return true;
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
			$this->redirect(Yii::app()->createUrl('site/login'));
//			throw new CHttpException(999,Yii::t('misc',"User ID has been logged in more than one station.")); 
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
		if ($rtn) {
			if ($this->interactive) Yii::app()->session['active_func'] = $this->function_id;
			$filterChain->run();
		} else {
			$returl = Yii::app()->user->returnUrl;
			Yii::app()->user->logout();
//			Dialog::message('Warning Message', Yii::t('misc',"Session expired."));
			$this->redirect(Yii::app()->createUrl('site/login'));
//			throw new CHttpException(999,Yii::t('misc',"Session expired.")); 
		}
	}
}
