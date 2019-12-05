<?php 
class WorkflowOprpt extends WorkflowDMS {

	public function routeToRequestor() {
		$user = $this->getRequestData('REQ_USER');
		$this->assignRespUser($user);
	}

	public function routeToApprover() {
		$users = $this->getUserByControlRight(array('YN01'));
		if (empty($users)) {
			$user = $this->getRequestData('REQ_USER');
			$this->assignRespUser($user);
		} else {
			foreach ($users as $user) {
				$this->assignRespUser($user);
			}
		}
	}

	public function routeToManager() {
		$user = $this->seekManager();
		$this->assignRespUser($user);
	}

	protected function getUserByControlRight($access=array()) {
		$rtn = array();
		if (!empty($access)) {
			$city = Yii::app()->user->city();
			$citylist = City::model()->getAncestorList($city);
			$citylist = empty($citylist) ? "'$city'" : $citylist.",'$city'";
			$suffix = Yii::app()->params['envSuffix'];
			$clause = '';
			foreach ($access as $value) $clause .= ($clause=='' ? '' : ' or ')."b.a_control like '%$value%'";
			$sql = "select a.username
					from security$suffix.sec_user a, security$suffix.sec_user_access b
					where a.username=b.username and a.city in ($citylist)
					and ($clause) and a.status='A'
				";
			$rows = $this->connection->createCommand($sql)->queryAll();
			foreach ($rows as $row) $rtn[] = $row['username'];
		}
		return $rtn;
	}
	
	protected function emailGeneric($params) {
		$username = array();
		$state = isset($params['state']) ? $params['state'] : '';
		
		$toaddr = (isset($params['to_addr'])) ? $params['to_addr'] : $this->getCurrentStateRespEmail();
		$temp = array();
		foreach ($toaddr as $key=>$email) {
//			if ($this->canNotify($key) && !$this->skipNotify($key,$state)) {
				if (!in_array($key,$username)) $username[] = $key;
				if (!in_array($email,$temp)) $temp[] = $email;
//			}
		}
		$toaddr = $temp;

		$ccaddr = (isset($params['cc_addr'])) ? $params['cc_addr'] : array();
		$temp = array();
		foreach ($ccaddr as $key=>$email) {
//			if ($this->canNotify($key) && !$this->skipNotify($key,$state)) {
				if (!in_array($key,$username)) $username[] = $key;
				if (!in_array($email,$temp)) $temp[] = $email;
//			}
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
		$subject = $subjectPrefix.Yii::t('app','Operation').': '.$params['subject'];
		$description = Yii::t('app','Operation').': '.$params['desc'];
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
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$url = Yii::app()->createAbsoluteUrl('monthly/view',array('index'=>$docId,'rtn'=>'indexa'));
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'PA';
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 request for report approval')
			.' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Operation');\">here</a> to carry out your job."));
		$msg = $this->requestDetail();
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";
		
		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailPH() {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$url = Yii::app()->createAbsoluteUrl('monthly/view',array('index'=>$docId,'rtn'=>'indexa'));
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'PH';
		$v['subjtype'] = 'action';
		$v['subject'] = Yii::t('workflow','You have 1 request for report approval')
			.' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg_url = str_replace('{url}',$url, Yii::t('workflow',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Operation');\">here</a> to carry out your job."));
		$msg = $this->requestDetail();
		$v['message'] = "<p>$msg</p><p>$msg_url</p>";
		
		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}
	
	public function emailA() {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$user = $this->getRequestData('REQ_USER');

		$toaddr = array($this->getEmail($user));
		$ccaddr = $this->getLastStateRespEmail();
		$approver = $this->getLastStateActionRespUser('APPROVE');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'A';
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Sales report has been approved by HQ').' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg1 = Yii::t('workflow','Sales Report Approved');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}

	public function emailAH() {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$user = $this->getRequestData('REQ_USER');

		$toaddr = array($this->getEmail($user));
		$ccaddr = $this->getLastStateRespEmail();
		$approver = $this->getLastStateActionRespUser('HDAPPROVE');
		$apprname = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
			}
		}
		
		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'AH';
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Sales report has been approved by Manager').' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg1 = Yii::t('workflow','Sales Report Approved');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}
	
	public function emailD() {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$approver = $this->getLastStateActionRespUser('DENY');
		$apprname = "";
		$reason = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
				$reason .= (($reason=="") ? "" : "<br>").$this->getCurrentStateRemarks($user);
			}
		}

		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'D';
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Sales report has been denied by HQ').' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg1 = Yii::t('workflow','Sales Report Denied');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('workflow','Reason').': '.$reason.'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
	}
	
	public function emailDH() {
		$docId = $this->getDocId();
		$year = $this->getRequestData('YEAR');
		$month = $this->getRequestData('MONTH');
		$cityname = $this->getRequestData('CITYNAME');
		$user = $this->getRequestData('REQ_USER');
		$toaddr[] = $this->getEmail($user);
		$ccaddr = $this->getLastStateRespEmail();
		$approver = $this->getLastStateActionRespUser('HDDENY');
		$apprname = "";
		$reason = "";
		if (!empty($approver)) {
			foreach ($approver as $user) {
				$apprname .= (($apprname=="") ? "" : ", ").$this->getDisplayName($user);
				$reason .= (($reason=="") ? "" : "<br>").$this->getCurrentStateRemarks($user);
			}
		}

		$v = array();
		$v['doc_id'] = $docId;
		$v['send'] = 'Y';
		$v['state'] = 'DH';
		$v['to_addr'] = $toaddr;
		$v['cc_addr'] = $ccaddr;
		$v['subjtype'] = 'notice';
		$v['subject'] = Yii::t('workflow','Sales report has been denied by Manager').' ('.$cityname.', '.$year.'/'.$month.')';
		$v['desc'] = Yii::t('workflow','Report Approval');
		$msg1 = Yii::t('workflow','Sales Report Denied');
		$msg2 = $this->requestDetail();
		$msg2 .= Yii::t('workflow','Reason').': '.$reason.'<br>';
		$msg2 .= Yii::t('workflow','Approver').': '.$apprname.'<br>';
		$v['message'] = "<p>$msg1</p><p>$msg2</p>";

		$rtn = array();
		$rtn[] = $this->emailGeneric($v);
		return $rtn;
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

}
?>