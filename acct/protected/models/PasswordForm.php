<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class PasswordForm extends CFormModel
{
	public $oldPassword;
	public $newPassword;
	public $confirmPassword;

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
			array('oldPassword, newPassword, confirmPassword', 'required'),
			// password needs to be authenticated
			array('oldPassword', 'authenticate'),
			array('newPassword', 'compare', 'compareAttribute'=>'confirmPassword', 
				'message'=>Yii::t('dialog','New password and confirm password not match')),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'oldPassword'=>Yii::t('misc','Old Password'),
			'newPassword'=>Yii::t('misc','New Password'),
			'confirmPassword'=>Yii::t('misc','Confirm Password'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity(Yii::app()->user->id,$this->oldPassword);
			if(!$this->_identity->authenticate())
				$this->addError('oldPassword',Yii::t('dialog','Incorrect old password.'));
		}
	}

	public function save()
	{
		$user = User::model()->find('LOWER(username)=?',array(Yii::app()->user->id));
		$pwd = $user->hashPassword($this->newPassword,$user->salt);
		User::model()->updateByPk(Yii::app()->user->id,array('password'=>$pwd));
	}
}
