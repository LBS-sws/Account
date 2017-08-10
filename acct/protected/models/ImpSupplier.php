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
				'tax_reg_no'=>Yii::t('import','Taxpayer No.'),
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
				'tax_reg_no'=>8,
			);
	}
	
	public function validateData($data) {
		$rtn = !empty($data['code'])
				? (strlen($data['code'])>20 ? Yii::t('import','Code').' '.Yii::t('import','is too long').' /' : '') 
				: Yii::t('import','Code').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['name']) 
				? (strlen($data['name'])>1000 ? Yii::t('import','Name').' '.Yii::t('import','is too long').' /' : '') 
				: Yii::t('import','Name').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['full_name']) && strlen($data['full_name'])>1000 ? Yii::t('import','Full Name').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['cont_name']) && strlen($data['cont_name'])>100 ? Yii::t('import','Contact Name').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['cont_phone']) && strlen($data['cont_phone'])>30 ? Yii::t('import','Contact Phone').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['address']) && strlen($data['address'])>1000 ? Yii::t('import','Address').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['tax_reg_no']) && strlen($data['tax_reg_no'])>100 ? Yii::t('import','Taxpayer No.').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['bank']) && strlen($data['bank'])>255 ? Yii::t('import','Bank').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['acct_no']) && strlen($data['acct_no'])>255 ? Yii::t('import','Account No.').' '.Yii::t('import','is too long').' /' : '';
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
						(code, name, full_name, tax_reg_no, cont_name, cont_phone, address, bank, acct_no, city, lcu, luu)
					values
						(:code, :name, :full_name, :tax_reg_no, :cont_name, :cont_phone, :address, :bank, :acct_no, :city, :uid, :uid)
				"
				: "update swoper$suffix.swo_supplier 
					set name = :name, 
						full_name = :full_name, 
						tax_reg_no = :tax_reg_no, 
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
		if (strpos($sql,':tax_reg_no')!==false)
			$command->bindParam(':tax_reg_no',$data['tax_reg_no'],PDO::PARAM_STR);
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