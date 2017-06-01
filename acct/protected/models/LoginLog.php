<?php
class LoginLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function rules()
	{
		return array(
			array('station_id,username,client_ip,login_time','safe'),
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'security'.Yii::app()->params['envSuffix'].'.sec_login_log';
	}
}
