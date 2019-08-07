<?php

class CReportForm extends CFormModel
{
	public $id;
	public $name;
	public $start_dt;
	public $end_dt;
	public $format;
	public $uid;
	public $city;
    public $date;
	public $target_dt;
	public $fields;
	public $email;
	public $emailcc;
	public $touser;
	public $ccuser;
	public $year;
	public $month;
	public $type;
	
	public $rpt_array;

	public $paper_sz = 'A4';
	public $multiuser = false;
	
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$orig = array(
			'start_dt'=>Yii::t('report','Start Date'),
			'end_dt'=>Yii::t('report','End Date'),
			'format'=>Yii::t('report','Output Format'),
			'target_dt'=>Yii::t('report','Date'),
			'email'=>Yii::t('report','Email'),
			'touser'=>Yii::t('report','Email To'),
			'emailcc'=>Yii::t('report','Email Cc'),
			'ccuser'=>Yii::t('report','Email Cc').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
			'year'=>Yii::t('report','Year'),
			'month'=>Yii::t('report','Month'),
			'city'=>Yii::t('report','City'),
			'cityx'=>Yii::t('report','City').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
			'type'=>Yii::t('report','Type').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
		);
		
		$extra = $this->labelsEx();
		
		return array_merge($orig, $extra);
	}

	protected function labelsEx() {
		return array();
	}
	
	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		$orig = array(
			array('id, name, format, uid, city, fields, touser, ccuser, year, month, type','safe'),
			array('start_dt','date','allowEmpty'=>!$this->showField('start_dt'),
				'format'=>array('yyyy/MM/dd','yyyy-MM-dd','yyyy/M/d','yyyy-M-d',),
			),
			array('end_dt','date','allowEmpty'=>!$this->showField('end_dt'),
				'format'=>array('yyyy/MM/dd','yyyy-MM-dd','yyyy/M/d','yyyy-M-d',),
			),
			array('target_dt','date','allowEmpty'=>!$this->showField('target_dt'),
				'format'=>array('yyyy/MM/dd','yyyy-MM-dd','yyyy/M/d','yyyy-M-d',),
			),
			array('email','validateEmail'),
			array('emailcc','validateEmailList'),
		);
		
		$extra = $this->rulesEx();
		return array_merge($orig, $extra);
	}
	
	protected function rulesEx() {
		return array();
	}
	
	public function validateEmail($attribute, $params){
		if ($this->format=='EMAIL') {
			$ev = new CEmailValidator();
			$ev->attributes = explode(',',$attribute);
			foreach ($params as $key=>$value) $ev->$key = $value;
			$ev->allowEmpty = false;
			$ev->validate($this);
		}
	}
	
	public function validateEmailList($attribute, $params){
		$ev = new CEmailValidator();
		$ev->attributes = explode(',',$attribute);
		foreach ($params as $key=>$value) $ev->$key = $value;
		$ev->allowEmpty = true;
		foreach ($ev->attributes as $field) {
			$rtn = true;
			$list = str_replace(',',';',$this->$field);
			$emails = explode(';',$list);
			if (is_array($emails)) {
				foreach ($emails as $email) {
					if (!empty($email)) $rtn = $ev->validateValue(trim($email));
					if (!$rtn) break;
				}
			} else {
				$rtn = (empty($list) || $ev->validateValue($list));
			}
			if (!$rtn) {
				$fldnames = $this->attributeLabels();
				$message = str_replace('{attribute}',$fldnames[$field],Yii::t('yii','{attribute} is not a valid email address.'));
				$this->addError($field,$message);
				break;
			}
		}
	}

	public function showField($name) {
		$a = explode(',',$this->fields);
		return empty($this->fields) || in_array($name, $a);
	}
	
	protected function queueItemEx() {
		return array();
	}
	
	public function addQueueItem() {
		$uid = Yii::app()->user->id;
		$bosses = Yii::app()->params['feedbackCcBoss'];
		$now = date("Y-m-d H:i:s");
		if (empty($rpt_array)) $rpt_array = array($this->id=>$this->name);
		$this->ccuser = (!empty($this->ccuser) && is_array($this->ccuser)) ? array_merge($this->ccuser, $bosses) : $bosses;
		$data = array(
					'RPT_ID'=>$this->id,
					'RPT_NAME'=>$this->name,
					'CITY'=>$this->city,
					'PAPER_SZ'=>$this->paper_sz,
					'FIELD_LST'=>$this->fields,
					'START_DT'=>General::toMyDate($this->start_dt),
					'END_DT'=>General::toMyDate($this->end_dt),
					'TARGET_DT'=>General::toMyDate($this->target_dt),
					'EMAIL'=>$this->email,
					'EMAILCC'=>$this->emailcc,
					'TOUSER'=>$this->touser,
					'CCUSER'=>json_encode($this->ccuser),
					'RPT_ARRAY'=>json_encode($rpt_array),
					'LANGUAGE'=>Yii::app()->language,
					'CITY_NAME'=>Yii::app()->user->city_name(),
					'YEAR'=>$this->year,
					'MONTH'=>$this->month,
				);
		$dataex = $this->queueItemEx();
		if (!empty($dataex)) $data = array_merge($data, $dataex);
		
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$sql = "insert into acc_queue (rpt_desc, req_dt, username, status, rpt_type)
						values(:rpt_desc, :req_dt, :username, 'P', :rpt_type)
					";
			$command=$connection->createCommand($sql);
			if (strpos($sql,':rpt_desc')!==false)
				$command->bindParam(':rpt_desc',$this->name,PDO::PARAM_STR);
			if (strpos($sql,':req_dt')!==false)
				$command->bindParam(':req_dt',$now,PDO::PARAM_STR);
			if (strpos($sql,':username')!==false)
				$command->bindParam(':username',$uid,PDO::PARAM_STR);
			if (strpos($sql,':rpt_type')!==false)
				$command->bindParam(':rpt_type',$this->format,PDO::PARAM_STR);
			$command->execute();
			$qid = Yii::app()->db->getLastInsertID();
	
			$sql = "insert into acc_queue_param (queue_id, param_field, param_value)
						values(:queue_id, :param_field, :param_value)
					";
			foreach ($data as $key=>$value) {
				$command=$connection->createCommand($sql);
				if (strpos($sql,':queue_id')!==false)
					$command->bindParam(':queue_id',$qid,PDO::PARAM_INT);
				if (strpos($sql,':param_field')!==false)
					$command->bindParam(':param_field',$key,PDO::PARAM_STR);
				if (strpos($sql,':param_value')!==false)
					$command->bindParam(':param_value',$value,PDO::PARAM_STR);
				$command->execute();
			}

			if ($this->multiuser) {
				$sql = "insert into swo_queue_user (queue_id, username)
						values(:queue_id, :username)
					";

				$command=$connection->createCommand($sql);
				if (strpos($sql,':queue_id')!==false)
					$command->bindParam(':queue_id',$qid,PDO::PARAM_INT);
				if (strpos($sql,':username')!==false)
					$command->bindParam(':username',$this->touser,PDO::PARAM_STR);
				$command->execute();
				
				if (!empty($this->ccuser) && is_array($this->ccuser)) {
					foreach ($this->ccuser as $user) {
						$command=$connection->createCommand($sql);
						if (strpos($sql,':queue_id')!==false)
							$command->bindParam(':queue_id',$qid,PDO::PARAM_INT);
						if (strpos($sql,':username')!==false)
							$command->bindParam(':username',$user,PDO::PARAM_STR);
						$command->execute();
					}
				}
			}
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
}
