<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
    public $errorCode;

    public $selectUser;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password', 'required'),
			// password needs to be authenticated
			array('password', 'authenticate'),
			array('rememberMe', 'safe'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>Yii::t('misc','User ID'),
			'password'=>Yii::t('misc','Password'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params) {
		if(!$this->hasErrors()) {
			$this->_identity=new UserIdentity($this->username,$this->password);
			if(!$this->_identity->authenticate()) {
				switch ($this->_identity->errorCode) {
					case UserIdentity::ERROR_PASSWORD_INVALID:
						User::model()->updateCounters(array('fail_count'=>1),
							'username=:username',
							array(':username'=>$this->username));
					case UserIdentity::ERROR_USERNAME_INVALID:
						$errmsg = Yii::t('dialog','Incorrect username or password.');
						break;
					case UserIdentity::ERROR_FAIL_EXCESS:
						$errmsg = Yii::t('dialog','Account is locked.');
						break;
                    case UserIdentity::ERROR_RESET_PASSWORD:
                        $this->errorCode = UserIdentity::ERROR_RESET_PASSWORD;
                        $errmsg = Yii::t('dialog','Please reset password');
                        break;
					default:
						$errmsg = Yii::t('dialog','Unable to login.');
				}
				$this->addError('password',$errmsg);
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()	{
		if($this->_identity===null) {
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE) {
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		} else {
			return false;
		}
	}

    /**
     * 门户网站单点登录
     * @return boolean whether login is successful
     */
    public function MHLogin($staffCode,$user_id='')	{
        $this->selectUser=null;
        if($this->_identity===null) {
            $suffix = Yii::app()->params['envSuffix'];
            $staffRows = Yii::app()->db->createCommand()->select("a.user_id")
                ->from("hr{$suffix}.hr_binding a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("b.code=:code and b.staff_status!=-1",array(":code"=>$staffCode))->queryAll();
            if($staffRows===false||empty($staffRows)){
                Dialog::message(Yii::t('dialog','Validation Message'), "员工:{$staffCode}，未绑定账号");
            }elseif (count($staffRows)==1){
                $staffRow = $staffRows[0];
                $this->username = $staffRow["user_id"];
                $this->_identity=new UserIdentity($this->username,$this->password);
                if(!$this->_identity->MHAuthenticate()){
                    Dialog::message(Yii::t('dialog','Validation Message'), $this->getErrorMessage($this->_identity->errorCode));
                }
            }else{ //员工绑定了多个账号
                if(!empty($user_id)){
                    $userBool = false;
                    foreach ($staffRows as $staffRow){
                        if($user_id==$staffRow['user_id']){
                            $userBool = true;
                            $this->username = $user_id;
                            $this->_identity=new UserIdentity($this->username,$this->password);
                            if(!$this->_identity->MHAuthenticate()){
                                Dialog::message(Yii::t('dialog','Validation Message'), $this->getErrorMessage($this->_identity->errorCode));
                            }
                        }
                    }
                    if($userBool===false){
                        Dialog::message(Yii::t('dialog','Validation Message'), "账号:{$user_id}，未绑定员工");
                    }
                }else{
                    $this->selectUser=$staffRows;
                }
            }
        }
        if(!empty($this->_identity)&&$this->_identity->errorCode===UserIdentity::ERROR_NONE) {
            $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 企业微信登录
     * @return boolean whether login is successful
     */
    public function weChatLogin($weChatList,$user_id='')	{
        $this->selectUser=null;
        $suffix = Yii::app()->params['envSuffix'];
        $userid = isset($weChatList["userid"])?$weChatList["userid"]:"";

        if($this->mobileValidate($userid)){//企微绑定的账号直接登录
            return true;//登录成功
        }
        $staffRows = Yii::app()->db->createCommand()->select("a.user_id")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security{$suffix}.sec_user_mobile f","a.user_id=f.username")//需要未绑定企微的账号
            ->where("(b.code=:code or b.qi_wechat=:qi_wechat or b.bs_staff_id=:bs_staff_id) and b.staff_status!=-1 and f.username is null",array(
                ":code"=>$userid,":bs_staff_id"=>$userid,":qi_wechat"=>$userid
            ))->queryAll();
        if($staffRows===false||empty($staffRows)){
            Dialog::message(Yii::t('dialog','Validation Message'), "企微{$userid}，未绑定员工或账号");
        }elseif (count($staffRows)==1){
            $staffRow = $staffRows[0];
            $this->username = $staffRow["user_id"];
            $this->_identity=new UserIdentity($this->username,$this->password);
            if(!$this->_identity->MHAuthenticate()){
                Dialog::message(Yii::t('dialog','Validation Message'), "企微{$userid}，".$this->getErrorMessage($this->_identity->errorCode));
            }
        }else{ //员工绑定了多个账号
            if(!empty($user_id)){
                $userBool = false;
                foreach ($staffRows as $staffRow){
                    if($user_id==$staffRow['user_id']){
                        $userBool = true;
                        $this->username = $user_id;
                        $this->_identity=new UserIdentity($this->username,$this->password);
                        if(!$this->_identity->MHAuthenticate()){
                            Dialog::message(Yii::t('dialog','Validation Message'), "企微{$userid}，".$this->getErrorMessage($this->_identity->errorCode));
                        }
                    }
                }
                if($userBool===false){
                    Dialog::message(Yii::t('dialog','Validation Message'), "账号:{$user_id}，未绑定员工");
                }
            }else{
                $this->selectUser=$staffRows;
            }
        }

        if(!empty($this->_identity)&&$this->_identity->errorCode===UserIdentity::ERROR_NONE) {
            $this->saveMobile($this->username,$userid);
            $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        } else {
            return false;
        }
    }

    //如果企微已经绑定，直接登录
    protected function mobileValidate($userid){
        $suffix = Yii::app()->params['envSuffix'];
        $mobileRow = Yii::app()->db->createCommand()->select("username")
            ->from("security{$suffix}.sec_user_mobile")
            ->where("wechat_key=:wechat_key",array(":wechat_key"=>$userid))->queryRow();
        if($mobileRow){
            $this->username = $mobileRow["username"];
            $identity=new UserIdentity($this->username,$this->password);
            if($identity->MHAuthenticate()){
                $this->loginForIdentity($identity);
                return true;
            }
        }
        return false;
    }

    protected function saveMobile($username,$wechat_key){
        $suffix = Yii::app()->params['envSuffix'];
        $mobileRow = Yii::app()->db->createCommand()->select("username")
            ->from("security{$suffix}.sec_user_mobile")
            ->where("username=:username",array(":username"=>$username))->queryRow();
        if(!$mobileRow){//如果不存在
            Yii::app()->db->createCommand()->insert("security{$suffix}.sec_user_mobile",array(
                "username"=>$username,
                "wechat_key"=>$wechat_key
            ));
        }
    }

    protected function loginForIdentity($identity){
        $this->_identity = $identity;
        $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
        Yii::app()->user->login($this->_identity,$duration);
    }

    public function getUserRows(){
        if (empty($this->selectUser)||!is_array($this->selectUser)){
            return array();
        }else{
            $userSql = array();
            foreach ($this->selectUser as $row){
                $userSql[]=is_array($row)?$row["user_id"]:$row;
            }
            $userSql = implode("','",$userSql);
            $suffix = Yii::app()->params['envSuffix'];
            $staffRows = Yii::app()->db->createCommand()->select("a.username,a.disp_name,b.name as city_name")
                ->from("security{$suffix}.sec_user a")
                ->leftJoin("security{$suffix}.sec_city b","a.city=b.code")
                ->where("a.username in ('{$userSql}')")->queryAll();
            return $staffRows;
        }
    }

    protected function getErrorMessage($code){
        switch ($code){
            case UserIdentity::ERROR_PASSWORD_INVALID:
                $errmsg = "密码错误";
                break;
            case UserIdentity::ERROR_USERNAME_INVALID:
                $errmsg = "用户名不存在";
                break;
            case UserIdentity::ERROR_FAIL_EXCESS:
                $errmsg = Yii::t('dialog','Account is locked.');
                break;
            case UserIdentity::ERROR_RESET_PASSWORD:
                $errmsg = Yii::t('dialog','Please reset password');
                break;
            default:
                $errmsg = $code;
        }
        return $errmsg;
    }
}
