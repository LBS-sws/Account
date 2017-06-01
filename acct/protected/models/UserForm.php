<?php

/**
 * UserForm class.
 * UserForm is the data structure for keeping
 * user form data. It is used by the 'user' action of 'SiteController'.
 */
class UserForm extends CFormModel
{
	/* User Fields */
	public $username;
	public $password;
	public $disp_name;
	public $logon_time;
	public $logoff_time;
	public $status='A';
	public $city;
	public $fail_count;
	public $lock;
	public $email;
	public $rights = array();

	private $systems;
	private $localelabels;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>Yii::t('user','User ID'),
			'password'=>Yii::t('user','Password'),
			'disp_name'=>Yii::t('user','Display Name'),
			'status'=>Yii::t('user','Status'),
			'logon_time'=>Yii::t('user','Logon Time'),
			'logoff_time'=>Yii::t('user','Logoff Time'),
			'group_id'=>Yii::t('user','Group'),
			'city'=>Yii::t('user','City'),
			'lock'=>Yii::t('user','Lock'),
			'email'=>Yii::t('user','Email'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('username, disp_name, city','required'),
			array('username','filter','filter'=>'trim'),
			array('username','unique','allowEmpty'=>false,
					'attributeName'=>'username',
					'caseSensitive'=>false,
					'className'=>'User',
					'on'=>'new'
				),
			array('status','in','range'=>array('A','I'),'allowEmpty'=>false),
			array('logon_time, logoff_time, fail_count, lock, rights','safe'), 
			array('password','required','on'=>'new'),
			array('password','safe','on'=>'edit, delete'),
			array('email','email','allowEmpty'=>true,),
		);
	}

	public function init() {
		parent::init();
		$this->systems = General::getInstalledSystemFunctions();
		$this->localelabels = General::getLocaleAppLabels();
		$this->initAccessRights();
	}

	protected function initAccessRights() {
		$cnt = 0;
		foreach($this->systems as $sid=>$items) {
			$this->rights[$cnt] = array();
			foreach($items['item'] as $group=>$func) {
				foreach($func as $fid=>$fname) {
					$this->rights[$cnt][$fid] = 'NA';
				}
			}
			$cnt++;
		}
	}

	public function functionLabels($key) {
		return (!empty($this->localelabels) && isset($this->localelabels[$key]) ? $this->localelabels[$key] : $key);
	}

	public function installedSystem() {
		$rtn = array();
		foreach($this->systems as $id=>$value) {
			$rtn[$id] = Yii::t('app',$value['name']);
		}
		return $rtn;
	}

	public function installedSystemGroup($systemId) {
		$rtn = array();
		foreach($this->systems[$systemId]['item'] as $group=>$value) {
			$rtn[] = $group;
		}
		return $rtn;
	}

	public function installedSystemItems($systemId, $groupName) {
		$rtn = array();
		foreach($this->systems[$systemId]['item'][$groupName] as $id=>$value) {
			$rtn[$id] = $this->functionLabels($value['name']).' '.$value['tag'];
		}
		return $rtn;
	}

	public function getTemplateData($id) {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select system_id, a_read_only, a_read_write, a_control from security$suffix.sec_template
				where temp_id=$id 
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$ro = $record['a_read_only'];
				$rw = $record['a_read_write'];
				$cn = $record['a_control'];
				$sid = $record['system_id'];
				break;
			}

			$a_sys = $this->systemMappingArray();
			$idx = array_search($sid, $a_sys);

			foreach($this->rights[$idx] as $key=>$value) {
				$access = (strpos($rw,$key)!==false) ? 'RW' :
											((strpos($ro,$key)!==false) ? 'RO' :
											((strpos($cn,$key)!==false) ? 'CN' : 'NA'
											));
				$rtn[] = array('idx'=>$idx,'id'=>$key,'value'=>$access);
			}
		}
		return $rtn;
	}

	public function retrieveData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.* from security$suffix.sec_user a where a.username='$index' and a.username<>'admin'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$this->username = $row['username'];
				$this->password = '';
				$this->disp_name = $row['disp_name'];
				$this->status = $row['status'];
				$this->logon_time = $row['logon_time'];
				$this->logoff_time = $row['logoff_time'];
				$this->city = $row['city'];
				$this->fail_count = $row['fail_count'];
				$this->lock = (Yii::app()->params['noOfLoginRetry']>0 && Yii::app()->params['noOfLoginRetry']<=$this->fail_count) ?
						Yii::t('misc','Yes') :
						Yii::t('misc','No');
				$this->email = $row['email'];
				break;
			}

/*
	array('drs'=>array('name'=>'Daily Report',
					'item'=>array(
						'Data Entry'=>array(
							'A07'=>'Staff Info',
						),
					),
				),
		'acct'=>array(),
	)
*/
			$sql = "select system_id, a_read_only, a_read_write, a_control 
						from security$suffix.sec_user_access
						where username='$index'
				";
			$dtls = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($dtls) > 0){
				$a_sys = $this->systemMappingArray();

				foreach ($dtls as $dtl) {
					$sid = $dtl['system_id'];
					$idx = array_search($sid, $a_sys);
					foreach($this->rights[$idx] as $key=>$value) {
						$this->rights[$idx][$key] = (strpos($dtl['a_read_write'],$key)!==false) ? 'RW' :
													((strpos($dtl['a_read_only'],$key)!==false) ? 'RO' :
													((strpos($dtl['a_control'],$key)!==false) ? 'CN' : 'NA'
													));
					}
				}
			}
		}
		return true;
	}
	
	protected function systemMappingArray() {
		$rtn = array();
		foreach (Yii::app()->params['systemMapping'] as $key=>$value) {
			$rtn[] = $key;
		}
		return $rtn;
	}

	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveUser($connection);
			$this->saveRights($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveUser(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$userobj = User::model();
		$hashPass = $this->password == '' ? '' :
			$userobj->hashPassword($this->password,$userobj->salt);

		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from security$suffix.sec_user where username = :username";
				break;
			case 'new':
				$sql = "insert into security$suffix.sec_user
							(username, password, disp_name, email, status, lcu, luu, city)
						values 
							(:username, :password, :dispname, :email, :status, :lcu, :luu, :city)
					";
				break;
			case 'edit':
				$sql = "update security$suffix.sec_user set ";
				if ($hashPass !== '') $sql .= "password = :password, ";
				$sql .= "disp_name = :dispname, email = :email, city = :city, "
					. (($this->lock==Yii::t('misc','Yes') && $this->fail_count==0) ? "fail_count = 0, " : "")
					. "luu = :luu, status = :status where username = :username";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		$command->bindParam(':username',$this->username,PDO::PARAM_STR);
		if (strpos($sql,':dispname')!==false)
			$command->bindParam(':dispname',$this->disp_name,PDO::PARAM_STR);
		if (strpos($sql,':email')!==false)
			$command->bindParam(':email',$this->email,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':password')!==false)
			$command->bindParam(':password',$hashPass,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}

	protected function saveRights(&$connection) {
		$suffix = Yii::app()->params['envSuffix'];

		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from security$suffix.sec_user_access where username = :username and system_id :system_id";
				break;
			case 'new':
			case 'edit':
				$sql = "insert into security$suffix.sec_user_access 
							(username, system_id, a_read_only, a_read_write, a_control, lcu, luu)
						values 
							(:username, :system_id, :a_read_only, :a_read_write, :a_control, :lcu, :luu)
						on duplicate key update a_read_only = :a_read_only, a_read_write = :a_read_write,
							a_control = :a_control, luu = :luu
					";
				break;
		}

		$uid = Yii::app()->user->id;
		$a_sys = $this->systemMappingArray();
		foreach($this->rights as $idx=>$funcs) {
			$ro = '';
			$rw = '';
			$cn = '';
			foreach($funcs as $aid=>$aval) {
				$rw .= ($aval=='RW') ? $aid : '';
				$ro .= ($aval=='RO') ? $aid : '';
				$cn .= ($aval=='CN') ? $aid : '';
			}
			$sid = $a_sys[$idx];
			$command=$connection->createCommand($sql);
			$command->bindParam(':username',$this->username,PDO::PARAM_STR);
			$command->bindParam(':system_id',$sid,PDO::PARAM_STR);
			$command->bindParam(':a_read_only',$ro,PDO::PARAM_STR);
			$command->bindParam(':a_read_write',$rw,PDO::PARAM_STR);
			$command->bindParam(':a_control',$cn,PDO::PARAM_STR);
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			$command->execute();
		}
	}
}
