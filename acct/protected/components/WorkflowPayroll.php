<?php 
class WorkflowPayroll extends WorkflowDMS {
	protected $approvers;

	protected $approver_list;

	protected $functions = array('hasLevelOne', 'hasLevelTwo');

	public function hasLevelOne() {
		return !empty($this->approver_list[1]);
	}

	public function hasLevelTwo() {
		return !empty($this->approver_list[2]);
	}

	public function getApproverList() {
		$right_ad = $this->hasRight($this->approvers['regionDirectorA'], 'XS06');
		$right_m = $this->hasRight($this->approvers['regionMgr'], 'XS06');
		$right_am = $this->hasRight($this->approvers['regionMgrA'], 'XS06');
		$right_s = $this->hasRight($this->approvers['regionSuper'], 'XS06');

		$lv_1 = array();
		if ($right_m) $lv_1[] = $this->approvers['regionMgr'];
		if (!in_array($this->approvers['regionMgrA'], $lv_1) && $right_am) $lv_1[] = $this->approvers['regionMgrA'];
		if (!in_array($this->approvers['regionSuper'], $lv_1) && $right_s) $lv_1[] = $this->approvers['regionSuper'];

		$key = array_search($this->approvers['regionDirectorA'], $lv_1);
		if ($key!==false) unset($lv_1[$key]);
//		$key = array_search($this->approvers['regionDirector'], $lv_1);
//		if ($key!==false) unset($lv_1[$key]);

		$lv_2 = array();
		if ($this->approvers['regionDirector']!=$this->approvers['regionDirectorA']) {
//			if (empty($lv_1)) {
//				$lv_1[] = $this->approvers['regionDirectorA'];
//			} else {
				if ($right_ad) $lv_2[] = $this->approvers['regionDirectorA'];
//			}
		}

		$lv_3 = array();
		$lv_3[] = $this->approvers['regionDirector'];
		
		return array(
				1=>$lv_1,
				2=>$lv_2,
				3=>$lv_3,
			);
	}

	public function toManager() {
		foreach ($this->approver_list[1] as $user) {
			$this->assignRespUser($user);
//			foreach ($this->getDelegated("'$user'") as $dele) {
//				$this->assignRespUser($dele);
//			}
		}
	}

	public function toADirector() {
		foreach ($this->approver_list[2] as $user) {
			$this->assignRespUser($user);
//			foreach ($this->getDelegated("'$user'") as $dele) {
//				$this->assignRespUser($dele);
//			}
		}
	}

	public function toDirector() {
		foreach ($this->approver_list[3] as $user) {
			$this->assignRespUser($user);
//			foreach ($this->getDelegated("'$user'") as $dele) {
//				$this->assignRespUser($dele);
//			}
		}
	}

	public function toRequestor() {
		$user = $this->getRequestData('REQ_USER');
		$this->assignRespUser($user);
	}

	public function route($type='') {
		$transit = $this->getTransitionList();
		$prefix = $type=='denied' ? 'D' : ($type=='accepted' ? 'A' : null);
		
		$this->approvers = $this->getApprovers();
		$this->approver_list = $this->getApproverList();

		$path = array();
		$match = $this->getStateCode($this->current_state);
		$cnt = 0;
		$flag = true;
		while ($flag) {
			$cnt++;
			$choice = array();
			foreach ($transit as $item) {
				if ($item['from_code']==$match) $choice[$item['to_code']] = array('condition'=>$item['state_cond'],'to'=>$item['resp_party']);
			}

			if (empty($choice)) {	// no next state
				$flag = false;
			} else {		//have next states
				foreach ($choice as $code=>$item) {	
					$strtmp = substr($code,0,1);
					if ($strtmp=='A' || $strtmp=='D') {		// state start with 'A' or 'D'
						if ($strtmp===$prefix) {
							$path[$code] = $item['to'];
							$match = $code;
							break;
						}
					} elseif ($strtmp=='P') {		// state start with 'P'
//						if (empty($item['condition']) || (method_exists($this,$item['condition']) && call_user_func(array($this,$item['condition'])))) {
						if (empty($item['condition']) || $this->evaluate($item['condition'])) {
							$path[$code] = $item['to'];
							$match = $code;
							break;
						}
					} else {
						$path[$code] = $item['to'];
						$match = $code;
						break;
					}
				}
				if (substr($match,0,1)=='P' || $match=='ED') $flag = false;
			}
			if ($cnt > 30) $flag = false; // prevent endless loop
		}

		
		foreach ($path as $code=>$to) {
			if (!$this->transit($code)) break;
			if (!empty($to) && method_exists($this,$to)) call_user_func(array($this,$to));
		}
	}

	protected function evaluate($stmt) {
		foreach ($this->functions as $function) {
			if (strpos($stmt, $function)!==false) {
				$result = call_user_func(array($this,$function)) ? 'true' : 'false';
				$stmt = str_replace($function, $result , $stmt);
			}
		}
		$check = str_replace(array(' ','!','(',')','&','|','=','true','false'),'',$stmt);
		$rtn = empty($check) ? eval("return ($stmt);") : false;
		return $rtn===null ? false : $rtn;
	}

	protected function getApprovers() {
		$city = $this->getRequestData('CITY');
		$sql = "select approver_type, username from acc_approver where city='$city'";
		$rows = $this->connection->createCommand($sql)->queryAll();
		$rtn = array();
		foreach ($rows as $row) {
			$rtn[$row['approver_type']] = $row['username'];
		}
		return $rtn;
	}
	
	protected function getTransitionList() {
		$suffix = Yii::app()->params['envSuffix'];
		$procid = $this->proc_id;
		$sql = "select b.code as from_code, c.code as to_code, a.state_cond, a.resp_party
				from workflow$suffix.wf_transition a,
					workflow$suffix.wf_state b,
					workflow$suffix.wf_state c
				where a.current_state = b.id and a.next_state = c.id
				and a.proc_ver_id = $procid
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		return $rows;
	}

	protected function getStateCode($id) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select code from workflow$suffix.wf_state where id=$id";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? '' : $row['code']);
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

	protected function hasRight($user, $code) {
		$suffix = Yii::app()->params['envSuffix'];
		$sysid = Yii::app()->params['systemId'];
		$sql = "select username from security$suffix.sec_user_access 
				where username='$user' and system_id='$sysid' and a_read_write like '%$code%'
				limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row!==false);
	}

	public function sendEmail($state) {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');

		$this->approvers = $this->getApprovers();
		$this->approver_list = $this->getApproverList();

		$name = 'mail'.(empty($state) ? 'PendingState' : $state);
		if (method_exists($this, $name)) {
			$record = call_user_func_array(array($this, $name), array($docId, $year, $month, $cityname));

			$suffix = Yii::app()->params['envSuffix'];
			$suffix = $suffix=='dev' ? '_w' : $suffix;
			$sql = "insert into swoper$suffix.swo_email_queue
						(from_addr, to_addr, cc_addr, subject, description, message, status, lcu)
					values
						(:from_addr, :to_addr, :cc_addr, :subject, :description, :message, 'P', 'admin')
				";
			if (!empty($record['to_addr']) || !empty($record['cc_addr'])) {
				if (!isset($record['send']) || $record['send']=='Y') {
					$from_addr = Yii::app()->params['adminEmail'];
					$command = $this->connection->createCommand($sql);
					if (strpos($sql,':from_addr')!==false)
						$command->bindParam(':from_addr',$from_addr,PDO::PARAM_STR);
					if (strpos($sql,':to_addr')!==false)
						$command->bindParam(':to_addr',$record['to_addr'],PDO::PARAM_STR);
					if (strpos($sql,':cc_addr')!==false)
						$command->bindParam(':cc_addr',$record['cc_addr'],PDO::PARAM_STR);
					if (strpos($sql,':subject')!==false)
						$command->bindParam(':subject',$record['subject'],PDO::PARAM_STR);
					if (strpos($sql,':description')!==false)
						$command->bindParam(':description',$record['description'],PDO::PARAM_STR);
					if (strpos($sql,':message')!==false)
						$command->bindParam(':message',$record['message'],PDO::PARAM_STR);
					$command->execute();
				}
				$this->notification($record);
			}	
		}
	}

	protected function mailPendingState($docId, $year, $month, $cityname) {
		$state = $this->getStateCode($this->current_state);
		
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('', $username, $to_addr);

		$subject = $state=='PS'
				? Yii::t('workflow','You have 1 request for payroll file resubmission')." ($cityname, $year/$month)"
				: Yii::t('workflow','You have 1 request for payroll file approval')." ($cityname, $year/$month)";

		$description = $state=='PS'
				? Yii::t('workflow','Payroll File Submission')
				: Yii::t('workflow','Payroll File Approval');

		$url = $state=='PS'
			? Yii::app()->createAbsoluteUrl('payroll/edit',array('index'=>$docId))
			: Yii::app()->createAbsoluteUrl('payrollappr/edit',array('index'=>$docId));
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Account');\">here</a> to carry out your job."));
		$msg = $this->requestDetail();
		
		return array(
			'send'=>'Y',
			'note_type'=>'ACTN',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>$subject,
			'description'=>$description,
			'message'=>"<p>$msg</p><p>$msg_url</p>",
			'username'=>json_encode($username),
			'form_id'=>$state,
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}
	
	protected function mailAA($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('PA', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		foreach ($this->approver_list[2] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		foreach ($this->approver_list[1] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		$msg1 = Yii::t('workflow','Payroll File Approved');
		$msg2 = $this->requestDetail();
		$reason = $this->getCurrentStateRemarks(Yii::app()->user->id);
		if (!empty($reason)) $msg2 .= Yii::t('trans','Remarks').': '.$reason.'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been approved by Director')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'AB',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}
	
	protected function mailAB($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('P1', $username, $to_addr);
		if (empty($username) && empty($to_addr)) $this->getUsernameAndEmail('PB', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		$msg1 = Yii::t('workflow','Payroll File Approved');
		$msg2 = $this->requestDetail();
		$reason = $this->getCurrentStateRemarks(Yii::app()->user->id);
		if (!empty($reason)) $msg2 .= Yii::t('trans','Remarks').': '.$reason.'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been approved by Manager')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'AB',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}
	
	protected function mailAC($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('P2', $username, $to_addr);
		if (empty($username) && empty($to_addr)) $this->getUsernameAndEmail('PC', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		foreach ($this->approver_list[1] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		$msg1 = Yii::t('workflow','Payroll File Approved');
		$msg2 = $this->requestDetail();
		$reason = $this->getCurrentStateRemarks(Yii::app()->user->id);
		if (!empty($reason)) $msg2 .= Yii::t('trans','Remarks').': '.$reason.'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been approved by A.Director')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'AC',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}
	
	protected function mailDA($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('PA', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		foreach ($this->approver_list[2] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		foreach ($this->approver_list[1] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		$msg1 = Yii::t('workflow','Payroll File Denied');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('trans','Reason').': '.$this->getCurrentStateRemarks(Yii::app()->user->id).'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been denied by Director')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'DA',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}

	protected function mailDB($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('P1', $username, $to_addr);
		if (empty($username) && empty($to_addr)) $this->getUsernameAndEmail('PB', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		$msg1 = Yii::t('workflow','Payroll File Denied');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('trans','Reason').': '.$this->getCurrentStateRemarks(Yii::app()->user->id).'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been denied by Manager')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'DB',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}

	protected function mailDC($docId, $year, $month, $cityname) {
		$username = array();
		$to_addr = array();
		$this->getUsernameAndEmail('P2', $username, $to_addr);
		if (empty($username) && empty($to_addr)) $this->getUsernameAndEmail('PC', $username, $to_addr);

		$user = $this->getRequestData('REQ_USER');
		$username[] = $user;
		$to_addr[] = $this->getEmail($user);

		foreach ($this->approver_list[1] as $user) {
			if (!in_array($user, $username)) {
				$username[] = $user;
				$to_addr[] = $this->getEmail($user);
			}
		}

		$msg1 = Yii::t('workflow','Payroll File Denied');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('trans','Reason').': '.$this->getCurrentStateRemarks(Yii::app()->user->id).'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$this->getDisplayName(Yii::app()->user->id).'<br>';
		return array(
			'send'=>'Y',
			'note_type'=>'NOTI',
			'to_addr'=>json_encode($to_addr),
			'cc_addr'=>json_encode(array()),
			'subject'=>Yii::t('workflow','Payroll file has been denied by A.Director')." ($cityname, $year/$month)",
			'description'=>Yii::t('workflow','Payroll File Approval'),
			'message'=>"<p>$msg1</p><p>$msg2</p>",
			'username'=>json_encode($username),
			'form_id'=>'DC',
			'rec_id'=>$docId,
			'system_id'=>Yii::app()->user->system(),
		);
	}

	protected function requestDetail() {
		$user = $this->getRequestData('REQ_USER');
		$requser = $this->getDisplayName($user);
		
		$rtn = '';
		$rtn = Yii::t('workflow','Year/Month').': '.$this->getRequestData('YEAR').'/'.$this->getRequestData('MONTH').'<br>';
		$rtn .= Yii::t('misc','City').': '.$this->getRequestData('CITYNAME').'<br>';
		$rtn .= Yii::t('workflow','Requestor').': '.$requser.'<br>';
		return $rtn;
	}

	protected function getUsernameAndEmail($state, &$username, &$email) {
		$record = empty($state) ? $this->getCurrentStateRespEmail() : $this->getRespEmailByStateType($state);
		foreach ($record as $key=>$value) {
//			if ($this->canNotify($key) && !$this->skipNotify($key,$state)) {
				if (!in_array($key,$username)) $username[] = $key;
				if (!in_array($value,$email)) $email[] = $value;
//			}
		}
	}

	protected function getRespEmailByStateType($state) {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$logId = $this->transit_log_id;
		$sql = "select a.log_id, b.email, a.username  
				from workflow$suffix.wf_request_resp_user a, security$suffix.sec_user b,
					workflow$suffix.wf_state c
				where a.request_id=$reqId 
				and c.code = '$state'
				and a.current_state=c.id   
				and a.username=b.username
				order by a.log_id desc
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			$l = 0;
			foreach ($rows as $row) {
				if ($l==0) $l = $row['log_id'];
				if ($l!=$row['log_id']) break;
				if (!empty($row['email'])) $rtn[$row['username']] = $row['email'];
			}
		}
		return $rtn;
	}

	protected function getCurrentStateRemarks($userid) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$state = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select a.remarks
				from workflow$suffix.wf_request_resp_user a
				where a.request_id=$reqId
				and a.status='C'
				and a.username='$userid'
				order by id desc limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		if ($row===false) {
			return '';
		} else {
			return $row['remarks'];
		}
	}

	public function getLatestActionRemark() {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$state = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select b.log_id, b.username, b.remarks 
				from workflow$suffix.wf_request_resp_user b
				where b.request_id=$reqId
				and b.log_id<>$logId   
				and b.status='C'
				order by b.log_id desc limit 1
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (empty($rows)) {
			return array();
		} else {
			$rtn = array();
			$l = 0;
			foreach($rows as $row) {
				if ($l==0) $l = $row['log_id'];
				if ($l!=$row['log_id']) break;
				$rtn[$row['username']] = $row['remarks'];
			}
			return $rtn;
		}
	}
	
	public function rollbackFromEndState() {
		$state = $this->current_state;
		$proc = $this->proc_id;
		$reqid = $this->request_id;
		
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select code from workflow$suffix.wf_state where proc_ver_id=$proc and id=$state";
		$row = $this->connection->createCommand($sql)->queryRow();
		if ($row!==false && $row['code']=='ED') {
			$sql = "select current_state from workflow$suffix.wf_request_resp_user
					where request_id=$reqid
					order by id desc limit 1
				";
			$row = $this->connection->createCommand($sql)->queryRow();
			if ($row!==false) {
				$lastState = $row['current_state'];
				$log_ids = array();
				$transitId = 0;
				$sql = "select id, old_state, new_state from workflow$suffix.wf_request_transit_log
						where request_id=$reqid
						order by id desc
					";
				$records = $this->connection->createCommand($sql)->queryAll();
				foreach ($records as $record) {
					if ($record['new_state']!=$lastState) {
						$log_ids[] = $record['id'];
					} else {
						$transitId = $record['id'];
						break;
					}
				}

				if (!empty($log_ids) && $transitId > 0) {
					$idlist = implode(',',$log_ids);
					$sql = "delete from workflow$suffix.wf_request_transit_log where id in ($idlist)";
					$this->connection->createCommand($sql)->execute();

					$sql = "update workflow$suffix.wf_request_resp_user
							set status='P', action_id=0, remarks=''
							where request_id=$reqid and log_id=$transitId
						";
					$this->connection->createCommand($sql)->execute();
						
					$sql = "update workflow$suffix.wf_request set current_state=$lastState where id=$reqid";
					$this->connection->createCommand($sql)->execute();
				}
			}
		}
	}
}
?>