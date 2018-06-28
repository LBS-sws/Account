<?php

class DelegateForm extends CFormModel
{
	public $detail = array(
						array('delegated'=>'','uflag'=>'N',)
					);

	public function attributeLabels()
	{
		return array(
			'delegated'=>Yii::t('code','Delegated User'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()	{
		return array(
			array('detail','safe'), 
		);
	}

	public function retrieveData() {
		$suffix = Yii::app()->params['envSuffix'];
		$uid = Yii::app()->user->id;
		$sql = "select a.delegated from acc_delegation a
					where a.username='$uid'
				";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			$this->detail = array();
			foreach ($rows as $row) {
				$temp = array();
				$temp['delegated'] = $row['delegated'];
				$temp['uflag'] = 'N';
				$this->detail[] = $temp;
			}
		}
		return true;
	}
	
	public function saveData() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDelegated($connection);
			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveDelegated(&$connection) {
		$sql = "insert into acc_delegation
					(username, delegated, luu, lcu) 
				values 
					(:username, :delegated, :luu, :lcu)
				on duplicate key update
					luu = :luu
			";

		$uid = Yii::app()->user->id;

		foreach ($this->detail as $row) {
			switch ($row['uflag']) {
				case 'D':
					$sql = "delete from acc_delegation where username = :username and delegated = :delegated";
					break;
				case 'Y':
					$sql = "insert into acc_delegation
								(username, delegated, luu, lcu) 
							values 
								(:username, :delegated, :luu, :lcu)
							on duplicate key update
								luu = :luu
							";
					break;
			}
			if (!empty($row['delegated'])) {
				$command=$connection->createCommand($sql);
				if (strpos($sql,':username')!==false)
					$command->bindParam(':username',$uid,PDO::PARAM_STR);
				if (strpos($sql,':delegated')!==false)
					$command->bindParam(':delegated',$row['delegated'],PDO::PARAM_STR);
				if (strpos($sql,':luu')!==false)
					$command->bindParam(':luu',$uid,PDO::PARAM_STR);
				if (strpos($sql,':lcu')!==false)
					$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
				$command->execute();
			}
		}

		return true;
	}
	
	public function getUserList() {
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$sql = "select username, disp_name from security$suffix.sec_user where city='$city' and username<>'$uid' order by disp_name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		$rtn = array(
					''=>Yii::t('misc','-- None --'),
				);
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[$row['username']] = $row['disp_name'];
			}
		}
		return $rtn;
	}
}
