<?php

class RealizeForm extends CFormModel
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
	public $user_name;
	public $city;
	public $wfstatus;
	public $reason;
	
	public $acct_id;
	public $ref_no;
	public $acct_code;
	public $acct_code_desc;
	public $item_code;
	public $pitem_desc;
	public $cheque_no;
	public $invoice_no;
	public $trans_dt;
	public $trans_id;
	public $trans_id_c;
	public $int_fee;
		
	private $dyn_fields = array(
							'acct_id',
							'ref_no',
							'acct_code',
							'cheque_no',
							'invoice_no',
							'trans_dt',
							'trans_id',
							'trans_id_c',
							'int_fee',
							'item_code',
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

	public function attributeLabels() {
		return array(
			'req_dt'=>Yii::t('trans','Req. Date'),
			'trans_type_code'=>Yii::t('trans','Trans. Type'),
			'payee_name'=>Yii::t('trans','Payee'),
			'item_desc'=>Yii::t('trans','Details'),
			'amount'=>Yii::t('trans','Amount'),
			'city_name'=>Yii::t('misc','City'),
			'status_desc'=>Yii::t('trans','Status'),
			'trans_dt'=>Yii::t('trans','Trans. Date'),
			'acct_id'=>Yii::t('trans','Account'),
			'trans_desc'=>Yii::t('trans','Remarks'),
			'amount'=>Yii::t('trans','Amount'),
			'cheque_no'=>Yii::t('trans','Cheque No.'),
			'invoice_no'=>Yii::t('trans','China Invoice No.'),
			'status_desc'=>Yii::t('trans','Status'),
			'ref_no'=>Yii::t('trans','Ref. No.'),
			'item_code'=>Yii::t('trans','Paid Item'),
			'pitem_desc'=>Yii::t('trans','Paid Item'),
			'acct_code'=>Yii::t('trans','Account Code'),
			'acct_id'=>Yii::t('trans','Paid Account'),
			'user_name'=>Yii::t('trans','Requestor'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),
			'reason'=>Yii::t('trans','Return Reason'),
		);
	}

	public function rules() {
		return array(
			array('trans_dt','required'),
			array('trans_dt','validateTransDate'),
			array('cheque_no, invoice_no','safe'),
			array('trans_type_code, req_user, req_dt, payee_name, payee_type, acct_id, item_code, pitem_desc, amount, wfstatus','safe'),
			array('id, item_desc, payee_id, status, status_desc, acct_code, city, ref_no, user_name, trans_id, trans_id_c, int_fee, reason','safe'), 
			array('files, removeFileId, docMasterId','safe'), 
			array ('no_of_attm','validateTaxSlip'),
				
		);
	}

	public function validateTransDate($attribute, $params) {
		$dt1 = General::toDate($this->$attribute);
		if ($dt1 > date("Y/m/d")) {
			$this->addError($attribute, Yii::t('trans','Invalid transaction date (later than today)'));
		} else {
			$id = $this->acct_id;
			$city = $this->city; //Yii::app()->user->city();
			$sql = "select trans_dt from acc_trans where acct_id=$id and city='$city' and trans_type_code='OPEN' and status='A'";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row!==false) {
				$dt0 = General::toDate($row['trans_dt']);
				if ($dt0 > $dt1) $this->addError($attribute, Yii::t('trans','Invalid transaction date (eariler than openning balance date)'));
			}
		}
	}

	public function validateTaxSlip($attribute, $params) {
		$count = $this->no_of_attm['tax'];
		if ($this->scenario=='submit' && (empty($count) || $count==0)) {
			$this->addError($attribute, Yii::t('trans','Please upload Tax Slip'));
		}
	}

	public function retrieveData($index) {
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list1 = $wf->getPendingRequestIdList('PAYMENT', 'PR', Yii::app()->user->id);
		$list2 = $wf->getPendingRequestIdList('PAYMENT', 'QR', Yii::app()->user->id);
		$list = $list1.(!empty($list1) && !empty($list2) ? ',' : '').$list2;
		if (empty($list)) $list = '0';

		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.*, b.disp_name as user_name,  
				workflow$suffix.RequestStatus('PAYMENT',id,req_dt) as wfstatus,
				workflow$suffix.RequestStatusDesc('PAYMENT',id,req_dt) as wfstatusdesc,
				docman$suffix.countdoc('payreal',id) as payrealcountdoc,
				docman$suffix.countdoc('payreq',id) as payreqcountdoc,
				docman$suffix.countdoc('tax',id) as taxcountdoc
				from acc_request a, security$suffix.sec_user b where id=$index and id in ($list) 
				and a.req_user=b.username
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
				$this->user_name = $row['user_name'];
				$this->city = $row['city'];
				$this->no_of_attm['payreal'] = $row['payrealcountdoc'];
				$this->no_of_attm['payreq'] = $row['payreqcountdoc'];
				$this->no_of_attm['tax'] = $row['taxcountdoc'];
				$this->wfstatus = $row['wfstatus'];
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
			
			if (empty($this->trans_dt)) $this->trans_dt = $this->req_dt;

			$acctcodelist = General::getAcctCodeList();
			$acctitemlist = General::getAcctItemList();
			if (isset($acctcodelist[$this->acct_code])) $this->acct_code_desc = $acctcodelist[$this->acct_code];
			if (isset($acctitemlist[$this->item_code])) $this->pitem_desc = $acctitemlist[$this->item_code];
			
			if ($this->wfstatus=='QR') {
				if ($wf->initReadOnlyProcess('PAYMENT',$this->id,$this->req_dt)) {
					$this->reason = "";
					$reasons = $wf->getLastStateActionRemarks('REIMREJ');
					foreach ($reasons as $reason) {
						$this->reason = $reason;
						break;
					}
				}
				SystemNotice::markReadforAllUser('QR', $index);
			} else {
				SystemNotice::markReadforAllUser('PR', $index);
			}
			
		}

		return (count($rows) > 0);
	}
	
	public function submit() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$this->saveReq($connection);
			$this->saveInfo($connection);
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->takeAction('REIMBURSE');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function gentrans() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$this->saveReq($connection);
			$this->saveInfo($connection);

			$data = array(
					'trans_dt'=>$this->trans_dt,
					'trans_type_code'=>$this->trans_type_code,
					'acct_id'=>$this->acct_id,
					'item_desc'=>$this->item_desc,
					'amount'=>$this->amount,
					'city'=>$this->city,
					'luu'=>Yii::app()->user->id,
					'lcu'=>Yii::app()->user->id,
					'payee_type'=>$this->payee_type,
					'payee_id'=>$this->payee_id,
					'payee_name'=>$this->payee_name,
					'cheque_no'=>$this->cheque_no,
					'invoice_no'=>$this->invoice_no,
					'acct_code'=>$this->acct_code,
					'item_code'=>$this->item_code,
					'united_inv_no'=>'N/A',
					'int_fee'=>$this->int_fee,
					'ref_no'=>$this->ref_no,
				);
			$tid = $wf->genAccTransRecord($data);
			$wf->genAccTransInfoRecord($tid, $data);
			$this->saveTransId($connection, $tid);
			
			if ($this->payee_type=='A') {
				$ctid = $wf->genCounterAccTransRecord($data);
				$wf->genCounterAccTransInfoRecord($ctid, $data);
				$this->saveTransId($connection, $ctid, 'C');
			}
			
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
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
				$wf->takeAction('REIMCANCEL');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveReq(&$connection)
	{
		$sql = "update acc_request set 
					req_dt = :req_dt, 
					payee_type = :payee_type, 
					payee_id = :payee_id, 
					payee_name = :payee_name, 
					trans_type_code = :trans_type_code,
					item_desc = :item_desc, 
					amount = :amount, 
					luu = :luu
				where id = :id and city=:city and req_user=:req_user
			";

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

		return true;
	}

	protected function saveInfo(&$connection) {
		$sql = "insert into acc_request_info(
					req_id, field_id, field_value, luu, lcu) values (
					:id, :field_id, :field_value, :luu, :lcu)
					on duplicate key update
					field_value = :field_value
				";

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
	
	protected function saveTransId(&$connection, $transId, $type='') {
		$sql = "insert into acc_request_info(
					req_id, field_id, field_value, luu, lcu) values (
					:id, :field_id, :field_value, :luu, :lcu)
					on duplicate key update
					field_value = :field_value
				";

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':field_id')!==false) {
			$fldid = ($type=='C') ? 'trans_id_c' : 'trans_id';
			$command->bindParam(':field_id',$fldid,PDO::PARAM_STR);
		}
		if (strpos($sql,':field_value')!==false) 
			$command->bindParam(':field_value',$transId,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}
	
	public function isReadOnly() {
		return ($this->scenario=='view'||$this->status=='V');
	}
	
	public function isFrozen() {
		return !empty($this->trans_id);
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
