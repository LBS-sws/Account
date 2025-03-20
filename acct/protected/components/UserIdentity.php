<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	const ERROR_FAIL_EXCESS=10;

    const ERROR_RESET_PASSWORD=1001;

	public $displayname;

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user==null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else if($user->status != 'A')
			$this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
		else if(Yii::app()->params['noOfLoginRetry']!=-1 && $user->fail_count >= Yii::app()->params['noOfLoginRetry'])
			$this->errorCode=self::ERROR_FAIL_EXCESS;
        else if(!$user->is_replace_password) //检查是否重新设置过密码
            $this->errorCode=self::ERROR_RESET_PASSWORD;
		else {
			$this->username=$user->username;
			$this->displayname=$user->disp_name;
			$this->errorCode=self::ERROR_NONE;
		}
		return ($this->errorCode===self::ERROR_NONE);
	}

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function MHAuthenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user==null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($user->status != 'A')
			$this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
		else if(Yii::app()->params['noOfLoginRetry']!=-1 && $user->fail_count >= Yii::app()->params['noOfLoginRetry'])
			$this->errorCode=self::ERROR_FAIL_EXCESS;
        else if(!$user->is_replace_password) //检查是否重新设置过密码
            $this->errorCode=self::ERROR_RESET_PASSWORD;
		else {
			$this->username=$user->username;
			$this->displayname=$user->disp_name;
			$this->errorCode=self::ERROR_NONE;
		}
		return ($this->errorCode===self::ERROR_NONE);
	}
}