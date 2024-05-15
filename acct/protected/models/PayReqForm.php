<?php

class PayReqForm extends CFormModel
{
	public $id;
	public $req_dt;
	public $req_user;
	public $trans_type_code;
	public $payee_type = 'C';
	public $payee_id;
	public $payee_name;
	public $item_desc;
	public $amount;
	public $status;
	public $status_desc;
	public $wfstatus;
	public $wfstatusdesc;
	public $city;
	
	public $acct_id;
	public $ref_no;
	public $acct_code;
	public $acct_code_desc;
	public $reason;
	public $reason_cf;
	public $item_code;
	public $pitem_desc;
	public $int_fee;
	
	private $dyn_fields = array(
							'acct_id',
							'ref_no',
							'acct_code',
							'reason',
							'reason_cf',
							'item_code',
							'int_fee',
						);
	
	public $files;

	public $docMasterId = array(
							'payreal'=>0,
							'payreq'=>0,
							'tax'=>0
						);
	public $removeFileId = array(
							'payreal'=>0,
							'payreq'=>0,
							'tax'=>0
						);
	public $no_of_attm = array(
							'payreal'=>0,
							'payreq'=>0,
							'tax'=>0
						);
	
	public $wfCode = 'PAYMENT';
	public $wfIdField = 'id';
	public $wfDateField = 'req_dt';

	public $lcu;
	public $luu;
	public $lcd;
	public $lud;
	
	public function init() {
		$this->req_dt = date('Y/m/d');
		$this->trans_type_code = '';
		$this->acct_id = 0;
		$this->req_user = Yii::app()->user->id;
		$this->int_fee = 'N';
		$this->city = Yii::app()->user->city();
		
		parent::init();
	}
	
	public function attributeLabels()
	{
		return array(
			'req_dt'=>Yii::t('trans','Req. Date'),
			'trans_type_code'=>Yii::t('trans','Trans. Type'),
			'payee_name'=>Yii::t('trans','Payee'),
			'item_desc'=>Yii::t('trans','Details'),
			'amount'=>Yii::t('trans','Amount'),
			'city_name'=>Yii::t('misc','City'),
			'city'=>Yii::t('misc','City'),
			'status_desc'=>Yii::t('trans','Status'),
			'wfstatusdesc'=>Yii::t('trans','Flow Status'),
			'ref_no'=>Yii::t('trans','Ref. No.'),
			'item_code'=>Yii::t('trans','Paid Item'),
			'pitem_desc'=>Yii::t('trans','Paid Item'),
			'acct_code'=>Yii::t('trans','Account Code'),
			'acct_id'=>Yii::t('trans','Paid Account'),
			'reason'=>Yii::t('trans','Reason'),
			'reason_cf'=>Yii::t('trans','Reason'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),

            'lcu'=>Yii::t('trans','lcu'),
            'luu'=>Yii::t('trans','luu'),
            'lcd'=>Yii::t('trans','lcd'),
            'lud'=>Yii::t('trans','lud'),
		);
	}

	public function rules() {
		return array(
			array('trans_type_code, req_user, req_dt, payee_name, payee_type, acct_id, amount, item_code, pitem_desc, acct_code, city','required'),
			array('city','validateCity'),
			array('acct_id','validateAcctId'),
			array('id, item_desc, payee_id, status, status_desc, acct_code_desc, int_fee, city, reason, reason_cf','safe'),
			array('files, removeFileId, docMasterId, no_of_attm,lcu,luu,lcd,lud','safe'),
		);
	}

	public function validateCity($attribute, $params) {
        $city = $this->city;
        $city_allow = Yii::app()->user->city_allow();
        $city =strpos("'{$city_allow}'","'{$city}'")!==false?$city:Yii::app()->user->city();
        $this->city=$city;
    }

	public function validateAcctId($attribute, $params) {
		if ($this->$attribute=='0') {
			$this->addError($attribute, Yii::t('trans','Paid Account cannot be blank'));
		} else {
			$id = $this->$attribute;
			$date = date("Y-m-d");
			$city = $this->city;
			$sql = "select AccountBalance($id,'$city','2010-01-01','$date') as balance";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row===false || empty($row['balance']) || $row['balance'] < 0)
				$this->addError($attribute, Yii::t('trans','This paid account cannot be used because balance is less than 0'));
		}
	}
	
	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$user = Yii::app()->user->id;
		$city = Yii::app()->user->city_allow();
		$sql = "select *,  
				workflow$suffix.RequestStatus('PAYMENT',id,req_dt) as wfstatus,
				workflow$suffix.RequestStatusDesc('PAYMENT',id,req_dt) as wfstatusdesc,
				docman$suffix.countdoc('payreal',id) as payrealcountdoc,
				docman$suffix.countdoc('payreq',id) as payreqcountdoc,
				docman$suffix.countdoc('tax',id) as taxcountdoc
				from acc_request where id=$index 
				and ((city in ($city) and req_user<>'$user') or req_user='$user') 
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$this->id = $row['id'];
				$this->req_dt = General::toDate($row['req_dt']);
				$this->req_user = $row['req_user'];
				$this->trans_type_code = $row['trans_type_code'];
				$this->payee_type = $row['payee_type'];
				$this->payee_id = $row['payee_id'];
				$this->payee_name= $row['payee_name'];
				$this->item_desc = $row['item_desc'];
				$this->amount = $row['amount'];
				$this->status = $row['status'];
				$this->status_desc = General::getTransStatusDesc($row['status']);
				$this->wfstatus = $row['wfstatus'];
				$this->wfstatusdesc = $row['wfstatusdesc'];
				$this->no_of_attm['payreal'] = $row['payrealcountdoc'];
				$this->no_of_attm['payreq'] = $row['payreqcountdoc'];
				$this->no_of_attm['tax'] = $row['taxcountdoc'];
				$this->city = $row['city'];
                $this->lcd = $row['lcd'];
                $this->lud = $row['lud'];
                $this->lcu = $row['lcu'];
                $this->luu = $row['luu'];
				break;
			}
		
			$sql = "select * from acc_request_info where req_id=$index";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				foreach ($rows as $row) {
					$dynfldid = $row['field_id'];
					if (in_array($dynfldid,$this->dyn_fields)) {
						$this->$dynfldid = $row['field_value'];
					}
				}
			}
			
			$acctcodelist = General::getAcctCodeList();
			$acctitemlist = General::getAcctItemList();
			if (isset($acctcodelist[$this->acct_code])) $this->acct_code_desc = $acctcodelist[$this->acct_code];
			if (isset($acctitemlist[$this->item_code])) $this->pitem_desc = $acctitemlist[$this->item_code];
		}
		return (count($rows) > 0);
	}
	
	public function voidRecord() {
		try {
			$reqId = $this->id;
			$uid = Yii::app()->user->id;
			$sql = "update acc_request set status='V', luu='$uid'
					where id = $reqId
				";
			Yii::app()->db->createCommand($sql)->execute();
		}
		catch(Exception $e) {
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function cancel() {
		$uid = Yii::app()->user->id;
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$reqId = $this->id;
			$sql = "update acc_request set status='V', luu='$uid' 
					where id = $reqId
				";
			$connection->createCommand($sql)->execute();
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->takeAction('CANCEL');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function submit()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$this->saveReq($connection);
			$this->ref_no = $this->genRequestRefNo();
			$this->saveInfo($connection);
			$this->updateDocman($connection,'PAYREQ');
			$this->updateDocman($connection,'TAX');
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->saveRequestData('CITY',$this->city);
				//$wf->saveRequestData('CITY',Yii::app()->user->city());
				$wf->saveRequestData('REQ_USER',Yii::app()->user->id);
				$wf->saveRequestData('REF_NO',$this->ref_no);
				$wf->saveRequestData('AMOUNT',$this->amount);
				$payee = $this->getPayeeUserId();
				$wf->saveRequestData('PAYEE_USER',$payee);
				$wf->takeAction('SUBMIT');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function request()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$this->saveReq($connection);
			$this->ref_no = $this->genRequestRefNo();
			$this->saveInfo($connection);
			$this->updateDocman($connection,'PAYREQ');
			$this->updateDocman($connection,'TAX');
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->saveRequestData('CITY',Yii::app()->user->city());
				$wf->saveRequestData('REQ_USER',Yii::app()->user->id);
				$wf->saveRequestData('REF_NO',$this->ref_no);
				$wf->saveRequestData('AMOUNT',$this->amount);
				$payee = $this->getPayeeUserId();
				$wf->saveRequestData('PAYEE_USER',$payee);
				$wf->takeAction('REQUEST');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function check()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
//			$this->saveReq($connection);
//			$this->saveInfo($connection);
			$this->updateDocman($connection,'PAYREQ');
			$this->updateDocman($connection,'TAX');
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
//				$wf->saveRequestData('CITY',Yii::app()->user->city());
//				$wf->saveRequestData('REQ_USER',Yii::app()->user->id);
				$wf->saveRequestData('REF_NO',$this->ref_no);
				$wf->saveRequestData('AMOUNT',$this->amount);
				$payee = $this->getPayeeUserId();
				$wf->saveRequestData('PAYEE_USER',$payee);
				$wf->takeAction('CHECK');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveReq($connection);
			$this->saveInfo($connection);
			$this->updateDocman($connection,'PAYREQ');
			$this->updateDocman($connection,'TAX');
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function updateDocman(&$connection, $doctype) {
		if ($this->scenario=='new') {
			$docidx = strtolower($doctype);
			if ($this->docMasterId[$docidx] > 0) {
				$docman = new DocMan($doctype,$this->id,get_class($this));
				$docman->masterId = $this->docMasterId[$docidx];
				$docman->updateDocId($connection, $this->docMasterId[$docidx]);
			}
		}
	}

	protected function saveReq(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_request where id = :id and req_user = :req_user and status <> 'S'";
				break;
			case 'new':
				$sql = "insert into acc_request(
							req_dt, req_user, payee_type, payee_id, payee_name, trans_type_code,
							item_desc, amount, status, city, lcu, luu
						) values (
							:req_dt, :req_user, :payee_type, :payee_id, :payee_name, :trans_type_code,
							:item_desc, :amount, 'Y', :city, :lcu, :luu
						)";
				break;
			case 'edit':
				$sql = "update acc_request set 
							req_dt = :req_dt, 
							payee_type = :payee_type, 
							payee_id = :payee_id, 
							payee_name = :payee_name, 
							trans_type_code = :trans_type_code,
							item_desc = :item_desc, 
							amount = :amount, 
							luu = :luu
						where id = :id and city=:city and req_user=:req_user";
				break;
		}

		$city = $this->city;	//Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':trans_type_code')!==false)
			$command->bindParam(':trans_type_code',$this->trans_type_code,PDO::PARAM_STR);
		if (strpos($sql,':payee_id')!==false)
			$command->bindParam(':payee_id',$this->payee_id,PDO::PARAM_INT);
		if (strpos($sql,':payee_type')!==false)
			$command->bindParam(':payee_type',$this->payee_type,PDO::PARAM_STR);
		if (strpos($sql,':payee_name')!==false)
			$command->bindParam(':payee_name',$this->payee_name,PDO::PARAM_STR);
		if (strpos($sql,':req_dt')!==false)
			$command->bindParam(':req_dt',$this->req_dt,PDO::PARAM_STR);
		if (strpos($sql,':req_user')!==false)
			$command->bindParam(':req_user',$uid,PDO::PARAM_STR);
		if (strpos($sql,':amount')!==false) {
			$amt = General::toMyNumber($this->amount);
			$command->bindParam(':amount',$amt,PDO::PARAM_STR);
		}
		if (strpos($sql,':item_desc')!==false)
			$command->bindParam(':item_desc',$this->item_desc,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveInfo(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_request_info
						where req_id = :id and field_id = :field_id
					";
				break;
			case 'new':
				$sql = "insert into acc_request_info(
						req_id, field_id, field_value, luu, lcu) values (
						:id, :field_id, :field_value, :luu, :lcu)";
				break;
			case 'edit':
				$sql = "insert into acc_request_info(
						req_id, field_id, field_value, luu, lcu) values (
						:id, :field_id, :field_value, :luu, :lcu)
						on duplicate key update
						field_value = :field_value, luu = :luu
					";
				break;
		}

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		foreach ($this->dyn_fields as $dynfldid) {
		
			if (isset($this->$dynfldid)) {
				$command=$connection->createCommand($sql);
				if (strpos($sql,':id')!==false)
					$command->bindParam(':id',$this->id,PDO::PARAM_INT);
				if (strpos($sql,':field_id')!==false)
					$command->bindParam(':field_id',$dynfldid,PDO::PARAM_STR);
				if (strpos($sql,':field_value')!==false) {
					$value = $this->$dynfldid;
					$command->bindParam(':field_value',$value,PDO::PARAM_STR);
				}
				if (strpos($sql,':lcu')!==false)
					$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
				if (strpos($sql,':luu')!==false)
					$command->bindParam(':luu',$uid,PDO::PARAM_STR);
				$command->execute();
			}
		}
		return true;
	}
	
	protected function getDefaultAccountValue($type) {
		$rtn = '';
		$sql = "select a.id from acc_account a, acc_account_type b 
				where a.acct_type_id=b.id and b.rpt_cat='$type'
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row !== false) {
			$rtn = $row['id'];
		}
		return $rtn;
	}
	
	protected function genRequestRefNo() {
		$city = Yii::app()->user->city();
		$date = date('YmdHis');
		return $city.$date;
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view'||$this->status=='V'|| strpos('~~PC~','~'.$this->wfstatus.'~')===false || !$this->allowRequestCheck());
	}
	
	public function isTaxSlipReadOnly() {
		return ($this->scenario=='view'||(!empty($this->wfstatus) && strpos('~ED~PS~SI~RC~C~D~','~'.$this->wfstatus.'~')!==false));
	}
	
	public function isPayrealReady() {
		return (!empty($this->wfstatus) && strpos('~ED~PR~QR~PS~SI~RC~RR~RE~','~'.$this->wfstatus.'~')!==false);
	}

	public function allowRequestCheck() {
		return Yii::app()->user->validFunction('CN03');
	}

	public function allowSubmit() {
		return Yii::app()->user->validFunction('CN04');
	}

	public function allowVoid() {
		return Yii::app()->user->validFunction('CN06');
	}
	
	protected function getPayeeUserId() {
		$rtn = '';
		if ($this->payee_type=='F') {
			$suffix = Yii::app()->params['envSuffix'];
			$sql = "select user_id from hr$suffix.hr_binding where employee_id=".$this->payee_id;
			$user = Yii::app()->db->createCommand($sql)->queryRow();
			if ($user!==false) $rtn = $user['user_id'];
		}
		return $rtn;
	}

    //由於列表需要顯示附件數量，導致列表打開太慢，所以保存附件數量
    public function resetFileSum($id=0){
        $id = empty($id)||!is_numeric($id)?0:$id;
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "update acc_request set
          doc_count_req=docman{$suffix}.countdoc('payreq',{$id}),
          doc_count_real=docman{$suffix}.countdoc('payreal',{$id}),
          doc_count_tax=docman{$suffix}.countdoc('tax',{$id})
          WHERE id={$id}
        ";
        Yii::app()->db->createCommand($sql)->execute();
    }
}
