<?php

class BonusForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $rpt_type;
	public $type_group;
	public $city;
    public $sum;
    public $sums;
    public $year;
    public $month;
    public $spanning;
    public $otherspanning;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('app','Description'),
			'rpt_type'=>Yii::t('app','Report Category'),
			'city'=>Yii::t('app','City'),
			'type_group'=>Yii::t('app','Type'),
            'sum'=>Yii::t('app','Sum'),
            'sums'=>Yii::t('app','Sums'),
            'year'=>Yii::t('app','Year'),
            'month'=>Yii::t('app','Month'),
            'money'=>Yii::t('app','Money'),


		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('','required'),
			array('id,rpt_type,sums,spanning,otherspanning','safe'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_performance where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->year = $row['year'];
			$this->month = $row['month'];
            $this->sum = $row['sum'];
            $this->sums = $row['sums'];
            $this->spanning = $row['spanning'];
            $this->otherspanning = $row['otherspanning'];
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_performance where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_performance(
						name, rpt_type, type_group, city, lcu, luu) values (
						:name, :rpt_type, :type_group, :city, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_performance set 
					sum = :sum, 	
					sums = :sums, 	
					spanning = :spanning,
					otherspanning = :otherspanning,		  
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $sum=$_POST['PerformanceForm']['sum'];
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':sum')!==false)
			$command->bindParam(':sum',$sum,PDO::PARAM_STR);
        if (strpos($sql,':sums')!==false)
            $command->bindParam(':sums',$this->sums,PDO::PARAM_STR);
        if (strpos($sql,':spanning')!==false)
            $command->bindParam(':spanning',$this->spanning,PDO::PARAM_STR);
        if (strpos($sql,':otherspanning')!==false)
            $command->bindParam(':otherspanning',$this->otherspanning,PDO::PARAM_STR);
		if (strpos($sql,':type_group')!==false)
			$command->bindParam(':type_group',$this->type_group,PDO::PARAM_INT);
		if (strpos($sql,':rpt_type')!==false)
			$command->bindParam(':rpt_type',$this->rpt_type,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	public function getCityList() {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select code, name from security$suffix.sec_city order by name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		$rtn = array('99999'=>Yii::t('sales','All'));
		foreach ($rows as $row) {
			$rtn[$row['code']] = $row['name'];
		}
		return $rtn;
	}

	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.id from sal_visit a where a.cust_type=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}
}
