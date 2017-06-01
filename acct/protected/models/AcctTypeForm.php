<?php

class AcctTypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $acct_type_desc;
	public $rpt_cat;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'acct_type_desc'=>Yii::t('code','Description'),
			'rpt_cat'=>Yii::t('code','Type'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('acct_type_desc','required'),
			array('id, rpt_cat','safe'), 
		);
	}

	public function retrieveData($index)
	{
		$sql = "select * from acc_account_type where id=$index";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$this->id = $row['id'];
				$this->acct_type_desc = $row['acct_type_desc'];
				$this->rpt_cat = $row['rpt_cat'];
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
				$sql = "delete from acc_account_type where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_account_type(
						acct_type_desc, rpt_cat, luu, lcu) values (
						:acct_type_desc, :rpt_cat, :luu, :lcu)";
				break;
			case 'edit':
				$sql = "update acc_account_type set 
					acct_type_desc = :acct_type_desc, 
					rpt_cat = :rpt_cat,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':acct_type_desc')!==false)
			$command->bindParam(':acct_type_desc',$this->acct_type_desc,PDO::PARAM_STR);
		if (strpos($sql,':rpt_cat')!==false)
			$command->bindParam(':rpt_cat',$this->rpt_cat,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

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
}
