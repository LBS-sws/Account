<?php
class ImpSupplier {
	public function getDbFields() {
		return array(
				'code'=>Yii::t('import','Code'),
				'name'=>Yii::t('import','Name'),
				'full_name'=>Yii::t('import','Full Name'),
				'cont_name'=>Yii::t('import','Contact Name'),
				'cont_phone'=>Yii::t('import','Contact Phone'),
				'address'=>Yii::t('import','Address'),
				'bank'=>Yii::t('import','Bank'),
				'acct_no'=>Yii::t('import','Account No.'),
			);
	}
	
	public function getDefaultMapping() {
	//	Db Field Name => Excel Column No.
		return array(
				'code'=>0,
				'name'=>1,
				'cont_name'=>2,
				'cont_phone'=>3,
				'address'=>4,
				'bank'=>5,
				'acct_no'=>6,
				'full_name'=>7,
			);
	}
	
	public function validateData($data) {
		$rtn = !empty($data['code'])? '' : Yii::t('import','Code').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['name']) ? '' : Yii::t('import','Name').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['city']) ? '' : Yii::t('import','City').' '.Yii::t('import','cannot be blank').' /';
		return empty($rtn) ? '' : Yii::t('import','ERROR').'- /'.Yii::t('import','Row No.').': '.$data['excel_row'].' /'.$rtn;
	}

	public function importData(&$connection, $data) {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		
		$sql = "select id from swoper$suffix.swo_supplier where code=:code and city=:city";
		$command=$connection->createCommand($sql);
		$command->bindParam(':code',$data['code'],PDO::PARAM_STR);
		$command->bindParam(':city',$data['city'],PDO::PARAM_STR);
		$row = $command->queryRow();
		
		$action = ($row===false) ? Yii::t('import','INSERT') : Yii::t('import','UPDATE');
		$sql = ($row===false)
				? "insert into swoper$suffix.swo_supplier 
						(code, name, full_name, cont_name, cont_phone, address, bank, acct_no, city, lcu, luu)
					values
						(:code, :name, :full_name, :cont_name, :cont_phone, :address, :bank, :acct_no, :city, :uid, :uid)
				"
				: "update swoper$suffix.swo_supplier 
					set name = :name, 
						full_name = :full_name, 
						cont_name = :cont_name, 
						cont_phone = :cont_phone, 
						address = :address, 
						bank = :bank, 
						acct_no = :acct_no, 
						lcu = :uid, 
						luu = :uid
					where
						code = :code and city = :city 
				"
				;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':code')!==false)
			$command->bindParam(':code',$data['code'],PDO::PARAM_STR);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$data['name'],PDO::PARAM_STR);
		if (strpos($sql,':full_name')!==false)
			$command->bindParam(':full_name',$data['full_name'],PDO::PARAM_STR);
		if (strpos($sql,':cont_name')!==false)
			$command->bindParam(':cont_name',$data['cont_name'],PDO::PARAM_STR);
		if (strpos($sql,':cont_phone')!==false)
			$command->bindParam(':cont_phone',$data['cont_phone'],PDO::PARAM_STR);
		if (strpos($sql,':address')!==false)
			$command->bindParam(':address',$data['address'],PDO::PARAM_STR);
		if (strpos($sql,':bank')!==false)
			$command->bindParam(':bank',$data['bank'],PDO::PARAM_STR);
		if (strpos($sql,':acct_no')!==false)
			$command->bindParam(':acct_no',$data['acct_no'],PDO::PARAM_STR);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$data['uid'],PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$data['city'],PDO::PARAM_LOB);
		$command->execute();
		$id = Yii::app()->db->getLastInsertID();
		return $action.'- /'.Yii::t('import','Row No.').': '.$data['excel_row']
			.' /'.Yii::t('import','Code').': '.$data['code']
			.' /'.Yii::t('import','Name').': '.$data['name']
			.' /'.Yii::t('import','City').': '.$data['city']
			.' /'.Yii::t('import','User').': '.$data['uid']
			;
	}}
?>