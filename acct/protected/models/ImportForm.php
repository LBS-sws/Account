<?php

class ImportForm extends CFormModel
{
	public $import_type;
	public $import_file;
	public $import_file_name;
	public $city;
	protected $choice = array(
						'customer'=>'ImpCustomer',
						'supplier'=>'ImpSupplier',
						'receipt'=>'ImpReceipt',
					);

	public function attributeLabels()
	{
		return array(
			'import_type'=>Yii::t('code','Import Type'),
			'import_file'=>Yii::t('code','Import File'),
		);
	}

	public function rules()
	{
		return array(
			array('import_type, import_file','required'),
			array('import_file','file','types'=>'xls,xlsx','allowEmpty'=>false),
		);
	}
	
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			if ($this->city!='99999') $this->saveAccount($connection);
			$this->saveTrans($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	public function addQueueItem() {
		$uid = Yii::app()->user->id;
		$city = Yii::app()->user->city();
		$now = date("Y-m-d H:i:s");
		
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$sql = "insert into acc_import_queue 
						(import_type, req_dt, username, status, class_name)
					values
						(:import_type, :req_dt, :username, 'P', :class_name)
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
