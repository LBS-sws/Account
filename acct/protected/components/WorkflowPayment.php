<?php 
class WorkflowPayment extends WorkflowDMS {
	public function transitByAmount($args) {
//		var_dump($args);
		$params = json_decode($args);
		$amount = $this->getRequestData('AMOUNT');
//		var_dump($params);
		if ($amount > $params[0]) {
			$this->transit($params[1]);
		} else {
			$this->transit($params[2]);
		}
//		Yii::app()->end();
	}

	public function routeToManagerOrApprover() {
		$amount = $this->getRequestData('AMOUNT');
		if ($amount > 1000) {
			$this->routeToConfirmor();
//			$this->routeToManager();
		} else {
			$this->routeToApprover();
		}
	}
	
	public function routeToApprover() {
// Check Payee User ID 
		$payee = $this->getRequestData('PAYEE_USER');
// --
		$value = $this->getRequestData('AMOUNT');
		$amount = (empty($value)||!is_numeric($value)) ? 0 : $value;
		$listappr = array();
		if ($amount > 6000) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		} elseif ($amount > 3000) {
			$approver0 = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$approver1 = $this->getApprover('regionDirector');
			if (empty($payee) || $approver1!=$payee) {
				$listappr[$approver0] = 'S';
				$this->assignRespStandbyUser($approver0);
				$listappr[$approver1] = 'A';
				$this->assignRespUser($approver1);
			} else {
				$listappr[$approver0] = 'A';
				$this->assignRespUser($approver0);
			}
		} elseif ($amount > 1000) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			$approver0 = $this->getApprover('regionDirector');
			$approver1 = $this->getApprover('regionDirectorA');
			if (empty($payee) || $approver1!=$payee) {
				$listappr[$approver0] = 'S';
				$this->assignRespStandbyUser($approver0);
				$listappr[$approver1] = 'A';
				$this->assignRespUser($approver1);
			} else {
				$listappr[$approver0] = 'A';
				$this->assignRespUser($approver0);
			}
		} elseif ($amount > 500) {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			$approver = $this->getApprover('regionDirector');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			$approver0 = $this->getApprover('regionDirectorA');
			$approver1 = $this->getApprover('regionMgr');
			if (empty($payee) || $approver1!=$payee) {
				$listappr[$approver0] = 'S';
				$this->assignRespStandbyUser($approver0);
				$listappr[$approver1] = 'A';
				$this->assignRespUser($approver1);
			} else {
				$listappr[$approver0] = 'A';
				$this->assignRespUser($approver0);
			}
		} else {
			$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			
			$approver = $this->getApprover('regionDirector');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			
			$approver = $this->getApprover('regionDirectorA');
			$listappr[$approver] = 'S';
			$this->assignRespStandbyUser($approver);
			
			$approver0 = $this->getApprover('regionMgr');
			$approver1 = $this->getApprover('regionMgrA');
			$approver2 = $this->getApprover('regionSuper');
			if ($approver1!=$approver2 && !empty($approver2)) {
				$listappr[$approver0] = 'S';
				$this->assignRespStandbyUser($approver0);
				if (empty($payee) || $approver1!=$payee) {
					$listappr[$approver1] = 'A';
					$this->assignRespUser($approver1);
				}
				if (empty($payee) || $approver2!=$payee) {
					$listappr[$approver2] = 'A';
					$this->assignRespUser($approver2);
				}
			} else {
				if (empty($payee) || $approver1!=$payee) {
					$listappr[$approver0] = 'S';
					$this->assignRespStandbyUser($approver0);
					$listappr[$approver1] = 'A';
					$this->assignRespUser($approver1);
				} else {
					$listappr[$approver0] = 'A';
					$this->assignRespStandbyUser($approver0);
				}
			}
		}
//		var_dump($listappr);
		$this->assignDelegatedUser($listappr);
//		Yii::app()->end();
	}

	public function routeToManager() {
		$approver = $this->getApprover('regionMgr');
		$listappr[$approver] = 'A';
		$this->assignRespUser($approver);
		$this->assignDelegatedUser($listappr);
	}
	
	public function routeToConfirmor() {
		$listappr = array();
/*
		$approver = $this->getApprover('regionHead'); //$this->seekBoss('CN');
		if ($this->hasRight($approver,'XA08') && !in_array($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
		$approver = $this->getApprover('regionDirector');
		if ($this->hasRight($approver,'XA08') && !in_array($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
		$approver = $this->getApprover('regionDirectorA');
		if ($this->hasRight($approver,'XA08') && !in_array($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
*/
		$approver = $this->getApprover('regionMgr');
		if (!empty($approver) && $this->hasRight($approver,'XA08') && !array_key_exists($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
		$approver = $this->getApprover('regionMgrA');
		if (!empty($approver) && $this->hasRight($approver,'XA08') && !array_key_exists($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
		$approver = $this->getApprover('regionSuper');
		if (!empty($approver) && $this->hasRight($approver,'XA08') && !array_key_exists($approver, $listappr)) {
			$listappr[$approver] = 'A';
			$this->assignRespUser($approver);
		}
		$this->assignDelegatedUser($listappr);
	}

	public function routeToSigner() {
		$listappr = array();
		$signer = $this->getRequestData('APPROVER');
		$listappr[$signer] = 'A';
		$this->assignRespUser($signer);
		$this->assignOriginalUser($signer);
		$this->assignDelegatedUser($listappr);
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
		$city = $this->getRequestData('CITY');
		$sql = "select username from acc_approver where city='$city' and approver_type='$type'";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row===false) ? '' : $row['username'];
	}

	protected function getDelegated($approvers) {
		$rtn = array();
		$sql = "select delegated from acc_delegation where username in ($approvers)";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[] = $row['delegated'];
			}
		}
		return $rtn;
	}
	
	protected function assignDelegatedUser($list) {
		if (!empty($list)) {
			$appr = array();
			$listA = '';
			$listS = '';
			foreach ($list as $user=>$type) {
//				var_dump($user);
				if (!empty($user)) {
					$appr[] = $user;
					$item = "'$user'";
					
					if ((strpos($listA, $item)===false) && (strpos($listS, $item)===false)) {
						if ($type=='A') {
							$listA .= empty($listA) ? $item : ','.$item;
						} else {
							$listS .= empty($listS) ? $item : ','.$item;
						}
					}
				}
			}
//			var_dump($listA);
//			var_dump($listS);
			$deleA = empty($listA) ? array() : $this->getDelegated($listA);
			$deleS = empty($listS) ? array() : $this->getDelegated($listS);
//			var_dump($deleA);
//			var_dump($deleS);
			foreach ($deleA as $user) {
				if (!in_array($user,$appr)) $this->assignRespUser($user);
			}
			foreach ($deleS as $user) {
				if (!in_array($user,$deleA) && !in_array($user,$appr)) $this->assignRespStandbyUser($user);
			}
		}
	}
	
	protected function assignOriginalUser($user) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select username from acc_delegation where delegated = '$user'";
		$origs = $this->connection->createCommand($sql)->queryAll();
		if (count($origs) > 0) {
			$approvers = array();
			foreach ($origs as $orig) {
				$approvers[] = $orig['username'];
			}
			$rid = $this->request_id;
			$sql = "select distinct username from workflow$suffix.wf_request_resp_user where request_id=$rid and status='T'";
			$actors = $this->connection->createCommand($sql)->queryAll();
			foreach ($actors as $actor) {
				if (in_array($actor['username'],$approvers)) $this->assignRespUser($actor['username']);
			}
		}
	}
	
	protected function getAccountStaff() {
		$city = Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.username
				from security$suffix.sec_user a, security$suffix.sec_user_access b
				where a.username=b.username and a.city='$city' 
				and b.a_control like '%CN04%' and a.status='A' and b.system_id='acct'
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
		$username = array();
		$state = isset($params['state']) ? $params['state'] : '';

		$toaddr = (isset($params['to_addr'])) ? $params['to_addr'] : $this->getCurrentStateRespEmail();
		$temp = array();
		foreach ($toaddr as $key=>$email) {
			if ($this->canNotify($key) && !$this->skipNotify($key,$state)) {
				if (!in_array($key,$username)) $username[] = $key;
				if (!in_array($email,$temp)) $temp[] = $email;
			}
		}
		$toaddr = $temp;

		$ccaddr = (isset($params['cc_addr'])) ? $params['cc_addr'] : array();
		$temp = array();
		foreach ($ccaddr as $key=>$email) {
			if ($this->canNotify($key) && !$this->skipNotify($key,$state)) {
				if (!in_array($key,$username)) $username[] = $key;
				if (!in_array($email,$temp)) $temp[] = $email;
			}
		}
		$ccaddr = $temp;

		if (empty($toaddr) && !empty($ccaddr)) {
			$toaddr = $ccaddr;
			$ccaddr = array();
		}
		
		$subjectPrefix = isset($params['subjtype']) 
			? ($params['subjtype']=='action' 
				? Yii::t('workflow','[Action]') 
				: ($params['subjtype']=='notice' ? Yii::t('workflow','[Notice]') : '')
			).' ' 
			: '';
		$subject = $subjectPrefix.Yii::t('app','Accounting').': '.$params['subject'];
		$description = Yii::t('app','Accounting').': '.$params['desc'];
		$message = $params['message'];
		$note_type = isset($params['subjtype']) && $params['subjtype']=='notice' 
				? 'NOTI'
				: 'ACTN';
				
		return array(
				'send'=>$params['send'],
				'note_type'=>$note_type,
				'from_addr'=>Yii::app()->params['adminEmail'],
				'to_addr'=>json_encode($toaddr),
				'cc_addr'=>json_encode($ccaddr),
				'subject'=>$subject,
				'description'=>$description,
				'message'=>$message,
				'username'=>json_encode($username),
				'system_id'=>Yii::app()->user->system(),
				'form_id'=>$params['state'],
				'rec_id'=>$params['doc_id'],
			);
	}

	public function emailPA() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$url = Yii::app()->createAbsoluteUrl('apprreq/edit',array('index'=>$docId));
		
		$v = array();
		
		$v['doc_id'] = $docId;
		$v['state'] = 'PA';
		$v['send'] = 'Y';
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 new payment request for ').Yii::t('workflow','approval')
			.' ('.Yii::t('trans','Ref. No.').' '.$refno.')';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to carry out your job."));
		$msg = $this->requestDetail($docId);
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";
		$approver = $this->emailGeneric($v);

		$v['state'] = 'PA';
		$v['send'] = 'N';
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
	
	public function emailPB() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$url = Yii::app()->createAbsoluteUrl('confreq/edit',array('index'=>$docId));
		
		$v = array();
		
		$v['doc_id'] = $docId;
		$v['state'] = 'PB';
		$v['send'] = 'Y';
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 new payment request for ').Yii::t('workflow','confirmation')
			.' ('.Yii::t('trans','Ref. No.').' '.$refno.')';
		$v['desc'] = Yii::t('trans','Request Confirmation');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to carry out your job."));
		$msg = $this->requestDetail($docId);
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}
	
	public function emailPS() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$url = Yii::app()->createAbsoluteUrl('signreq/edit',array('index'=>$docId));
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'PS';
		$v['send'] = 'Y';
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
		$toaddr[$user] = $this->getEmail($user);
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

		$rtn = array();
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'A';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been approved').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Approved');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

		$v['send'] = 'N';
		$v['to_addr'] = $ccaddr;
		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailS() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$cc = $this->getLastStateRespStandbyEmail();
		if (!empty($cc)) $ccaddr = array_merge($ccaddr, $cc);
		$approver = $this->getLastStateActionRespUser('APPRNSIGN');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}

		$rtn = array();
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'S';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been approved and signed').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Approved and Signed');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

		$v['send'] = 'N';
		$v['to_addr'] = $ccaddr;
		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailAB() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$cc = $this->getLastStateRespStandbyEmail();
		if (!empty($cc)) $ccaddr = array_merge($ccaddr, $cc);
		$approver = $this->getLastStateActionRespUser('CONFIRM');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}

		$rtn = array();
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'AB';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been confirmed').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Confirmation');
		$msg1 = Yii::t('workflow','Request Confirmed');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Confirmor').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

//		$v['send'] = 'N';
//		$v['to_addr'] = $ccaddr;
//		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailD() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
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

		$rtn = array();

		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'D';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been denied').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Denied');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

		$v['send'] = 'N';
		$v['to_addr'] = $ccaddr;
		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailDB() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$cc = $this->getLastStateRespStandbyEmail();
		if (!empty($cc)) $ccaddr = array_merge($ccaddr, $cc);
		$approver = $this->getLastStateActionRespUser('CONFDENY');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}

		$rtn = array();

		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'DB';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request confirmation has been denied').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Confirmation');
		$msg1 = Yii::t('workflow','Request Confirmation Denied');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

//		$v['send'] = 'N';
//		$v['to_addr'] = $ccaddr;
//		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailSI() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$toaddr = $this->getLastStateRespEmail();
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
		$approver = $this->getLastStateActionRespUser('REIMAPPR');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'SI';
		$v['send'] = 'Y';
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

		$rtn = array();
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'C';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
//		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Payment request has been cancelled').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Request Approval');
		$msg1 = Yii::t('workflow','Request Cancelled');
		$msg2 = $this->requestDetail($docId);
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";
		$rtn[] = $this->emailGeneric($v);

		$v['send'] = 'N';
		$v['to_addr'] = $ccaddr;
		$rtn[] = $this->emailGeneric($v);
		
		return $rtn;
	}

	public function emailRC() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$appr = $this->getLastStateActionRespUserEx('APPROVE');
		$toaddr[$appr[0]] = $this->getEmail($appr[0]);
//		$toaddr = $this->getLastStateRespEmail();

		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'RC';
		$v['send'] = 'Y';
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

	public function emailRR() {
		$docId = $this->getDocId();
		$refno = $this->getRequestData('REF_NO');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[$user] = $this->getEmail($user);
		$approver = $this->getLastStateActionRespUserEx('REIMREJ');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}

		$url = Yii::app()->createAbsoluteUrl('realize/edit',array('index'=>$docId));
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to reapply the reimbursement."));
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['state'] = 'RR';
		$v['send'] = 'Y';
		$v['to_addr'] = $toaddr;
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','Reimbursement has been returned').' ('.Yii::t('trans','Ref. No.').' '.$refno.') ';
		$v['desc'] = Yii::t('trans','Reimbursement');
		$msg1 = Yii::t('workflow','Reimbursement Return');
		$msg2 = $this->requestDetail($docId);
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$msg2 .= '<br>'.$msg_url;
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
			$this->saveTransId($transId, $record);
		} else {
			$transId = $record['trans_id'];
		}
		$this->genDocmanRecord($reqId, $transId);
		
		if ($record['payee_type']=='A') {
			if (!isset($record['trans_id_c']) || empty($record['trans_id_c'])) { 
				$transId = $this->genCounterAccTransRecord($record);
				$this->genCounterAccTransInfoRecord($transId, $record);
				$this->saveTransId($transId, $record, 'C');
			} else {
				$transId = $record['trans_id_c'];
			}
			$this->genDocmanRecord($reqId, $transId);
		}
	}
	
	public function cancelTransaction() {
		$reqId = $this->getDocId();
		$acctreq = $this->getAccRequestRecord($reqId);
		$acctreqinfo = $this->getAccRequestInfoRecord($reqId);
		$record = array_merge($acctreq, $acctreqinfo);
		
		if (isset($record['trans_id']) && !empty($record['trans_id'])) { 
			$transId = $record['trans_id'];
			$sql = "update acc_trans set status='V', luu='admin' where id=$transId";
			$this->connection->createCommand($sql)->execute();

			$sql = "update acc_request_info set field_value='', luu='admin' where req_id=$reqId and field_id='trans_id'";
			$this->connection->createCommand($sql)->execute();
			
			if ($record['payee_type']=='A') {
				if (isset($record['trans_id_c']) && !empty($record['trans_id_c'])) { 
					$transIdc = $record['trans_id_c'];
					$sql = "update acc_trans set status='V', luu='admin' where id=$transIdc";
					$this->connection->createCommand($sql)->execute();

					$sql = "update acc_request_info set field_value='', luu='admin' where req_id=$reqId and field_id='trans_id_c'";
					$this->connection->createCommand($sql)->execute();
				}
			}
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

	protected function canNotify($userid) {
		$sql = "select status from acc_notify_option where username='$userid'";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row===false || $row['status']=='Y');
	}

	protected function skipNotify($userid, $state) {
		$sql = "select exclude_list from acc_email_exclude where username='$userid'";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row!==false && strpos($row['exclude_list'],'/'.$state.'/')!==false);
	}
	
	protected function saveTransId($transId, $req, $type='') {
		$sql = "insert into acc_request_info(
					req_id, field_id, field_value, luu, lcu) values (
					:id, :field_id, :field_value, :luu, :lcu)
					on duplicate key update
					field_value = :field_value, luu = :luu
				";

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$this->connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$req['id'],PDO::PARAM_INT);
		if (strpos($sql,':field_id')!==false) {
			$fldid = ($type=='C') ? 'trans_id_c' : 'trans_id';
			$command->bindParam(':field_id',$fldid,PDO::PARAM_STR);
		}
		if (strpos($sql,':field_value')!==false) 
			$command->bindParam(':field_value',$transId,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$req['lcu'],PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$req['lcu'],PDO::PARAM_STR);
		$command->execute();

		return true;
	}

	protected function hasRight($username, $right) {
		$city = Yii::app()->user->city();
		$sysid = Yii::app()->user->system();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.username
				from security$suffix.sec_user a, security$suffix.sec_user_access b
				where a.username=b.username and a.username='$username'
				and (b.a_read_write like '%$right%') and a.status='A' and b.system_id='$sysid' limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row!==false);
	}
}
?>