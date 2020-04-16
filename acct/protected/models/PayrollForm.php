<?php

class PayrollForm extends CFormModel
{
	public $id;
	public $year_no;
	public $month_no;
	public $lcd;
	public $lud;
	public $city;
	public $city_name;
	public $wfstatus;
	public $wfstatusdesc;
	public $reason;
	public $reason_accept;
	public $reason_reject;

	public $wfactionuser;
	
	public $remarks;
	public $amt_sales;
	public $amt_tech;
	public $amt_office;
	public $amt_total;
	
	public $fields = array(
						'remarks',
						'amt_sales',
						'amt_tech',
						'amt_office',
						'amt_total',
					);
	
	public $files;

	public $docMasterId = array(
							'payfile1'=>0,
						);
	public $removeFileId = array(
							'payfile1'=>0,
						);
	public $no_of_attm = array(
							'payfile1'=>0,
						);
	
	public function attributeLabels()
	{
		return array(
			'city'=>Yii::t('misc','City'),
			'city_name'=>Yii::t('misc','City'),
			'year_no'=>Yii::t('report','Year'),
			'month_no'=>Yii::t('report','Month'),
			'remarks'=>Yii::t('trans','Remarks'),
			'wfstatusdesc'=>Yii::t('misc','Status'),
			'reason'=>Yii::t('trans','Reason'),
			'reason_accept'=>Yii::t('trans','Remarks'),
			'reason_reject'=>Yii::t('trans','Reason'),
			'amt_sales'=>Yii::t('trans','Sales Dept.'),
			'amt_tech'=>Yii::t('trans','Tech. Dept.'),
			'amt_office'=>Yii::t('trans','Office'),
			'amt_total'=>Yii::t('trans','Total'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id, year_no, month_no, lcd, lud, city, city_name, remarks, reason, reason_accept, reason_reject, wfstatus, wfstatusdesc','safe'),
			array('files, removeFileId, docMasterId','safe'), 
			array ('no_of_attm','validateAttachment'),
			array('amt_sales, amt_tech, amt_office','safe'), 
			array('amt_total','validateAmount'),
		);
	}

	public function validateAttachment($attribute, $params) {
		$count1 = $this->no_of_attm['payfile1'];
		if (empty($count1) || $count1==0) {
			$this->addError($attribute, Yii::t('trans','Please upload').' '.Yii::t('trans','Payroll File'));
		}
	}

	public function validateAmount($attribute, $params) {
		if (empty($this->amt_sales) && empty($this->amt_tech) && empty($this->amt_office) && empty($this->amt_total)) {
			$this->addError($attribute, Yii::t('trans','Please fill in amount'));
		}
	}

	public function retrieveData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql = "select a.id, a.year_no, a.month_no, b.hdr_id, b.data_field, b.data_value, a.lcd, a.lud,
				a.city, d.name as city_name, 
				workflow$suffix.RequestStatus('PAYROLL',a.id,a.lcd) as wfstatus, 
				workflow$suffix.RequestStatusDesc('PAYROLL',a.id,a.lcd) as wfstatusdesc,
				docman$suffix.countdoc('PAYFILE1',a.id) as file1countdoc
				from acc_payroll_file_hdr a inner join security$suffix.sec_city d on a.city=d.code 
				left outer join acc_payroll_file_dtl b on a.id=b.hdr_id  
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
					$this->wfstatus = $row['wfstatus'];
					$this->wfstatusdesc = $row['wfstatusdesc'];
					$this->no_of_attm['payfile1'] = $row['file1countdoc'];
				}
				$field = $row['data_field'];
				if (!empty($field)) $this->$field = $row['data_value'];
			}
		}

		if (!empty($this->wfstatus)) {
			$wf = new WorkflowPayroll;
			$connection = $wf->openConnection();
		}

		if ($this->wfstatus=='PA' || $this->wfstatus=='PB') {
			if ($wf->initReadOnlyProcess('PAYROLL',$this->id,$this->lcd)) {
				$actionusers = $wf->getCurrentStateRespUser();
				$this->wfactionuser = empty($actionusers) ? '' : implode('/',$actionusers);
			}
		}
		
		if (substr($this->wfstatus,0,1)=='P') {
			if ($wf->initReadOnlyProcess('PAYROLL',$this->id,$this->lcd)) {
				$reasons = $wf->getLatestActionRemark();
				if (!empty($reasons)) {
					foreach ($reasons as $userid=>$reason) {
						$this->reason = empty($this->reason) ? $reason : $this->reason."\n".$reason;
					}
				}
			}
		}
		return true;
	}
	
	public function submit() {
		$wf = new WorkflowPayroll;
		$connection = $wf->openConnection();
		try {
			$this->savePayroll($connection);
			$this->updateDocman($connection,'PAYFILE1');
			if ($wf->startProcess('PAYROLL',$this->id,$this->lcd)) {
				$wf->saveRequestData('CITY',$this->city);
				$wf->saveRequestData('CITYNAME',$this->city_name);
				$wf->saveRequestData('REQ_USER',Yii::app()->user->id);
				$wf->saveRequestData('YEAR',$this->year_no);
				$wf->saveRequestData('MONTH',$this->month_no);
				$wf->takeAction('SUBMIT');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function resubmit() {
		$wf = new WorkflowPayroll;
		$connection = $wf->openConnection();
		try {
			$this->savePayroll($connection);
			$this->updateDocman($connection,'PAYFILE1');
			if ($wf->startProcess('PAYROLL',$this->id,$this->lcd)) {
				$wf->takeAction('RESUBMIT');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	public function accept()
	{
		$wf = new WorkflowPayroll;
		$connection = $wf->openConnection();
		try {
			if ($wf->startProcess('PAYROLL',$this->id,$this->lcd)) {
				$action = $this->wfstatus=='PB' ? 'APPROVE' : 'RHAPPROVE';
				$wf->takeAction($action,$this->reason_accept);
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function reject()
	{
		$wf = new WorkflowPayroll;
		$connection = $wf->openConnection();
		try {
			if ($wf->startProcess('PAYROLL',$this->id,$this->lcd)) {
				$action = $this->wfstatus=='PB' ? 'DENY' : 'RHDENY';
				$wf->takeAction($action,$this->reason_reject);
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->savePayroll($connection);
			$this->updateDocman($connection,'PAYFILE1');
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
		}
	}

	protected function savePayroll(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
			case 'resubmit':
			case 'submit':
				$sql = "update acc_payroll_file_hdr
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
			case 'resubmit':
			case 'submit':
				$sql = "insert into acc_payroll_file_dtl(hdr_id, data_field, data_value, lcu, luu)
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

	public function validUserInCurrentAction() {
		$uid = Yii::app()->user->id;
		return (strpos('/'.$this->wfactionuser.'/', '/'.$uid.'/')!==false);
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view'|| strpos('~~PS~','~'.$this->wfstatus.'~')===false);
	}
}
