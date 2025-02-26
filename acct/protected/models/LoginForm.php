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
}
