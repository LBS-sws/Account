<?php

class ApproverForm extends CFormModel
{
	/* User Fields */
	public $city;
	public $regionSuper;
	public $regionMgrA;
	public $regionMgr;
	public $regionHeight;
	public $regionDirectorA;
	public $regionDirector;
	public $regionHead;
	public $dynfields = array(
					'regionSuper',
					'regionMgrA',
					'regionMgr',
					'regionHeight',//高级总经理
					'regionDirectorA',
					'regionDirector',
					'regionHead',
				);

	public function attributeLabels()
	{
		return array(
			'regionSuper'=>Yii::t('code','Region Supervisor'),
			'regionMgrA'=>Yii::t('code','Region A.Manager'),
			'regionMgr'=>Yii::t('code','Region Manager'),
			'regionHeight'=>Yii::t('code','Region Height'),
			'regionDirectorA'=>Yii::t('code','Region A.Director'),
			'regionDirector'=>Yii::t('code','Region Director'),
			'regionHead'=>Yii::t('code','Region Head'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()	{
		return array(
			array('city, regionSuper, regionMgrA, regionMgr,regionHeight, regionDirectorA, regionDirector, regionHead','safe'),
		);
	}

	public function retrieveData() {
		$city = Yii::app()->user->city();
		$this->city = $city;
		$sql = "select approver_type, username from acc_approver where city='$city'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$field = $row['approver_type'];
				$this->$field = $row['username'];
			}
		}
		return true;
	}
	
	public function saveData() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveApprover($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveApprover(&$connection) {
		$sql = "insert into acc_approver
					(city, approver_type, username, luu, lcu) 
				values 
					(:city, :approver_type, :username, :luu, :lcu)
				on duplicate key update
					username = :username, luu = :luu
			";

		$uid = Yii::app()->user->id;

		foreach ($this->dynfields as $field) {
			$command=$connection->createCommand($sql);
			if (strpos($sql,':city')!==false)
				$command->bindParam(':city',$this->city,PDO::PARAM_STR);
			if (strpos($sql,':approver_type')!==false)
				$command->bindParam(':approver_type',$field,PDO::PARAM_STR);
			if (strpos($sql,':username')!==false)
				$command->bindParam(':username',$this->$field,PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			$command->execute();
		}

		return true;
	}
}
