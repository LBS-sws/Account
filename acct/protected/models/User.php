<?php
class User extends CActiveRecord
{
	public $salt = 'bubble000';
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		
		return 'security'.Yii::app()->params['envSuffix'].'.sec_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username', 'required'),
			array('username', 'length', 'max'=>30),
			array('password', 'length', 'max'=>128),
			array('disp_name', 'length', 'max'=>100),
			array('status', 'length', 'max'=>1),
			array('logon_time, logoff_time, group_id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('username, password, disp_name, logon_time, logoff_time, status', 'safe', 'on'=>'search'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'username' => Yii::t('user','Username'),
			'password' => Yii::t('user','Password'),
			'disp_name' => Yii::t('user','Disp Name'),
			'logon_time' => Yii::t('user','Logon Time'),
			'logoff_time' => Yii::t('user','Logoff Time'),
			'status' => Yii::t('user','Status'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('disp_name',$this->disp_name,true);
		$criteria->compare('logon_time',$this->logon_time,true);
		$criteria->compare('logoff_time',$this->logoff_time,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function validatePassword($password)
	{
		return $this->hashPassword($password,$this->salt)===$this->password;
	}
	
	public function hashPassword($password,$salt)
	{
		return md5($salt.$password);
	}

	public function accessRights() {
		$suffix = Yii::app()->params['envSuffix'];

		$username = $this->username;
		$rtn = array(
				'read_only'=>array(),
				'read_write'=>array(),
				'control'=>array(),
			);
		$sql = "select system_id, a_read_only, a_read_write, a_control 
				from security$suffix.sec_user_access 
				where username='$username'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn['read_only'] = array_merge($rtn['read_only'], array($row['system_id']=>$row['a_read_only']));
				$rtn['read_write'] = array_merge($rtn['read_write'], array($row['system_id']=>$row['a_read_write']));
				$rtn['control'] = array_merge($rtn['control'], array($row['system_id']=>$row['a_control']));
			}
		}
		return $rtn;
	}

	public function getUserOption() {
		$suffix = Yii::app()->params['envSuffix'];

		$rtn = array();
		$username = $this->username;
		$sql = "select * from security$suffix.sec_user_option where Lower(username)='$username'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[$row['option_key']] = $row['option_value'];
			}
		}
		return $rtn;
	}

	public function saveUserOption($name, $key, $value)	{
		$suffix = Yii::app()->params['envSuffix'];

		$connection = Yii::app()->db;
		$sql = "replace into security$suffix.sec_user_option 
					(username, option_key, option_value)
				values
					(:username, :option_key, :option_value)
			";
		$command = $connection->createCommand($sql);
		$command->bindParam(':username', $name, PDO::PARAM_STR);
		$command->bindParam(':option_key', $key, PDO::PARAM_STR);
		$command->bindParam(':option_value', $value, PDO::PARAM_STR);
		$command->execute();
	}
	
	public function getUserInfoImage($fieldId) {
		$suffix = Yii::app()->params['envSuffix'];

		$rtn = '';
		$username = $this->username;
		$sql = "select field_blob from security$suffix.sec_user_info where Lower(username)='$username' and field_id='$fieldId'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) $rtn = base64_decode($row['field_blob']);
		return $rtn;
	}

	public function getUserInfo($fieldId) {
		$suffix = Yii::app()->params['envSuffix'];

		$rtn = '';
		$username = $this->username;
		$sql = "select field_value from security$suffix.sec_user_info where Lower(username)='$username' and field_id='$fieldId'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) $rtn = $row['field_value'];
		return $rtn;
	}

    public function resetPassword($username,$password)
    {
        $result = self::model()->find('LOWER(username)=?',array($username));
        if($result==null) return ['status'=>false,'msg'=>'未找到当前账号！'];
        $result->password = $this->hashPassword($password,$this->salt);
        $result->is_replace_password = 1;
        $res = $result->save();
        if(!$res) return ['status'=>false,'msg'=>'密码重置失败！'];
        return ['status'=>true,'msg'=>''];
    }
}