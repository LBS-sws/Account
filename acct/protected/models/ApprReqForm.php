<?php

class ApprReqForm extends CFormModel
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
	public $user_name;
	public $city;
	public $type;
	
	public $acct_id;
	public $ref_no;
	public $acct_code;
	public $reason;
	
	private $dyn_fields = array(
							'acct_id',
							'ref_no',
							'acct_code',
							'reason',
						);
	
	public $files;

	public $docMasterId = array(
							'payreq'=>0,
							'tax'=>0
						);
	public $removeFileId = array(
							'payreq'=>0,
							'tax'=>0
						);
	public $no_of_attm = array(
							'payreq'=>0,
							'tax'=>0
						);
	
	public function attributeLabels()
	{
		return array(
			'req_dt'=>Yii::t('trans','Req. Date'),
			'trans_type_code'=>Yii::t('trans','Trans. Type'),
			'payee_name'=>Yii::t('trans','Payee'),
			'item_desc'=>Yii::t('trans','Details'),
			'amount'=>Yii::t('trans','Amount'),
			'city_name'=>Yii::t('misc','City'),
			'status_desc'=>Yii::t('trans','Status'),
			'wfstatusdesc'=>Yii::t('trans','Flow Status'),
			'ref_no'=>Yii::t('trans','Ref. No.'),
			'acct_code'=>Yii::t('trans','Paid Item'),
			'acct_id'=>Yii::t('trans','Paid Account'),
			'user_name'=>Yii::t('trans','Requestor'),
			'reason'=>Yii::t('trans','Reason'),
		);
	}

	public function rules()
	{
		return array(
			array('trans_type_code, req_user, req_dt, payee_name, payee_type, acct_id, amount','safe'),
			array('id, item_desc, payee_id, status, status_desc, ref_no, acct_code, type, remarks, reason','safe'), 
			array('files, removeFileId, docMasterId, no_of_attm','safe'), 
				
		);
	}

	public function retrieveData($index, $type='P')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = ($type=='P')
				? $wf->getPendingRequestIdList('PAYMENT', 'PA', Yii::app()->user->id)
				: $wf->getPendingStandbyRequestIdList('PAYMENT', 'PA', Yii::app()->user->id);

		if (empty($list)) $list = '0';

		$sql = "select a.*, b.disp_name as user_name,  
				workflow$suffix.RequestStatus('PAYMENT',id,req_dt) as wfstatus,
				workflow$suffix.RequestStatusDesc('PAYMENT',id,req_dt) as wfstatusdesc
				from acc_request a, security$suffix.sec_user b where id=$index and id in ($list) 
				and a.req_user=b.username
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
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
				$this->user_name = $row['user_name'];
				$this->city = $row['city'];
				$this->type = $type;
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
	
	public function approve()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->saveRequestData('APPROVER',Yii::app()->user->id);
				$wf->takeAction('APPROVE');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	public function deny()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			$this->saveInfo($connection);
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->takeAction('DENY');
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
					field_value = :field_value, luu = :luu
				";

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		if (isset($this->reason)) {
			$dynfldid = 'reason';
			$command=$connection->createCommand($sql);
			if (strpos($sql,':id')!==false)
				$command->bindParam(':id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false)
				$command->bindParam(':field_id',$dynfldid,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false) {
				$value = $this->reason;
				$command->bindParam(':field_value',$value,PDO::PARAM_STR);
			}
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			$command->execute();
		}
		return true;
	}
	
	public function isReadOnly() {
		return true;
	}
}
