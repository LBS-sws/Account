<?php

class TransTypeDefForm extends CFormModel
{
	/* User Fields */
	public $trans_type_code;
	public $trans_type_desc;
	public $trans_cat;
	public $acct_id;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'trans_type_desc'=>Yii::t('code','Trans. Type'),
			'trans_cat'=>Yii::t('code','Type'),
			'acct_id'=>Yii::t('code','Account'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules() {
		return array(
			array('trans_type_code','required'),
			array('trans_type_desc, trans_cat, acct_id','safe'), 
		);
	}

	public function retrieveData($code, $city) {
		$citylist = Yii::app()->user->city_allow();
		if (strpos($citylist,$city)!==false) {
			$sql = "select a.*, b.acct_id 
					from acc_trans_type a 
					left outer join acc_trans_type_def b on b.trans_type_code=a.trans_type_code and b.city='$city'
					where a.trans_type_code='$code'";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				foreach ($rows as $row) {
					$this->trans_type_code = $row['trans_type_code'];
					$this->trans_type_desc = $row['trans_type_desc'];
					$this->trans_cat = $row['trans_cat'];
					$this->acct_id = empty($row['acct_id']) ? 0 : $row['acct_id'];
					break;
				}
				return true;
			} else {
				return false;
			}	
		} else {
			return false;
		}
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveTransTypeDef($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveTransTypeDef(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
				$sql = "insert into acc_trans_type_def 
							(trans_type_code, city, acct_id, lcu, luu)
						values
							(:trans_type_code, :city, :acct_id, :lcu, :luu)
						on duplicate key update
							acct_id = :acct_id,
							luu = :luu
					";
				break;
		}

		$uid = Yii::app()->user->id;
		$city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':trans_type_code')!==false)
			$command->bindParam(':trans_type_code',$this->trans_type_code,PDO::PARAM_INT);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':acct_id')!==false)
			$command->bindParam(':acct_id',$this->acct_id,PDO::PARAM_INT);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}

	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.trans_type_code from acc_trans_type a where a.trans_type_code='$index' limit 1";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			$rtn = true;
			break;
		}
		return $rtn;
	}
}
