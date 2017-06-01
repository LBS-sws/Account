<?php

class SignReqForm extends CFormModel
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
	public $cheque_no;
	public $invoice_no;
	public $trans_dt;
	public $acct_code;
	public $ref_no;
		
	private $dyn_fields = array(
							'acct_id',
							'ref_no',
							'acct_code',
							'cheque_no',
							'invoice_no',
							'trans_dt',
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
			array('trans_dt','safe'),
			array('cheque_no, invoice_no, acct_code, ref_no','safe'),
			array('trans_type_code, req_user, req_dt, payee_name, payee_type, acct_id, amount,','safe'),
			array('id, item_desc, payee_id, status, status_desc, cheque_no, invoice_no, ref_no, user_name','safe'), 
			array('no_of_attm, files, removeFileId, docMasterId','safe'), 
				
		);
	}

	public function retrieveData($index) {
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PS', Yii::app()->user->id);
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
	
	public function sign() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			if ($wf->startProcess('PAYMENT',$this->id,$this->req_dt)) {
				$wf->takeAction('REIMAPPR');
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	public function isReadOnly() {
		return true;
	}
}
