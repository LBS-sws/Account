<?php 
class Workflow {
	public $connection;
	public $transaction;
	
	public $proc_id;
	public $request_id;
	public $current_state;
	public $transit_log_id;
	
	public function openConnection() {
		$this->connection = Yii::app()->db;
		$this->transaction=$this->connection->beginTransaction();
		return $this->connection;
	}
	
	public function startProcess($procCode, $docId, $reqDate) {
		$rtn = true;
		$suffix = Yii::app()->params['envSuffix'];
		$this->proc_id = $this->getProcessId($procCode, $reqDate);
		$procId = $this->proc_id;
		$sql = "select id, current_state from workflow$suffix.wf_request
				where proc_ver_id=$procId and doc_id=$docId
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		if ($row===false) {
			$stateId = $this->getStateId($procId,'ST');
			if ($stateId!=0) {
				$sql2 = "insert into workflow$suffix.wf_request(proc_ver_id, current_state, doc_id)
						values($procId, $stateId, $docId)
					";
				$this->connection->createCommand($sql2)->execute();
				$this->request_id = $this->connection->getLastInsertID();
				$this->current_state = $stateId;
				$this->transit_log_id = 0;
			} else {
				$rtn = false;
			}
		} else {
			$this->request_id = $row['id'];
			$this->current_state = $row['current_state'];
			$this->transit_log_id = $this->getTransitLogId($this->request_id, $this->current_state);
		}
		return $rtn;
	}
	
	public function genTableStateList() {
		$rtn = "";
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$sql = "select a.id, a.lcd, b.name, c.username as targetuser, d.id as resp_id, d.username as actionuser
				from workflow$suffix.wf_request_transit_log a
				inner join workflow$suffix.wf_state b on a.new_state=b.id
				left outer join workflow$suffix.wf_request_resp_user c on a.id=c.log_id
				left outer join workflow$suffix.wf_request_resp_user d on a.request_id=d.request_id 
					and a.old_state=d.current_state and d.status='C'
					and a.id > d.log_id
				where a.request_id=$reqId
				order by a.id, d.id desc
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (empty($rows)) {
			$msg = Yii::t('dialog','No Record');
			$rtn = "<tr><td>&nbsp;</td><td>$msg</td></tr>";
		} else {
			$lid = 0;
			$date = '';
			$state = '';
			$user = '';
			foreach ($rows as $row) {
				if ($lid==0) {
					$d = $row['lcd'];
					$u = $this->getRequestData('REQ_USER');
					$s = Yii::t('workflow','Submit (Start)');
					$rtn = "<tr><td>$d</td><td>$s</td><td>$u</td></tr>";
					$lid = $row['id'];
				}
				if ($lid!=$row['id']) {
					$rtn .= "<tr><td>$date</td><td>$state</td><td>$user</td></tr>";
					$user = "";
				}
				$date = $row['lcd'];
				$state = $row['name'];
				$u = $this->getDisplayName($row['targetuser']);
				$user .= ($user=="" ? "" : ", ").(empty($u) ? $this->getDisplayName($row['actionuser']) : $u);
			}
			$rtn .= "<tr><td>$date</td><td>$state</td><td>$user</td></tr>";
		}
		return $rtn;
	}
	
	public function takeAction($actionCode) {
		$suffix = Yii::app()->params['envSuffix'];
		$actionId = $this->getActionId($this->proc_id, $actionCode);

		$user = Yii::app()->user->id;
		$reqId = $this->request_id;
		$state = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "update workflow$suffix.wf_request_resp_user
				set status='C', action_id=$actionId 
				where request_id=$reqId
				and log_id=$logId
				and current_state=$state
				and username='$user'
			";
		$this->connection->createCommand($sql)->execute();

		$sql = "select b.name, b.function_call, b.param, b.proc_ver_id 
				from workflow$suffix.wf_action_task a, workflow$suffix.wf_task b 
				where a.action_id=$actionId and a.task_id=b.id
				order by a.seq_no
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$func_name = (strpos($row['function_call'],'::')!==false) ? $row['function_call'] : array($this, $row['function_call']);
				$params = explode('~',$row['param']);
				$result = call_user_func_array($func_name, $params);
			}
		}
	}

	public function transit($stateCode) {
		$targetState = $this->getStateId($this->proc_id, $stateCode);
		if ($this->isValidMove($this->proc_id, $this->current_state, $targetState)) {
			$suffix = Yii::app()->params['envSuffix'];
			$reqId = $this->request_id;
			$oldState = $this->current_state;
			$sql = "insert into workflow$suffix.wf_request_transit_log(request_id, old_state, new_state)
					values($reqId, $oldState, $targetState)
				";
			$this->connection->createCommand($sql)->execute();
			$this->transit_log_id = $this->connection->getLastInsertID();
				
			$sql = "update workflow$suffix.wf_request
					set current_state=$targetState
					where id=$reqId
				";
			$this->connection->createCommand($sql)->execute();
			$this->current_state = $targetState;
		} else {
			return false;
		}
		return true;
	}
	
	public function assignRespUser($user) {
		return $this->assignRespUserByType($user, 'P');
	}

	public function assignRespStandbyUser($user) {
		return $this->assignRespUserByType($user, 'Q');
	}
	
	public function assignRespUserByType($user, $type) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$currentState = $this->current_state;
		$logId = $this->transit_log_id;
		if ($logId > 0) {
			$sql = "insert into workflow$suffix.wf_request_resp_user(request_id, log_id, current_state, username, status, action_id)
					values($reqId, $logId, $currentState, '$user', '$type', 0)
				";
			$this->connection->createCommand($sql)->execute();
		} else {
			return false;
		}
		return true;
	}

	public function clearAllPending() {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$sql = "update workflow$suffix.wf_request_resp_user set status='T' 
				where request_id = $reqId and status in ('P','Q')
			";
		$this->connection->createCommand($sql)->execute();
	}
	
	public function sendEmail() {
		$suffix = Yii::app()->params['envSuffix'];
		$state = $this->current_state;
		$sql = "select code from workflow$suffix.wf_state
				where id=$state
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		$statecode = ($row!==false) ? $row['code'] : '';
		
		$name = 'email'.$statecode;
		if (!empty($statecode) && method_exists($this, $name)) {
			$func = array($this, $name);
			$params = call_user_func_array($func, array());
			
			$sql = "insert into swoper_w.swo_email_queue
						(from_addr, to_addr, cc_addr, subject, description, message, status, lcu)
					values
						(:from_addr, :to_addr, :cc_addr, :subject, :description, :message, 'P', 'admin')
				";
			foreach ($params as $record) {
				$command = $this->connection->createCommand($sql);
				if (strpos($sql,':from_addr')!==false)
					$command->bindParam(':from_addr',$record['from_addr'],PDO::PARAM_STR);
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
		}
	}

	public function getRequestData($code) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		
		$sql = "select data_value from workflow$suffix.wf_request_data
				where request_id=$reqId and data_name='$code'
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? '' : $row['data_value']);
	}
	
	public function saveRequestData($code, $value) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		
		$sql = "select id from workflow$suffix.wf_request_data
				where request_id=$reqId and data_name='$code'
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		
		$sql = ($row===false)
				? "insert into workflow$suffix.wf_request_data(request_id, data_name, data_value)
					values($reqId, '$code', '$value')
				"
				: "update workflow$suffix.wf_request_data set data_value='$value'
					where request_id=$reqId and data_name='$code'
				";
		$this->connection->createCommand($sql)->execute();
	}

	public function getPendingRequestIdList($procCode, $state, $user) {
		return $this->getPendingRequestIdListByType($procCode, $state, $user, 'P');
	}
	
	public function getPendingStandbyRequestIdList($procCode, $state, $user) {
		return $this->getPendingRequestIdListByType($procCode, $state, $user, 'Q');
	}

	public function getPendingRequestIdListByType($procCode, $state, $user, $type) {
		$rtn = '';
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select e.doc_id 
				from workflow$suffix.wf_request_resp_user a,
					workflow$suffix.wf_state b,
					workflow$suffix.wf_process_version c,
					workflow$suffix.wf_process d,
					workflow$suffix.wf_request e
				where a.current_state = b.id
				and b.proc_ver_id = c.id
				and c.process_id = d.id
				and d.code = '$procCode'
				and b.code = '$state'
				and a.username = '$user'
				and a.status = '$type'
				and a.request_id = e.id
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn .= (($rtn=='') ? '' : ',').$row['doc_id'];
			}
		}
		return $rtn;
	}

	protected function getCurrentStateRespUser() {
		return $this->getCurrentStateRespUserByType('P');
	}
	
	protected function getCurrentStateRespStandbyUser() {
		return $this->getCurrentStateRespUserByType('Q');
	}
	
	protected function getCurrentStateRespUserByType($type) {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$stateId = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select username from workflow$suffix.wf_request_resp_user
				where request_id=$reqId and log_id=$logId and current_state=$stateId and status='$type'
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn[] = $row['username'];
			}
		}
		return $rtn;
	}

	protected function getCurrentStateRespEmail() {
		return $this->getCurrentStateRespEmailByType('P');
	}
	
	protected function getCurrentStateRespStandbyEmail() {
		return $this->getCurrentStateRespEmailByType('Q');
	}
	
	protected function getCurrentStateRespEmailByType($type) {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$stateId = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select b.email 
				from workflow$suffix.wf_request_resp_user a, security$suffix.sec_user b
				where a.request_id=$reqId and a.log_id=$logId and a.current_state=$stateId and a.status='$type'  
				and a.username=b.username
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn[] = $row['email'];
			}
		}
		return $rtn;
	}

	protected function getLastStateRespEmail() {
		return $this->getLastStateRespEmailByType('P');
	}
	
	protected function getLastStateRespStandbyEmail() {
		return $this->getLastStateRespEmailByType('Q');
	}
	
	protected function getLastStateRespEmailByType($type) {
		$rtn = array();
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$stateId = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select b.log_id, c.email 
				from workflow$suffix.wf_request_transit_log a, 
					workflow$suffix.wf_request_resp_user b, 
					security$suffix.sec_user c
				where a.id=$logId and a.request_id=$reqId 
				and a.request_id=b.request_id 
				and b.current_state=a.old_state   
				and b.status='$type'
				and b.username=c.username
				order by b.log_id desc
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			$l = 0;
			foreach ($rows as $row) {
				if ($l==0) $l = $row['log_id'];
				if ($l!=$row['log_id']) break;
				$rtn[] = $row['email'];
			}
		}
		return $rtn;
	}

	protected function getActionRespUser($action) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$state = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select a.username
				from workflow$suffix.wf_request_resp_user a, workflow$suffix.wf_action b
				where a.request_id=$reqId
				and a.log_id=$logId
				and a.current_state=$state
				and a.status='C'
				and a.action_id=b.id
				and b.code='$action'
			";
		$rows = $this->connection->createCommand($sql)->queryAll();
		if (empty($rows)) {
			return array();
		} else {
			$rtn = array();
			foreach($rows as $row) {
				$rtn[] = $row['username'];
			}
			return $rtn;
		}
	}
	
	protected function getLastStateActionRespUser($action) {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$state = $this->current_state;
		$logId = $this->transit_log_id;
		$sql = "select b.log_id, b.username
				from workflow$suffix.wf_request_transit_log a,
					workflow$suffix.wf_request_resp_user b, 
					workflow$suffix.wf_action c
				where a.request_id=$reqId and a.id=$logId
				and a.request_id=b.request_id 
				and b.current_state=a.old_state   
				and b.status='C'
				and b.action_id=c.id
				and c.code='$action'
				order by b.log_id desc
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
				$rtn[] = $row['username'];
			}
			return $rtn;
		}
	}

	protected function getDisplayName($user) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select disp_name from security$suffix.sec_user 
				where username='$user'
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row!==false) ? $row['disp_name'] : '');
	}
	
	protected function getEmail($user) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select email from security$suffix.sec_user 
				where username='$user'
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row!==false) ? $row['email'] : '');
	}
	
	protected function getDocId() {
		$suffix = Yii::app()->params['envSuffix'];
		$reqId = $this->request_id;
		$sql = "select doc_id from workflow$suffix.wf_request
				where id=$reqId
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? 0 : $row['doc_id']);
	}
	
	protected function getProcessId($code, $date) {
		$suffix = Yii::app()->params['envSuffix'];
		$d = General::toMyDate($date);
		$sql = "select b.id from workflow$suffix.wf_process a, workflow$suffix.wf_process_version b 
				where a.code='$code' and a.id = b.process_id
				and b.start_dt <= '$d' and b.end_dt >= '$d' 
				order by b.id desc limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? 0 : $row['id']);
	}
	
	protected function getActionId($procId, $code) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select id from workflow$suffix.wf_action
				where proc_ver_id=$procId and code='$code' 
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? 0 : $row['id']);
	}
	
	protected function getStateId($procId, $code) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select id from workflow$suffix.wf_state
				where proc_ver_id=$procId and code='$code' 
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? 0 : $row['id']);
	}
	
	protected function getTransitLogId($reqId, $currentState) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select id from workflow$suffix.wf_request_transit_log
				where new_state=$currentState and request_id=$reqId
				order by id desc limit 1
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return (($row===false) ? 0 : $row['id']);
	}

	protected function isValidMove($procId, $fromState, $toState) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select id from workflow$suffix.wf_transition
				where proc_ver_id=$procId and current_state=$fromState and next_state=$toState 
			";
		$row = $this->connection->createCommand($sql)->queryRow();
		return ($row!==false);
	}
}
?>