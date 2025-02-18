<?php
class ImpReceipt {
	public function getDbFields() {
		return array(
				'trans_dt'=>Yii::t('import','Trans. Date'),
				'year_month'=>Yii::t('import','Year/Month'),
				't3_doc_no'=>Yii::t('import','T3 Document No.'),
				'cust_code'=>Yii::t('import','Customer Code'),
				'cust_name'=>Yii::t('import','Customer Name'),
				'cust_full_name'=>Yii::t('import','Customer Full Name'),
				'detail'=>Yii::t('import','Detail'),
				'amount'=>Yii::t('import','Amount'),
				'method'=>Yii::t('import','Payment Method'),
				'item_source'=>Yii::t('import','Item Source'),
				'remarks1'=>Yii::t('import','Remarks 1'),
				'remarks2'=>Yii::t('import','Remarks 2'),
				'coa'=>Yii::t('import','COA'),
			);
	}
	
	public function getDefaultMapping() {
	//	Db Field Name => Excel Column No.
		return array(
				'trans_dt'=>0,
				'year_month'=>1,
				't3_doc_no'=>2,
				'cust_code'=>3,
				'cust_name'=>4,
				'cust_full_name'=>5,
				'detail'=>6,
				'amount'=>7,
				'method'=>8,
				'item_source'=>9,
//				'remarks1'=>10,
				'coa'=>10,
				'remarks2'=>11,
			);
	}
	
	public function validateData($data) {
		$name = $this->getDbFields();
		$connection = Yii::app()->db;
		$dt = $this->convertExcelDate($data['trans_dt']);
		$rtn = !empty($dt)? '' : $name['trans_dt'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= $this->validateDate($dt,'Y-m-d') ? '' : $name['trans_dt'].' '.Yii::t('import','is not valid').' /';
		$rtn .= !empty($data['t3_doc_no']) 
				? (strlen($data['t3_doc_no'])>100 ? $name['t3_doc_no'].' '.Yii::t('import','is too long').' /' : '')
				: $name['t3_doc_no'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['amount']) ? '' : $name['amount'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= is_numeric($data['amount']) ? '' : $name['amount'].' '.Yii::t('import','is not valid').' /';
		$rtn .= !empty($data['method']) ? '' : $name['method'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['item_source']) ? '' : $name['item_source'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= $data['item_source']=='QT99' || !empty($data['cust_code']) ? '' : $name['cust_code'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= $data['item_source']=='QT99' || !empty($data['cust_full_name']) ? '' : $name['cust_full_name'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= empty($data['cust_code']) || $this->getCustomerId($connection, $data['cust_code'])!=0 ? '' : $name['cust_code'].' '.Yii::t('import','cannot be found in system').' /';
		$rtn .= !empty($data['detail']) && strlen($data['detail'])>1000 ? $name['detail'].' '.Yii::t('import','is too long').' /' : '';
//		$rtn .= !empty($data['remarks1']) && strlen($data['remarks1'])>1000 ? $name['remarks1'].' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['remarks2']) && strlen($data['remarks2'])>1000 ? $name['remakrs2'].' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['coa'])? '' : $name['coa'].' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['coa']) && $this->existCOA($connection, $data['coa'],$data['city']) ? '' : $name['coa'].' '.Yii::t('import','is not valid').' /';
		return empty($rtn) ? '' : Yii::t('import','ERROR').'- /'.Yii::t('import','Row No.').': '.$data['excel_row'].' /'.$rtn;
	}
	
	
	public function importData(&$connection, $data) {
		$trans_dt = $this->convertExcelDate($data['trans_dt']);
		$trans_type_code = ($data['method']=='1001') ? 'CASHIN' : 'BANKIN';
		$acct_id = ($trans_type_code=='CASHIN') 
				? $this->getDefAccount($connection, $trans_type_code, $data['city'])
				: $this->getAccount($connection, $data['coa'], $data['city']);
//		$acct_id = $this->getDefAccount($connection, $trans_type_code, $data['city']);
//		$trans_desc = $data['remarks1'];
		$trans_desc = '';
		$amount = General::toMyNumber($data['amount']);
		$status = 'A';
		$payer_type = ($data['item_source']=='QT99') ? 'O' : 'C';
		$payer_id = ($data['item_source']=='QT99') ? 0 : $this->getCustomerId($connection, $data['cust_code']);
		$payer_name = empty($data['cust_full_name']) ? $data['cust_name'] : $data['cust_full_name'];
		$cheque_no = '';
		$invoice_no = '';
		$handle_staff = 0;
		$handle_staff_name = '';
		$acct_code = $data['item_source'];
		$item_code = ($data['item_source']=='QT99')
				? ($data['method']=='1001' ? 'CI0016' : 'BI0002')
				: ($data['method']=='1001' ? 'CI0001' : 'BI0001');
		$year_no = empty($data['year_month']) ? '' : '20'.substr($data['year_month'],0,2);
		$month_no = empty($data['year_month']) ? '' : substr($data['year_month'],-2);
		$united_inv_no = '';
		$int_fee = 'N';
		$reason = '';
		$req_ref_no = '';
		$t3_doc_no = $data['t3_doc_no'];
		$detail = $data['detail'];
		$remarks = $data['remarks2'];
		
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		
		$sql = "select trans_id from acc_trans_t3 where t3_doc_no=:t3_doc_no";
		$command=$connection->createCommand($sql);
		$command->bindParam(':t3_doc_no',$data['t3_doc_no'],PDO::PARAM_STR);
		$row = $command->queryRow();
        $exist = ($row!==false);
		//2024年12月2日12:52:10 增加日期判断
        if($exist){
            // 需要查询日期
            $dateRow = $connection->createCommand()->select("id")->from("acc_trans")
                ->where("id=:id and DATE_FORMAT(trans_dt,'%Y-%m-%d')='{$trans_dt}'",array(":id"=>$row['trans_id']))
                ->queryRow();
            if(!$dateRow){//如果时间不一致，强制新增
                $exist=false;
            }
        }
		$id = (!$exist) ? 0 : $row['trans_id'];
		
		$action = (!$exist) ? Yii::t('import','INSERT') : Yii::t('import','UPDATE');
		$sql = (!$exist)
				? "insert into acc_trans 
						(trans_dt, trans_type_code, acct_id, trans_desc, amount, status, city, lcu, luu)
					values
						(:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, :status, :city, :uid, :uid)
				"
				: "update acc_trans 
					set trans_dt = :trans_dt, 
						trans_type_code = :trans_type_code, 
						acct_id = :acct_id, 
						trans_desc = :trans_desc, 
						amount = :amount, 
						status = :status, 
						lcu = :uid, 
						luu = :uid
					where
						id = :id and city = :city 
				"
				;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$id,PDO::PARAM_INT);
		if (strpos($sql,':trans_dt')!==false) 
			$command->bindParam(':trans_dt',$trans_dt,PDO::PARAM_STR);
		if (strpos($sql,':trans_type_code')!==false)
			$command->bindParam(':trans_type_code',$trans_type_code,PDO::PARAM_STR);
		if (strpos($sql,':acct_id')!==false)
			$command->bindParam(':acct_id',$acct_id,PDO::PARAM_INT);
		if (strpos($sql,':trans_desc')!==false)
			$command->bindParam(':trans_desc',$data['trans_desc'],PDO::PARAM_STR);
		if (strpos($sql,':amount')!==false)
			$command->bindParam(':amount',$amount,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$status,PDO::PARAM_STR);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$data['uid'],PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$data['city'],PDO::PARAM_LOB);
		$command->execute();
		
		if (!$exist) {
			$id = Yii::app()->db->getLastInsertID();

            $t3Row = $connection->createCommand()->select("t3_doc_no")->from("acc_trans_t3")
                ->where("t3_doc_no=:t3_doc_no",array(":t3_doc_no"=>$t3_doc_no))
                ->queryRow();
            if(!$t3Row){
                $sql = "insert into acc_trans_t3 (t3_doc_no, trans_id)
					values (:t3_doc_no, :trans_id)
				";
                $command=$connection->createCommand($sql);
                if (strpos($sql,':trans_id')!==false)
                    $command->bindParam(':trans_id',$id,PDO::PARAM_INT);
                if (strpos($sql,':t3_doc_no')!==false)
                    $command->bindParam(':t3_doc_no',$t3_doc_no,PDO::PARAM_STR);
                $command->execute();
            }
		}
		
		$dynfields = array(
						'payer_type'=>$payer_type,
						'payer_id'=>$payer_id,
						'payer_name'=>$payer_name,
						'cheque_no'=>$cheque_no,
						'invoice_no'=>$invoice_no,
						'handle_staff'=>$handle_staff,
						'handle_staff_name'=>$handle_staff_name,
						'acct_code'=>$acct_code,
						'item_code'=>$item_code,
						'year_no'=>$year_no,
						'month_no'=>$month_no,
						'united_inv_no'=>$united_inv_no,
						'int_fee'=>$int_fee,
						'reason'=>$reason,
						'req_ref_no'=>$req_ref_no,
						't3_doc_no'=>$t3_doc_no,
						'detail'=>$detail,
						'remarks'=>$remarks,
				);
		
		$sql = "insert into acc_trans_info
					(trans_id, field_id, field_value, luu, lcu) 
				values 
					(:id, :field_id, :field_value, :uid, :uid)
				on duplicate key update
					field_value = :field_value, luu = :uid
		";

		foreach ($dynfields as $field=>$value) {
			$command=$connection->createCommand($sql);
			if (strpos($sql,':id')!==false)
				$command->bindParam(':id',$id,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false) 
				$command->bindParam(':field_id',$field,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false)
				$command->bindParam(':field_value',$value,PDO::PARAM_STR);
			if (strpos($sql,':uid')!==false)
				$command->bindParam(':uid',$data['uid'],PDO::PARAM_STR);
			$command->execute();
		}
		
		return $action.'- /'.Yii::t('import','Row No.').': '.$data['excel_row']
			.' /'.Yii::t('import','Trans. Date').': '.$trans_dt
			.' /'.Yii::t('import','T3 Document No.').': '.$t3_doc_no
			.' /'.Yii::t('import','Amount').': '.$amount
			.' /'.Yii::t('import','City').': '.$data['city']
			.' /'.Yii::t('import','User').': '.$data['uid']
			.' /id: '.$id
			;
	}
	
	protected function getDefAccount(&$connection, $type, $city) {
		$sql = "select acct_id from acc_trans_type_def where trans_type_code='$type' and city='$city'";
		$row = $connection->createCommand($sql)->queryRow();
		return ($row===false) ? 0 : $row['acct_id'];
	}
	
	protected function getAccount(&$connection, $coa, $city) {
		$sql = "select id from acc_account where coa='$coa' and city='$city'";
		$row = $connection->createCommand($sql)->queryRow();
		return ($row===false) ? 0 : $row['id'];
	}

	protected function getCustomerId(&$connection, $code) {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$sql = "select id from swoper$suffix.swo_company where code='$code'";
		$row = $connection->createCommand($sql)->queryRow();
		return ($row===false) ? 0 : $row['id'];
	}
	
	protected function validateDate($date, $format = 'Y-m-d H:i:s') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	
	protected function existCOA(&$connection, $value, $city) {
		$sql = "select coa from acc_account where coa='$value' and (city='$city' or city='99999') limit 1";
		$row = $connection->createCommand($sql)->queryRow();
		return ($row!==false);
	}
	
	protected function convertExcelDate($value) {
		$uxdate = ($value - 25569) * 86400;
		return gmdate('Y-m-d', $uxdate);
	}
}
?>