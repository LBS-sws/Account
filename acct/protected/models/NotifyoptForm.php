<?php

class NotifyoptForm extends CFormModel
{
	public $username;

	public $status;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('status, username', 'required'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'status'=>Yii::t('misc','Status'),
		);
	}
	
	public function retrieveData() {
		$uid = Yii::app()->user->id;
		$sql = "select status from acc_notify_option where username='$uid'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$this->username = $uid;
		$this->status = $row!==false ? $row['status'] : 'Y';
		return true;
	}
	
	public function save() {
		$sql = "insert into acc_notify_option(username, status, lcu, luu) value (:username, :status, :uid, :uid)
				on duplicate key update status=:status, luu=:uid
			";
		$uid = Yii::app()->user->id;
		$command=Yii::app()->db->createCommand($sql);
		if (strpos($sql,':username')!==false)
			$command->bindParam(':username',$this->username,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$uid,PDO::PARAM_STR);
		$command->execute();
		return true;
	}
}
