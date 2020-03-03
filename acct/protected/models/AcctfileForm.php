<?php

class AcctfileForm extends CFormModel
{
	public $id;
	public $year_no;
	public $month_no;
	public $lcd;
	public $lud;
	public $city;
	public $city_name;
	
	public $remarks;
	public $mail_dt;
	
	public $fields = array(
						'remarks',
						'mail_dt',
					);
	
	public $files;

	public $docMasterId = array(
							'acctfile1'=>0,
							'acctfile2'=>0,
							'acctfile3'=>0,
							'acctfile4'=>0
						);
	public $removeFileId = array(
							'acctfile1'=>0,
							'acctfile2'=>0,
							'acctfile3'=>0,
							'acctfile4'=>0
						);
	public $no_of_attm = array(
							'acctfile1'=>0,
							'acctfile2'=>0,
							'acctfile3'=>0,
							'acctfile4'=>0
						);
	
	public function attributeLabels()
	{
		return array(
			'city'=>Yii::t('misc','City'),
			'city_name'=>Yii::t('misc','City'),
			'year_no'=>Yii::t('report','Year'),
			'month_no'=>Yii::t('report','Month'),
			'remarks'=>Yii::t('trans','Remarks'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id, year_no, month_no, lcd, lud, city, city_name, remarks','safe'),
			array('files, removeFileId, docMasterId','safe'), 
			array ('no_of_attm','validateAttachment'),
		);
	}

	public function validateAttachment($attribute, $params) {
		$count1 = $this->no_of_attm['acctfile1'];
		$count2 = $this->no_of_attm['acctfile2'];
		$count3 = $this->no_of_attm['acctfile3'];
		$count4 = $this->no_of_attm['acctfile4'];
		if (empty($count1) || $count1==0) {
			$this->addError($attribute, Yii::t('trans','Please upload').' '.Yii::t('trans','General Account File'));
		}
		if (empty($count2) || $count2==0) {
			$this->addError($attribute, Yii::t('trans','Please upload').' '.Yii::t('trans','Basic Account File'));
		}
	}

	public function retrieveData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql = "select a.id, a.year_no, a.month_no, b.hdr_id, b.data_field, b.data_value, a.lcd, a.lud,
				a.city, d.name as city_name, 
				docman$suffix.countdoc('ACCTFILE1',a.id) as file1countdoc,
				docman$suffix.countdoc('ACCTFILE2',a.id) as file2countdoc,
				docman$suffix.countdoc('ACCTFILE3',a.id) as file3countdoc,
				docman$suffix.countdoc('ACCTFILE4',a.id) as file4countdoc
				from acc_account_file_hdr a inner join security$suffix.sec_city d on a.city=d.code 
				left outer join acc_account_file_dtl b on a.id=b.hdr_id  
				where a.id=$index and a.city in ($citylist)
				order by a.year_no, a.month_no, b.data_field
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			$hid = 0;
			foreach ($rows as $row) {
				if ($hid!=$row['id']) {
					$hid = $row['id'];
					$this->id = $hid;
					$this->year_no = $row['year_no'];
					$this->month_no = $row['month_no'];
					$this->city = $row['city'];
					$this->city_name = $row['city_name'];
					$this->lcd = $row['lcd'];
					$this->lud = $row['lud'];
					$this->no_of_attm['acctfile1'] = $row['file1countdoc'];
					$this->no_of_attm['acctfile2'] = $row['file2countdoc'];
					$this->no_of_attm['acctfile3'] = $row['file3countdoc'];
					$this->no_of_attm['acctfile4'] = $row['file4countdoc'];
				}
				$field = $row['data_field'];
				if (!empty($field)) $this->$field = $row['data_value'];
			}
		}

		return true;
	}
	
	public function send() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$dt = Date('Y-m-d H:i:s');
			
			$sql = "insert into acc_account_file_dtl(hdr_id, data_field, data_value, lcu, luu)
					values(:hdr_id, 'mail_dt', :data_value, :uid, :uid)
					on duplicate key update
						data_value = :data_value, luu = :uid 
				";
			$command=$connection->createCommand($sql);
			if (strpos($sql,':hdr_id')!==false)
				$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':data_value')!==false) {
				$command->bindParam(':data_value',$dt,PDO::PARAM_STR);
			}
			if (strpos($sql,':uid')!==false)
				$command->bindParam(':uid',$uid,PDO::PARAM_STR);
			$command->execute();
			
			$from_addr = Yii::app()->params['adminEmail'];
			$to_addr = json_encode($this->getRecipient());
			$cc_addr = json_encode(array());
			$subject = Yii::t('workflow','[Notice]').Yii::t('app','Accounting').': '.Yii::t('trans','Bank Balance File Uploaded')
				.' ('.$this->year_no.'/'.$this->month_no.' '.$this->city_name.')';
			$description = Yii::t('app','Accounting').': '.Yii::t('app','Bank Balance');
			$message = Yii::t('misc','City').': '.$this->city_name.'<br>'
				 .Yii::t('report','Date').': '.$this->year_no.'/'.$this->month_no.'<br>'
				 .Yii::t('trans','Bank balance file uploaded. Please check.');
			
			$suffix = Yii::app()->params['envSuffix'];
			$suffix = $suffix=='dev' ? '_w' : $suffix;
			$sql = "insert into swoper$suffix.swo_email_queue
						(from_addr, to_addr, cc_addr, subject, description, message, status, lcu)
					values
						(:from_addr, :to_addr, :cc_addr, :subject, :description, :message, 'P', 'admin')
					";
			$command = $connection->createCommand($sql);
			if (strpos($sql,':from_addr')!==false)
				$command->bindParam(':from_addr',$from_addr,PDO::PARAM_STR);
			if (strpos($sql,':to_addr')!==false)
				$command->bindParam(':to_addr',$to_addr,PDO::PARAM_STR);
			if (strpos($sql,':cc_addr')!==false)
				$command->bindParam(':cc_addr',$cc_addr,PDO::PARAM_STR);
			if (strpos($sql,':subject')!==false)
				$command->bindParam(':subject',$subject,PDO::PARAM_STR);
			if (strpos($sql,':description')!==false)
				$command->bindParam(':description',$description,PDO::PARAM_STR);
			if (strpos($sql,':message')!==false)
				$command->bindParam(':message',$message,PDO::PARAM_STR);
			$command->execute();

			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
		}
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveAcctfile($connection);
			$this->updateDocman($connection,'ACCTFILE1');
			$this->updateDocman($connection,'ACCTFILE2');
			$this->updateDocman($connection,'ACCTFILE3');
			$this->updateDocman($connection,'ACCTFILE4');
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
		}
	}

	protected function saveAcctfile(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
				$sql = "update acc_account_file_hdr
						set luu = :uid
						where id = :id
					";
				break;
		}
		if (empty($sql)) return false;

		$city = $this->city; //Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$uid,PDO::PARAM_STR);
		$command->execute();
		
		switch ($this->scenario) {
			case 'edit':
				$sql = "insert into acc_account_file_dtl(hdr_id, data_field, data_value, lcu, luu)
						values(:hdr_id, :data_field, :data_value, :uid, :uid)
						on duplicate key update
							data_value = :data_value, luu = :uid 
					";
				break;
		}

		foreach ($this->fields as $field) {
			$command=$connection->createCommand($sql);
			if (strpos($sql,':hdr_id')!==false)
				$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':data_field')!==false) 
				$command->bindParam(':data_field',$field,PDO::PARAM_STR);
			if (strpos($sql,':data_value')!==false) 
				$command->bindParam(':data_value',$this->$field,PDO::PARAM_STR);
			if (strpos($sql,':uid')!==false)
				$command->bindParam(':uid',$uid,PDO::PARAM_STR);
			$command->execute();
		}
		return true;
	}
	
	protected function updateDocman(&$connection, $doctype) {
		if ($this->scenario=='new') {
			$docidx = strtolower($doctype);
			if ($this->docMasterId[$docidx] > 0) {
				$docman = new DocMan($doctype,$this->id,get_class($this));
				$docman->masterId = $this->docMasterId[$docidx];
				$docman->updateDocId($connection, $this->docMasterId[$docidx]);
			}
		}
	}

	protected function getRecipient() {
		$rtn = array();

		$city = new City();
		$city_list = $city->getAncestorList($this->city);
		$city_list .= (empty($city_list) ? '' : ',')."'".$this->city."'";

		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.email 
				from security$suffix.sec_user a, security$suffix.sec_user_access b
				where a.username=b.username and a.city in ($city_list)
				and b.a_read_only like '%XE07%'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			if (!in_array($row['email'],$rtn)) $rtn[] = $row['email'];
		}
		return $rtn;
	}

	public function validUserInCurrentAction() {
		$uid = Yii::app()->user->id;
		return (strpos('/'.$this->wfactionuser.'/', '/'.$uid.'/')!==false);
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view');
	}
}
