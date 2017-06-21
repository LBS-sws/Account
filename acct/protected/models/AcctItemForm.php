<?php

class AcctItemForm extends CFormModel
{
	/* User Fields */
	public $code;
	public $name;
	public $item_type;
	public $acct_code;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'code'=>Yii::t('code','Code'),
			'name'=>Yii::t('code','Description'),
			'item_type'=>Yii::t('code','Type'),
			'acct_code'=>Yii::t('code','T3 Code'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('code, name, item_type, acct_code','required'),
			array('code','unique','allowEmpty'=>false,
					'attributeName'=>'code',
					'caseSensitive'=>false,
					'className'=>'AcctItem',
					'on'=>'new'
				),

		);
	}

	public function retrieveData($index)
	{
		$sql = "select * from acc_account_item where code='$index'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$this->code = $row['code'];
				$this->name = $row['name'];
				$this->item_type = $row['item_type'];
				$this->acct_code = $row['acct_code'];
				break;
			}
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveAcctType($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveAcctType(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_account_item where code = :code";
				break;
			case 'new':
				$sql = "insert into acc_account_item(
						code, name, item_type, acct_code, luu, lcu) values (
						:code, :name, :item_type, :acct_code, :luu, :lcu)";
				break;
			case 'edit':
				$sql = "update acc_account_item set 
					name = :name, 
					item_type = :item_type,
					luu = :luu
					where code = :code";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':code')!==false)
			$command->bindParam(':code',$this->code,PDO::PARAM_STR);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':item_type')!==false)
			$command->bindParam(':item_type',$this->item_type,PDO::PARAM_STR);
		if (strpos($sql,':acct_code')!==false)
			$command->bindParam(':acct_code',$this->acct_code,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}

	public function isReadOnly() {
		return ($this->scenario=='view');
	}
	
/*
	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.id from acc_account a where a.acct_type_id=".$index." limit 1";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			$rtn = true;
			break;
		}
		return $rtn;
	}
*/
}
