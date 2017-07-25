<?php 
class WorkflowPayment extends WorkflowDMS {
	public function routeToApprover() {
		$value = $this->getRequestData('AMOUNT');
		$amount = (empty($value)||!is_numeric($value)) ? 0 : $value;
		if ($amount > 6000) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$this->assignRespUser($approver);
		} elseif ($amount > 3000) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirector');
			$this->assignRespUser($approver);
		} elseif ($amount > 1000) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirector');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirectorA');
			$this->assignRespUser($approver);
		} elseif ($amount > 500) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirector');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirectorA');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionMgr');
			$this->assignRespUser($approver);
		} else {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirector');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirectorA');
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionMgr');
			$this->assignRespStandbyUser($approver);
			$approver1 = $this->getApprover('regionMgrA');
			$approver2 = $this->getApprover('regionSuper');
			$this->assignRespUser($approver1);
			if ($approver1!=$approver2 && !empty($approver2)) $this->assignRespUser($approver2);
		}
	}
	
	public function routeToSigner() {
		$signer = $this->getRequestData('APPROVER');
		$this->assignRespUser($signer);
	}

	public function routeToRequestor() {
		$user = $this->getRequestData('REQ_USER');
		$this->assignRespUser($user);
	}
	
	public function routeToAccount() {
		$users = $this->getAccountStaff();
		if (empty($users)) {
			$user = $this->getRequestData('REQ_USER');
			$this->assignRespUser($user);
		} else {
			foreach ($users as $user) {
				$this->assignRespUser($user);
			}
		}
	}

	protected function getApprover($type) {
		$city = Yii::app()->user->city();
		$sql = "select username from acc_approver where city='$city' and approver_type='$type'";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row===false) ? '' : $row['username'];
	}

	protected function getAccountStaff() {
		$city = Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.username
				from security$suffix.sec_user a, security$suffix.sec_user_access b
				where a.username=b.username and a.city='$city' 
				and b.a_control like '%CN04%' and a.status='A'
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		$rtn = array();
		foreach ($rows as $row) $rtn[] = $row['username'];
		return $rtn;
	}
	
	protected function requestDetail($id) {
		$suffix = Yii::app()->params['envSuffix'];
		$rtn = '';
		$sql = "select a.*, x.disp_name, b.field_value as ref_no, c.field_value as acct_code,
					d.field_value as reason, e.field_value as int_fee  
				from acc_request a 
				left outer join security$suffix.sec_user x on a.req_user=x.username 
				left outer join acc_request_info b on a.id=b.req_id and b.field_id='ref_no'
				left outer join acc_request_info c on a.id=c.req_id and c.field_id='acct_code'
				left outer join acc_request_info d on a.id=d.req_id and d.field_id='reason'
				left outer join acc_request_info e on a.id=e.req_id and e.field_id='int_fee'
				where a.id=$id
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		if ($row===false) {
			$rtn = '&nbsp;';
		} else {
			$codelist = General::getAcctCodeList();
			$citylist = General::getCityList();
			$rtn = Yii::t('trans','Ref. No.').': '.$row['ref_no'].'<br>';
			$rtn .= Yii::t('misc','City').': '.$citylist[$row['city']].'<br>';
			$rtn .= Yii::t('workflow','Requestor').': '.$row['disp_name'].'<br>';
			$rtn .= Yii::t('trans','Payee').': '.$row['payee_name'].'<br>';
			$rtn .= Yii::t('trans','Paid Item').': '.$row['acct_code'].' '.(empty($row['acct_code']) ? '' : $codelist[$row['acct_code']]).'<br>';
			$rtn .= Yii::t('trans','Amount').': '.$row['amount'].'<br>';
			$rtn .= Yii::t('trans','Integrated Fee').': '.($row['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')).'<br>';
			if (!empty($row['reason']))
				$rtn .= Yii::t('trans','Reason').': '.$row['reason'].'<br>';
		}
		return $rtn;
	}
	
	protected function emailGeneric($params) {
		$toaddr = (isset($params['to_addr'])) ? $params['to_addr'] : $this->getCurrentStateRespEmail();
		$ccaddr = (isset($params['cc_addr'])) ? $params['cc_addr'] : array();
		$subjectPrefix = isset($params['subjtype']) 
			? ($params['subjtype']=='action' 
				? Yii::t('workflow','[Action]') 
				: ($params['subjtype']=='notice' ? Yii::t('workflow','[Notice]') : '')
			).' ' 
			: '';
		$subject = $subjectPrefix.Yii::t('app','Accounting').': '.$params['subject'];
		$description = Yii::t('app','Accounting').': '.$params['desc'];
		$message = $params['message'];
		return array(
				'from_addr'=>Yii::app()->params['adminEmail'],
				'to_addr'=>json_encode($toaddr),
				'cc_addr'=>json_encode($ccaddr),
				'subject'=>$subject,
				'description'=>$description,
				'message'=>$message,
			);
	}

	public function emailPA() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$url = Yii::app()->createAbsoluteUrl('apprreq/edit',array('index'=>$docId));
		
		$v = array();
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 new payment request for ').Yii::t('workflow','approval')
			.' ('.Yii::t('trans','Ref. No.').' '.$refno.')';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to carry out your job."));
		$msg = $this->requestDetail($docId);
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";
		$approver = $this->emailGeneric($v);

		$v['to_addr'] = $this->getCurrentStateRespStandbyEmail();
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','One new payment request for ').Yii::t('workflow','approval')
			.' ('.Yii::t('trans','Ref. No.').' '.$refno.')';
		$v['message'] = "<p>$msg</p>";
		$others = $this->emailGeneric($v);
		
		$rtn = array();
		$rtn[] = $approver;
		if (!empty($others)) $rtn[] = $others;
		return $rtn;
	}
	
	public function emailPS() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$url = Yii::app()->createAbsoluteUrl('signreq/edit',array('index'=>$docId));
		
		$v = array();
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 new reimbursement for ').Yii::t('workflow','approval')
			.' ('.Yii::t('trans','Ref. No.').' '.$refno.')';
		$v['desc'] = Yii::t('trans','Reimbursement Approval');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to carry out your job."));
		$msg = $this->requestDetail($docId);
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";
		
		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailA() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr = array($this->getEmail($user));
		$ccaddr = $this->getLastStateRespEmail();
		$cc = $this->getLastStateRespStandbyEmail();
		if (!empty($cc)) $ccaddr = array_merge($ccaddr, $cc);
		$approver = $this->getLastStateActionRespUser('APPROVE');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}
		
		$v = array();
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been approved').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Approved');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailD() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$cc = $this->getLastStateRespStandbyEmail();
		if (!empty($cc)) $ccaddr = array_merge($ccaddr, $cc);
		$approver = $this->getLastStateActionRespUser('DENY');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}

		$v = array();
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been denied').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Denied');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailSI() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$toaddr = $this->getLastStateRespEmail();
		$user = $this->getRequestData('REQ_USER');
		$toaddr[] = $this->getEmail($user);
		$approver = $this->getLastStateActionRespUser('REIMAPPR');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}
		
		$v = array();
		$v['to_addr'] = $toaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Reimbursement has been approved').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Reimbursement');
		$msg1 = Yii::t('workflow','Reimbursement Approved');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailC() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$toaddr = $this->getLastStateRespEmail();
		$ccaddr = $this->getLastStateRespStandbyEmail();

		$v = array();
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been cancelled').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Cancelled');
		$msg2 = $this->requestDetail($docId);
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailRC() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$toaddr = $this->getLastStateRespEmail();

		$v = array();
		$v['to_addr'] = $toaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Reimbursement has been cancelled').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Reimbursement');
		$msg1 = Yii::t('workflow','Reimbursement Cancelled');
		$msg2 = $this->requestDetail($docId);
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function generateTransaction() {
		$reqId = $this->getDocId();
		$acctreq = $this->getAccRequestRecord($reqId);
		$acctreqinfo = $this->getAccRequestInfoRecord($reqId);
		$record = array_merge($acctreq, $acctreqinfo);
		
		if (!isset($record['trans_id']) || empty($record['trans_id'])) { 
			$transId = $this->genAccTransRecord($record);
			$this->genAccTransInfoRecord($transId, $record);
		} else {
			$transId = $record['trans_id'];
		}
		$this->genDocmanRecord($reqId, $transId);
		
		if ($record['payee_type']=='A') {
			if (!isset($record['trans_id_c']) || empty($record['trans_id_c'])) { 
				$transId = $this->genCounterAccTransRecord($record);
				$this->genCounterAccTransInfoRecord($transId, $record);
			} else {
				$transId = $record['trans_id_c'];
			}
			$this->genDocmanRecord($reqId, $transId);
		}
	}
	
	public function genDocmanRecord($reqId, $transId) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select id from docman$suffix.dm_master 
				where doc_id=$reqId and doc_type_code in ('PAYREQ','PAYREAL','TAX')
					and remove<>'Y'
				limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		if ($row!==false) {
			$sql = "insert into docman$suffix.dm_master
						(doc_type_code, doc_id, remove, lcu, luu)
					values
						('TRANS', $transId, 'N', 'admin', 'admin')
				";
			$this->connection->createCommand($sql)->execute();
			$mastId = $this->connection->getLastInsertID();
			
			$sql = "insert into docman$suffix.dm_file
						(mast_id, phy_file_name, phy_path_name, display_name, file_type, archive, remove, lcu, luu)
					select
						$mastId, a.phy_file_name, a.phy_path_name, a.display_name, a.file_type, 'N', 'N', 'admin', 'admin'
					from
						docman$suffix.dm_file a, docman$suffix.dm_master b
					where
						a.mast_id=b.id and a.remove<>'Y' and
						b.doc_id=$reqId and b.doc_type_code in ('PAYREQ','PAYREAL','TAX') and
						b.remove<>'Y'
				";
			$this->connection->createCommand($sql)->execute();
		}
	}
	
	public function genAccTransInfoRecord($transId, $req) {
		$list = array(
					'payer_type'=>'payee_type',
					'payer_id'=>'payee_id',
					'payer_name'=>'payee_name',
					'cheque_no'=>'cheque_no',
					'invoice_no'=>'invoice_no',
					'acct_code'=>'acct_code',
					'item_code'=>'item_code',
					'int_fee'=>'int_fee',
					'req_ref_no'=>'ref_no',
			);
		$sql = "insert into acc_trans_info
					(trans_id, field_id, field_value, lcu, luu)
				values
					(:trans_id, :field_id, :field_value, :lcu, :luu)
			";
		foreach ($list as $key=>$value) {
			$command = $this->connection->createCommand($sql);
			if (strpos($sql,':trans_id')!==false)
				$command->bindParam(':trans_id',$transId,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false)
				$command->bindParam(':field_id',$key,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false)
				$command->bindParam(':field_value',$req[$value],PDO::PARAM_STR);
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$req['lcu'],PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$req['luu'],PDO::PARAM_STR);
			$command->execute();
		}
	}
	
	public function genAccTransRecord($req) {
		$sql = "insert into acc_trans
					(trans_dt, trans_type_code, acct_id, trans_desc, amount, status, city, lcu, luu)
				values
					(:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, 'Y', :city, :lcu, :luu)
			";
		$command = $this->connection->createCommand($sql);
		if (strpos($sql,':trans_dt')!==false) {
			$tdate = General::toMyDate($req['trans_dt']);
			$command->bindParam(':trans_dt',$tdate,PDO::PARAM_STR);
		}
		if (strpos($sql,':trans_type_code')!==false)
			$command->bindParam(':trans_type_code',$req['trans_type_code'],PDO::PARAM_STR);
		if (strpos($sql,':acct_id')!==false)
			$command->bindParam(':acct_id',$req['acct_id'],PDO::PARAM_INT);
		if (strpos($sql,':trans_desc')!==false)
			$command->bindParam(':trans_desc',$req['item_desc'],PDO::PARAM_STR);
		if (strpos($sql,':amount')!==false) {
			$amt = General::toMyNumber($req['amount']);
			$command->bindParam(':amount',$amt,PDO::PARAM_STR);
		}
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$req['city'],PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$req['luu'],PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$req['lcu'],PDO::PARAM_STR);
		$command->execute();

		return $this->connection->getLastInsertID();
	}
	
	public function genCounterAccTransRecord($req) {
		$sql = "insert into acc_trans
					(trans_dt, trans_type_code, acct_id, trans_desc, amount, status, city, lcu, luu)
				values
					(:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, 'Y', :city, :lcu, :luu)
			";
		$command = $this->connection->createCommand($sql);
		if (strpos($sql,':trans_dt')!==false) {
			$tdate = General::toMyDate($req['trans_dt']);
			$command->bindParam(':trans_dt',$tdate,PDO::PARAM_STR);
		}
		if (strpos($sql,':trans_type_code')!==false) {
			$countertype = General::getCounterTransType($req['trans_type_code']);
			$countertype = empty($countertype) ? $req['trans_type_code'] : $countertype;
			$command->bindParam(':trans_type_code',$countertype,PDO::PARAM_STR);
		}
		if (strpos($sql,':acct_id')!==false) 
			$command->bindParam(':acct_id',$req['payee_id'],PDO::PARAM_INT);
		if (strpos($sql,':trans_desc')!==false)
			$command->bindParam(':trans_desc',$req['item_desc'],PDO::PARAM_STR);
		if (strpos($sql,':amount')!==false) {
			$amt = General::toMyNumber($req['amount']);
			$command->bindParam(':amount',$amt,PDO::PARAM_STR);
		}
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$req['city'],PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$req['luu'],PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$req['lcu'],PDO::PARAM_STR);
		$command->execute();

		return $this->connection->getLastInsertID();
	}

	public function genCounterAccTransInfoRecord($transId, $req) {
		$list = array(
					'payer_type'=>'payee_type',
					'payer_id'=>'acct_id',
					'payer_name'=>'acct_desc',
					'cheque_no'=>'cheque_no',
					'invoice_no'=>'invoice_no',
					'acct_code'=>'acct_code',
					'item_code'=>'item_code',
					'united_inv_no'=>'united_inv_no',
					'int_fee'=>'int_fee',
					'req_ref_no'=>'ref_no',
			);
		$sql = "insert into acc_trans_info
					(trans_id, field_id, field_value, lcu, luu)
				values
					(:trans_id, :field_id, :field_value, :lcu, :luu)
			";
		foreach ($list as $key=>$value) {
			$command = $this->connection->createCommand($sql);
			if (strpos($sql,':trans_id')!==false)
				$command->bindParam(':trans_id',$transId,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false)
				$command->bindParam(':field_id',$key,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false) {
				$fvalue = isset($req[$value]) ? $req[$value] : '';
				$command->bindParam(':field_value',$fvalue,PDO::PARAM_STR);
			}
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$req['lcu'],PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$req['luu'],PDO::PARAM_STR);
			$command->execute();
		}
	}
	
	protected function getAccRequestRecord($id) {
		$sql = "select * from acc_request where id=$id";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row!==false) ? $row : array());
	}
	
	protected function getAccRequestInfoRecord($reqId) {
		$sql = "select field_id, field_value from acc_request_info where req_id=$reqId";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (empty($rows)) return array();
		$rtn = array();
		foreach ($rows as $row) {
			$rtn[$row['field_id']] = $row['field_value'];
			if ($row['field_id']=='acct_id') {
				$aid = $row['field_value'];
				$sqla = "select acct_name, acct_no from acc_account where id=$aid";
				$ac = $this->connection->createCommand($sqla)->queryRow();
				$rtn['acct_desc'] = ($ac===false) ? '' : (empty($ac['acct_name']) ? '' : $ac['acct_name']).(empty($ac['acct_no']) ? '' : ' ('.$ac['acct_no'].')');
			}
		}
		return $rtn;
	}
}
?>