<?php

class AccountForm extends CFormModel
{
	public $id;
	public $acct_type_id;
	public $acct_no;
	public $acct_name;
	public $bank_name;
	public $remarks;
	public $open_bal;
	public $open_dt;
	public $city;
	public $trans_city;
	public $coa;
	public $status;

    public $jd_set = array();
    public static $jd_set_list=array(
        //array("field_id"=>"jd_acct_code","field_type"=>"text","field_name"=>"jd acct code"),
    );

	public function init() {
		$this->city = Yii::app()->user->city();
	}
	
	public function attributeLabels()
	{
		return array(
			'acct_type_id'=>Yii::t('code','Account Type'),
			'acct_no'=>Yii::t('code','Account No.'),
			'acct_name'=>Yii::t('code','Account Name'),
			'bank_name'=>Yii::t('code','Bank'),
			'remarks'=>Yii::t('code','Remarks'),
			'open_bal'=>Yii::t('code','Open Balance'),
			'open_dt'=>Yii::t('code','Balance Date'),
			'coa'=>Yii::t('code','COA'),
			'status'=>Yii::t('misc','Display'),
		);
	}

	public function rules()
	{
		return array(
			array('acct_type_id, coa, open_bal, open_dt, status','required'),
			array('coa','validateCoa'),
			array('jd_set,id, acct_no, acct_name, bank_name, remarks, city, trans_city','safe'),
		);
	}

	public function validateCoa($attribute, $params) {
		if ($this->id==1 || $this->id==2) return;
		$coa = $this->coa;
		$city = $this->city; //Yii::app()->user->city();
		$sql = "select id from acc_account where coa='$coa' and city='$city' limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			if ($this->id != $row['id']) $this->addError($attribute, Yii::t('code','This COA already exists'));
		}
	}
	
	public function retrieveData($index, $city)
	{
		$citylist = Yii::app()->user->city_allow();
		$sql = "select a.*, b.trans_dt, b.amount, b.city as trans_city  
				from acc_account a 
				left outer join acc_trans b on a.id=b.acct_id and b.trans_type_code='OPEN'
				and b.city='$city'
				where a.id=$index and (a.city in ($citylist) or a.city='99999')
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$this->id = $row['id'];
				$this->acct_type_id = $row['acct_type_id'];
				$this->acct_no = $row['acct_no'];
				$this->acct_name = $row['acct_name'];
				$this->bank_name = $row['bank_name'];
				$this->remarks = $row['remarks'];
				$this->open_bal = $row['amount'];
				$this->open_dt = General::toDate($row['trans_dt']);
				$this->coa = $row['coa'];
				$this->city = $row['city'];
				$this->trans_city = $row['trans_city'];
				$this->status = $row['status'];

                $setRows = Yii::app()->db->createCommand()->select("field_id,field_value")
                    ->from("acc_send_set_jd")->where("table_id=:table_id and set_type='account'",array(":table_id"=>$index))->queryAll();
                $setList = array();
                foreach ($setRows as $setRow){
                    $setList[$setRow["field_id"]] = $setRow["field_value"];
                }
                $this->jd_set=array();
                foreach (self::$jd_set_list as $item){
                    $fieldValue = key_exists($item["field_id"],$setList)?$setList[$item["field_id"]]:null;
                    $this->jd_set[$item["field_id"]] = $fieldValue;
                }
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
			if ($this->city!='99999') $this->saveAccount($connection);
			$this->saveTrans($connection);
            //保存金蝶要求的字段
            $this->saveJDSetInfo($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    //保存金蝶要求的字段
    protected function saveJDSetInfo(&$connection) {
        foreach (self::$jd_set_list as $list){
            $field_value = key_exists($list["field_id"],$this->jd_set)?$this->jd_set[$list["field_id"]]:null;
            $rs = Yii::app()->db->createCommand()->select("id,field_id")->from("acc_send_set_jd")
                ->where("set_type ='account' and table_id=:table_id and field_id=:field_id",array(
                    ':field_id'=>$list["field_id"],':table_id'=>$this->id,
                ))->queryRow();
            if($rs){
                $connection->createCommand()->update('acc_send_set_jd',array(
                    "field_value"=>$field_value,
                ),"id=:id",array(':id'=>$rs["id"]));
            }else{
                $connection->createCommand()->insert('acc_send_set_jd',array(
                    "table_id"=>$this->id,
                    "set_type"=>'account',
                    "field_id"=>$list["field_id"],
                    "field_value"=>$field_value,
                ));
            }
        }
    }

	protected function saveAccount(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_account where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_account(
						acct_type_id, acct_no, acct_name, bank_name, coa, remarks, city, status, luu, lcu) values (
						:acct_type_id, :acct_no, :acct_name, :bank_name, :coa, :remarks, :city, :status, :luu, :lcu)";
				break;
			case 'edit':
				$sql = "update acc_account set 
					acct_type_id = :acct_type_id, 
					acct_no = :acct_no,
					acct_name = :acct_name,
					bank_name = :bank_name, 
					coa = :coa, 
					remarks = :remarks,
					status = :status,
					luu = :luu
					where id = :id and city=:city";
				break;
		}

		$city = ($this->scenario=='new') ? Yii::app()->user->city() : $this->city;
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':acct_type_id')!==false)
			$command->bindParam(':acct_type_id',$this->acct_type_id,PDO::PARAM_INT);
		if (strpos($sql,':acct_no')!==false)
			$command->bindParam(':acct_no',$this->acct_no,PDO::PARAM_STR);
		if (strpos($sql,':acct_name')!==false)
			$command->bindParam(':acct_name',$this->acct_name,PDO::PARAM_STR);
		if (strpos($sql,':bank_name')!==false)
			$command->bindParam(':bank_name',$this->bank_name,PDO::PARAM_STR);
		if (strpos($sql,':coa')!==false)
			$command->bindParam(':coa',$this->coa,PDO::PARAM_STR);
		if (strpos($sql,':remarks')!==false)
			$command->bindParam(':remarks',$this->remarks,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveTrans(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_trans where acct_id = :id and trans_type_code = 'OPEN'";
				break;
			case 'new':
				$sql = "insert into acc_trans(
						trans_dt, trans_type_code, acct_id,	trans_desc, amount,	city, luu, lcu) values (
						:trans_dt, 'OPEN', :id, '', :amount, :trans_city, :luu, :lcu)";
				break;
			case 'edit':
				if (empty($this->trans_city)) {
					$sql = "insert into acc_trans(
							trans_dt, trans_type_code, acct_id,	trans_desc, amount,	city, luu, lcu) values (
							:trans_dt, 'OPEN', :id, '', :amount, :trans_city, :luu, :lcu)";
				} else {
					$sql = "update acc_trans set 
							amount = :amount, 
							trans_dt = :trans_dt,
							luu = :luu
							where acct_id = :id and trans_type_code = 'OPEN' and city = :trans_city
							and (trans_dt <> :trans_dt or amount <> :amount or amount is null)
					";
				}
				break;
		}

		$trans_city = empty($this->trans_city) ? Yii::app()->user->city() : $this->trans_city;
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':trans_dt')!==false) {
			$tdate = General::toMyDate($this->open_dt);
			$command->bindParam(':trans_dt',$tdate,PDO::PARAM_STR);
		}
		if (strpos($sql,':amount')!==false) {
//			$amt = General::toMyNumber($this->open_bal);
			$amt = $this->open_bal;
			$command->bindParam(':amount',$amt,PDO::PARAM_STR);
		}
		if (strpos($sql,':trans_city')!==false)
			$command->bindParam(':trans_city',$trans_city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();
	}
	
	public function isOccupied($index) {
		$city = Yii::app()->user->city();
		$sql = "select a.id from acc_trans a 
				where a.trans_type_code<>'OPEN' and a.acct_id=$index 
				and a.city='$city' and a.status<>'V'
				limit 1
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row!==false);
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view' || $this->city=='99999');
	}
}
