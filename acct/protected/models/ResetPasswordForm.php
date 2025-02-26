<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class ResetPasswordForm extends CFormModel
{
	public $username;
//	public $password;
    public $new_password;
    public $again_new_password;
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
            array('username,new_password,again_new_password', 'required'),
			// password needs to be authenticated
			array('new_password', 'authenticate'),
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
            'new_password'=>Yii::t('misc','New Password'),
            'again_new_password'=>Yii::t('misc','Again New Password'),
		);
	}

    /**
     * Authenticates the password.
     */
    public function authenticate($attribute,$params) {
        if($this->new_password != $this->again_new_password) $this->addError('password',Yii::t('dialog','The password entered twice is inconsistent'));

        $length = mb_strlen($this->new_password, 'utf8');
        $vaildRes = $this->isValidString($this->new_password);
        if($length < 8 || !$vaildRes) $this->addError('password',Yii::t('dialog','The password requires 8-20 characters, consisting of numbers, letters, and symbols'));
        return true;
    }

    /**
     * 密码验证
     * @param $str
     * @return bool
     */
    public function isValidString($str) {
        $hasDigit = preg_match('/\d/', $str);
        $hasLetter = preg_match('/[a-zA-Z]/', $str);
        $hasSymbol = preg_match('/[^a-zA-Z\d]/', $str);
        $typesCount = array_sum([$hasDigit, $hasLetter, $hasSymbol]);
        return $typesCount >= 2;
    }


}
