<?php

class BalAdjForm extends CFormModel 
{
	public $audit_year;
	public $audit_month;
	public $acct_id;
	public $acct_name;
	public $t3record = array();
	public $lbsrecord = array();
	public $city;
	public $city_name;
	
	public $bal_adj = 0.00;
	public $bal_adj_id = 0;
	
	public $bal_t3 = 0.00;
	public $bal_lbs = 0.00;
	public $tot_tr_lnr = 0.00;
	public $tot_tp_lnp = 0.00;
	public $tot_lr_tnr = 0.00;
	public $tot_lp_tnp = 0.00;
	public $tot_adj_t = 0.00;
	public $tot_adj_l = 0.00;

	public function rules()	{
		return array(
			array('audit_year, audit_month, acct_id', 'required'),
			array('t3record','validateT3Record'),
			array('lbsrecord','validateLBSRecord'),
			array('city, city_name, acct_name, bal_adj, bal_adj_id','safe'),
			array('bal_adj, bal_t3, bal_lbs, tot_tr_lnr, tot_tp_lnp, tot_lr_tnr, tot_lp_tnp, tot_adj_t, tot_adj_l','safe'),
		);
	}

	public function attributeLabels() {
		return array(	
			'audit_year'=>Yii::t('trans','Year'),
			'audit_month'=>Yii::t('trans','Month'),
			'city'=>Yii::t('misc','City'),
			'acct_name'=>Yii::t('trans','Account Name'),
			'remarks'=>Yii::t('trans','Remarks'),
			'adjtype'=>Yii::t('trans','Type'),
			'amount'=>Yii::t('trans','Amount'),
			'bal_adj'=>Yii::t('trans','Balance After Adj.'),
			'bal_t3'=>Yii::t('trans','T3 Balance'),
			'bal_lbs'=>Yii::t('trans','LBS Balance'),
			'tot_tr_lnr'=>Yii::t('trans','T3 Rec. LBS Not'),
			'tot_tp_lnp'=>Yii::t('trans','T3 Paid LBS Not'),
			'tot_lr_tnr'=>Yii::t('trans','LBS Rec. T3 Not'),
			'tot_lp_tnp'=>Yii::t('trans','LBS Paid T3 Not'),
			'tot_adj_t'=>Yii::t('trans','T3 Adj. Balance'),
			'tot_adj_l'=>Yii::t('trans','LBS Adj. Balance'),
		);
	}
	
	public function retrieveData($city, $year, $month, $acctid) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql = "select b.bal_month_end, b.bal_t3, c.acct_name, c.bank_name, e.acct_type_desc
				from acc_t3_audit_hdr a 
				inner join acc_t3_audit_dtl b on a.id=b.hdr_id
				left outer join acc_account c on b.acct_id=c.id
				left outer join acc_account_type e on c.acct_type_id=e.id
				where a.city='$city' and a.audit_year=$year and a.audit_month=$month
				and b.acct_id=$acctid
				and a.city in ($citylist)
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();

		if ($row===false) return false;

		$this->audit_year = $year;
		$this->audit_month = $month;
		$this->acct_id = $acctid;
		$this->city = $city;
		$this->bal_t3 = $row['bal_t3'];
		$this->bal_lbs = $row['bal_month_end'];
		$this->acct_name = '('.$row['acct_type_desc'].') '.$row['acct_name']
						.(empty($row['bank_name']) ? '' : ' - ').$row['bank_name'];
		
		$sql = "select a.* 
				from acc_balance_adj a
				where a.city='$city' and a.audit_year=$year
				and a.audit_month=$month and a.acct_id=$acctid
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $k=>$record) {
				$temp = array();
				$temp['id'] = $record['id'];
				$temp['adjtype'] = $record['adj_type'];
				$temp['amount'] = $record['amount'];
				$temp['remarks'] = $record['remarks'];
				$temp['uflag'] = 'N';

				switch ($record['adj_type']) {
					case 'ADJ':
						$this->bal_adj = $record['amount'];
						$this->bal_adj_id = $record['id'];
						break;
					case 'L1':
						$this->tot_lr_tnr += $record['amount'];
						$this->lbsrecord[] = $temp;
						break;
					case 'L2':
						$this->tot_lp_tnp += $record['amount'];
						$this->lbsrecord[] = $temp;
						break;
					case 'T1':
						$this->tot_tr_lnr += $record['amount'];
						$this->t3record[] = $temp;
						break;
					case 'T2':
						$this->tot_tp_lnp += $record['amount'];
						$this->t3record[] = $temp;
						break;
				}
			}
		}
		$this->tot_adj_t = $this->bal_t3 + $this->tot_tr_lnr - $this->tot_tp_lnp;
		$this->tot_adj_l = $this->bal_lbs + $this->tot_lr_tnr - $this->tot_lp_tnp;
		
		$temp = array();
		$temp['id'] = 0;
		$temp['adjtype'] = '';
		$temp['amount'] = '';
		$temp['remarks'] = '';
		$temp['uflag'] = 'N';
		if (empty($this->t3record)) $this->t3record[] = $temp;
		if (empty($this->lbsrecord)) $this->lbsrecord[] = $temp;
			
		return true;
	}

	public function save() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	protected function saveDetail(&$connection) {
		$sql_i = "insert into acc_balance_adj 
					(audit_year, audit_month, acct_id, adj_type, amount, remarks, city, lcu, luu)
				values
					(:audit_year, :audit_month, :acct_id, :adj_type, :amount, :remarks, :city, :uid, :uid)
			";
		$sql_u = "update acc_balance_adj 
					set adj_type=:adj_type, amount=:amount, remarks=:remarks, luu=:uid
				where 
					id = :id
			";
		$sql_d = "delete from acc_balance_adj where id = :id";
		
		$uid = Yii::app()->user->id;
		
		$records = array_merge($this->t3record, $this->lbsrecord);
		$records[] = array('id'=>$this->bal_adj_id, 'remarks'=>'', 'amount'=>$this->bal_adj, 'adjtype'=>'ADJ', 'uflag'=>'Y');
		foreach ($records as $k=>$row) {
			if (!empty($row['uflag']) && $row['uflag']!='N') {
				$sql = '';
				if ($row['uflag']=='D') {
					$sql = $row['id']==0 ? '' : $sql_d;
				} else {
					$sql = $row['id']==0 ? $sql_i : $sql_u;
				}
				
				if (!empty($sql)) {
					$command=$connection->createCommand($sql);
					if (strpos($sql,':id')!==false) 
						$command->bindParam(':id',$row['id'],PDO::PARAM_INT);
					if (strpos($sql,':audit_year')!==false) 
						$command->bindParam(':audit_year',$this->audit_year,PDO::PARAM_INT);
					if (strpos($sql,':audit_month')!==false) 
						$command->bindParam(':audit_month',$this->audit_month,PDO::PARAM_INT);
					if (strpos($sql,':acct_id')!==false) 
						$command->bindParam(':acct_id',$this->acct_id,PDO::PARAM_INT);
					if (strpos($sql,':adj_type')!==false) 
						$command->bindParam(':adj_type',$row['adjtype'],PDO::PARAM_STR);
					if (strpos($sql,':amount')!==false) {
						$amt1 = $row['amount']==0 ? '0.00' : General::toMyNumber($row['amount']);
						$command->bindParam(':amount',$amt1,PDO::PARAM_STR);
					}
					if (strpos($sql,':remarks')!==false) 
						$command->bindParam(':remarks',$row['remarks'],PDO::PARAM_STR);
					if (strpos($sql,':city')!==false) 
						$command->bindParam(':city',$this->city,PDO::PARAM_STR);
					if (strpos($sql,':uid')!==false)
						$command->bindParam(':uid',$uid,PDO::PARAM_STR);
					$command->execute();
				}
			}
		}
		
		return true;
	}

	public function validateT3Record($attribute, $params){
		$message = '';
		foreach ($this->t3record as $data) {
			if ($data['uflag']=='Y') {
				if (empty($data['adjtype'])) {
					$message = Yii::t('trans','T3 Items: Type cannot be none');
					$this->addError($attribute,$message);
					break;
				}
				if (empty($data['remarks'])) {
					$message = Yii::t('trans','T3 Items: Remarks cannot be empty');
					$this->addError($attribute,$message);
					break;
				}
				if (!is_numeric($data['amount'])) {
					$message = Yii::t('trans','T3 Items: Amount value is incorrect');
					$this->addError($attribute,$message);
					break;
				}
			}
		}
	}
	
	public function validateLBSRecord($attribute, $params){
		$message = '';
		foreach ($this->lbsrecord as $data) {
			if ($data['uflag']=='Y') {
				if (empty($data['adjtype'])) {
					$message = Yii::t('trans','LBS Items: Type cannot be none');
					$this->addError($attribute,$message);
					break;
				}
				if (empty($data['remarks'])) {
					$message = Yii::t('trans','LBS Items: Remarks cannot be empty');
					$this->addError($attribute,$message);
					break;
				}
				if (!is_numeric($data['amount'])) {
					$message = Yii::t('trans','LBS Items: Amount value is incorrect');
					$this->addError($attribute,$message);
					break;
				}
			}
		}
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view' || !empty($this->audit_dt));
	}
}
