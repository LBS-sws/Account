<?php

class TransTypeForm extends CFormModel
{
	/* User Fields */
	public $trans_type_code;
	public $trans_type_desc;
	public $adj_type;
	public $trans_cat;
	public $counter_type;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'trans_type_code'=>Yii::t('code','Code'),
			'trans_type_desc'=>Yii::t('code','Description'),
			'adj_type'=>Yii::t('code','Adjustment'),
			'trans_cat'=>Yii::t('code','Type'),
			'counter_type'=>Yii::t('code','Counter Type'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('trans_type_code, trans_type_desc, adj_type','required'),
			array('trans_type_code','validateCode'),
			array('trans_cat, counter_type','safe'), 
		);
	}

	public function validateCode($attribute, $params) {
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? " and city='".Yii::app()->user->city()."' " : '');
		$code = $this->$attribute;
		if (!empty($code) && $this->scenario=='new') {
			$sql = "select trans_type_code from acc_trans_type where trans_type_code='$code' $citystr";
			if (Yii::app()->db->createCommand($sql)->queryRow()!==false) {
				$this->addError($attribute, Yii::t('code','Code')." '".$code."' ".Yii::t('app','already used'));
			}
		}
	}

	public function retrieveData($index)
	{
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? " and city='".Yii::app()->user->city()."' " : '');
		$sql = "select * from acc_trans_type where trans_type_code='$index' $citystr";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0)
		{
			foreach ($rows as $row) {
				$this->trans_type_code = $row['trans_type_code'];
				$this->trans_type_desc = $row['trans_type_desc'];
				$this->adj_type = $row['adj_type'];
				$this->trans_cat = $row['trans_cat'];
				$this->counter_type = $row['counter_type'];
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
			$this->saveTransType($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveTransType(&$connection)
	{
		$version = Yii::app()->params['version'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = $version=='intl'
					? "delete from acc_trans_type where trans_type_code = :trans_type_code and city=:city"
					: "delete from acc_trans_type where trans_type_code = :trans_type_code";
				break;
			case 'new':
				$sql = $version=='intl'
					? "insert into acc_trans_type(
						trans_type_code, trans_type_desc, adj_type, trans_cat, counter_type, luu, lcu, city) values (
						:trans_type_code, :trans_type_desc, :adj_type, :trans_cat, :counter_type, :luu, :lcu, :city)"
					: "insert into acc_trans_type(
						trans_type_code, trans_type_desc, adj_type, trans_cat, counter_type, luu, lcu) values (
						:trans_type_code, :trans_type_desc, :adj_type, :trans_cat, :counter_type, :luu, :lcu)";
				break;
			case 'edit':
				$sql = $version=='intl'
					? "update acc_trans_type set 
						trans_type_desc = :trans_type_desc, 
						adj_type = :adj_type, 
						trans_cat = :trans_cat,
						counter_type = :counter_type,
						luu = :luu
						where trans_type_code = :trans_type_code and city = :city";
					: "update acc_trans_type set 
						trans_type_desc = :trans_type_desc, 
						adj_type = :adj_type, 
						trans_cat = :trans_cat,
						counter_type = :counter_type,
						luu = :luu
						where trans_type_code = :trans_type_code";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':trans_type_code')!==false)
			$command->bindParam(':trans_type_code',$this->trans_type_code,PDO::PARAM_INT);
		if (strpos($sql,':trans_type_desc')!==false)
			$command->bindParam(':trans_type_desc',$this->trans_type_desc,PDO::PARAM_STR);
		if (strpos($sql,':adj_type')!==false)
			$command->bindParam(':adj_type',$this->adj_type,PDO::PARAM_STR);
		if (strpos($sql,':trans_cat')!==false)
			$command->bindParam(':trans_cat',$this->trans_cat,PDO::PARAM_STR);
		if (strpos($sql,':counter_type')!==false)
			$command->bindParam(':counter_type',$this->counter_type,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false) {
			$city = Yii::app()->user->city();
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		}
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}

	public function isOccupied($index) {
		$rtn = false;
		$city = Yii::app()->user->city();
		$version = Yii::app()->params['version'];
		$sql = $version=='intl'
			? "select a.trans_type_code from acc_trans_type a where a.trans_type_code='$index' and a.city='$city' limit 1"
			: "select a.trans_type_code from acc_trans_type a where a.trans_type_code='$index' limit 1";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			$rtn = true;
			break;
		}
		return $rtn;
	}
}
