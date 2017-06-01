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
	
	public $acct_id;
	public $ref_no;
	public $acct_code;
	public $cheque_no;
	public $invoice_no;
	public $trans_dt;
	public $trans_id;
	public $trans_id_c;
		
	private $dyn_fields = array(
							'acct_id',
							'ref_no',
							'acct_code',
							'cheque_no',
							'invoice_no',
							'trans_dt',
							'trans_id',
							'trans_id_c',
						);
	
	public $files;

	public $docMasterId = array(
							'payreal'=>0,
							'tax'=>0
						);
	public $removeFileId = array(
							'payreal'=>0,
							'tax'=>0
						);
	public $no_of_attm = array(
							'payreal'=>0,
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
			'req_dt'=>Yii::t('trans','Trans. Date'),
			'trans_dt'=>Yii::t('trans','Trans. Date'),
			'acct_id'=>Yii::t('trans','Account'),
			'trans_desc'=>Yii::t('trans','Remarks'),
			'amount'=>Yii::t('trans','Amount'),
			'cheque_no'=>Yii::t('trans','Cheque No.'),
			'invoice_no'=>Yii::t('trans','China Invoice No.'),
			'status_desc'=>Yii::t('trans','Status'),
			'ref_no'=>Yii::t('trans','Ref. No.'),
			'acct_code'=>Yii::t('trans','Paid Item'),
			'acct_id'=>Yii::t('trans','Paid Account'),
			'user_name'=>Yii::t('trans','Requestor'),

		);
	}

	public function rules() {
		return array(
			array('trans_dt','required'),
			array('cheque_no, invoice_no','safe'),
			array('trans_type_code, req_user, req_dt, payee_name, payee_type, acct_id, amount','safe'),
			array('id, item_desc, payee_id, status, status_desc, acct_code, city, ref_no, user_name, trans_id, trans_id_c','safe'), 
			array('files, removeFileId, docMasterId','safe'), 
			array ('no_of_attm','validateTaxSlip'),
				
		);
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
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PR', Yii::app()->user->id);
		if (empty($list)) $list = '0';

		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.*, b.disp_name as user_name,  
				workflow$suffix.RequestStatus('PAYMENT',id,req_dt) as wfstatus,
				workflow$suffix.RequestStatusDesc('PAYMENT',id,req_dt) as wfstatusdesc
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
		}
		return (count($rows) > 0);
	}
	
	public function submit() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
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
					'united_inv_no'=>'N/A',
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
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
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
}
