<?php

class ImportForm extends CFormModel
{
	public $queue_id;
	public $import_type;
	public $import_file;
	public $file_content;
	public $file_type;
	public $mapping = array();
	public $city;
	protected $choice = array(
						'Customer'=>'ImpCustomer',
						'Supplier'=>'ImpSupplier',
						'Receipt'=>'ImpReceipt',
					);

	public function attributeLabels()
	{
		return array(
			'import_type'=>Yii::t('import','Import Type'),
			'import_file'=>Yii::t('import','Import File'),
			'file_content'=>Yii::t('import','Import File'),
		);
	}

	public function rules()
	{
		return array(
			array('import_type, city','required'),
			array('file_content, file_type, mapping, queue_id','safe'),
// Enable direct submit without field mapping
			array('import_file','file','types'=>'xls,xlsx','allowEmpty'=>false),
		);
	}
	
	public function init() {
		$this->queue_id = 0;
		$this->city = Yii::app()->user->city();
	}
	
	public function getImportTypeList() {
		$rtn = array();
		foreach ($this->choice as $key=>$value) {
			$rtn[$key] = Yii::t('import',$key);
		}
		return $rtn;
	}
	
	public function setMapping() {
		$modelname = $this->choice[$this->import_type];
		$model = new $modelname();
		$map = $model->getDefaultMapping();
		foreach ($map as $field=>$column) {
			$this->mapping[] = array('dbfieldid'=>$field, 'filefield'=>$column);
		}
	}
	
	public function genMappingList($fileFields, $queueId) {
		$rtn = '';
		
		$modelname = $this->choice[$this->import_type];
		$model = new $modelname();
		$dbFields = $model->getDbFields();
		$list0 = array(-1=>Yii::t('misc','-- None --'));
		$list1 = $fileFields;
		$fileFields = $list0 + $list1;
		
		$i = 0;
		foreach ($dbFields as $key=>$field) {
			$tempid = ($i==0 ? TbHtml::hiddenField('temp_qid',$queueId) : '');
			
			$id = 'ImportForm_mapping_'.$i.'_dbfieldid';
			$name = 'ImportForm[mapping]['.$i.'][dbfieldid]';
			$fld_db_id = TbHtml::hiddenField($name, $key, array('id'=>$id));

			$id = 'ImportForm_mapping_'.$i.'_dbfieldname';
			$name = 'ImportForm[mapping]['.$i.'][dbfieldname]';
			$fld_db_name = TbHtml::textField($name, $field, array('readonly'=>true,'id'=>$id));
			
			$id = 'ImportForm_mapping_'.$i.'_filefield';
			$name = 'ImportForm[mapping]['.$i.'][filefield]';
			$fld_file = TbHtml::dropDownList($name, '-1', $fileFields, array('id'=>$id));
			
			$rtn .= "<tr><td>$tempid $fld_db_id $fld_db_name</td><td>$fld_file</td></tr>";
			
			$i++;
		}
		
		return $rtn;
	}
	
	public function activateQueueItem() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$data = array(
					'CITY'=>$this->city,
					'MAPPING'=>json_encode($this->mapping),
					'LOG'=>'',
				);
	
			$sql = "insert into acc_import_queue_param (queue_id, param_field, param_value)
						values(:queue_id, :param_field, :param_value)
					";
			foreach ($data as $key=>$value) {
				$command=$connection->createCommand($sql);
				if (strpos($sql,':queue_id')!==false)
					$command->bindParam(':queue_id',$this->queue_id,PDO::PARAM_INT);
				if (strpos($sql,':param_field')!==false)
					$command->bindParam(':param_field',$key,PDO::PARAM_STR);
				if (strpos($sql,':param_value')!==false)
					$command->bindParam(':param_value',$value,PDO::PARAM_STR);
				$command->execute();
			}

			$sql = "update acc_import_queue 
					set status = 'P'
					where id = :id
					";
			$command=$connection->createCommand($sql);
			if (strpos($sql,':id')!==false)
				$command->bindParam(':id',$this->queue_id,PDO::PARAM_INT);
			$command->execute();
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	public function addItemToQueue() {
		$qid = 0;
		$uid = Yii::app()->user->id;
		$now = date("Y-m-d H:i:s");
		
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$sql = "insert into acc_import_queue 
						(import_type, req_dt, username, status, class_name, file_type, file_content)
					values
						(:import_type, :req_dt, :username, 'N', :class_name, :file_type, :file_content)
					";
			$command=$connection->createCommand($sql);
			if (strpos($sql,':import_type')!==false)
				$command->bindParam(':import_type',$this->import_type,PDO::PARAM_STR);
			if (strpos($sql,':req_dt')!==false)
				$command->bindParam(':req_dt',$now,PDO::PARAM_STR);
			if (strpos($sql,':username')!==false)
				$command->bindParam(':username',$uid,PDO::PARAM_STR);
			if (strpos($sql,':class_name')!==false)
				$command->bindParam(':class_name',$this->choice[$this->import_type],PDO::PARAM_STR);
			if (strpos($sql,':file_type')!==false)
				$command->bindParam(':file_type',$this->file_type,PDO::PARAM_STR);
			if (strpos($sql,':file_content')!==false)
				$command->bindParam(':file_content',$this->file_content,PDO::PARAM_LOB);
			$command->execute();
			$qid = Yii::app()->db->getLastInsertID();
	
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
		
		return $qid;
	}
}
