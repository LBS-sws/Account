<?php
class RptApprovalReimb extends CReport {
	protected $users = array();

	protected $result;
	
	public function genReport() {
		$report = ''
		$this->retrieveUsers();
		if (count($this->users) > 0) {
			foreach ($this->users as $user) {
				$this->retrieveData($user);
				$output = '';
				$output = $this->printHeader();
				foreach ($this->result as $record) {
					$output .= $this->printDetail($record);
				}
				$output .= $this->printFooter();
				$this->submitEmail($user, $output);
				$report .= $output
			}
		}	
		return $report;
	}
	
	public function retrieveUsers() {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;

		$sql = "select username from security$suffix where a_read_only like '%XB07%'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$this->users[] = $row['username'];
			}
		}
	}
		public function retrieveData($user) {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		$rows = array($user);
/*		
		$sql = "select delegated from account$suffix.acc_delegation where username='$user'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		$rows[] = $user;
*/
		$userlist = "'".implode("','", $rows)."'";
		
		$sql = "select 
					g.field_value as ref_no, f.name as city_name, h.disp_name as requestor,
					e.payee_name, j.name as item_name, e.amount, l.field_value as int_fee,
					k.disp_name as approver, c.name as action_desc,
					a1.lud, a1.request_id, b.name as state_desc
				from
					workflow$suffix.wf_request_resp_user a 
					inner join workflow.wf_request_resp_user a1 on a.request_id=a1.request_id and a.log_id=a1.log_id 
						and a.current_state=a1.current_state and a1.status='C'
					inner join workflow$suffix.wf_state b on a1.current_state = b.id and b.code='PS'
					inner join workflow$suffix.wf_action c on a1.action_id = c.id 
					inner join workflow$suffix.wf_request d on a1.request_id = d.id 
					inner join account$suffix.acc_request e on d.doc_id = e.id
					inner join security$suffix.sec_city f on e.city = f.code
					left outer join account$suffix.acc_request_info g on e.id = g.req_id and g.field_id='ref_no'
					left outer join security$suffix.sec_user h on e.req_user = h.username
					left outer join account$suffix.acc_request_info i on e.id = i.req_id and i.field_id='item_code'
					left outer join account$suffix.acc_account_item j on i.field_value=j.code
					left outer join security$suffix.sec_user k on a1.username = k.username
					left outer join account$suffix.acc_request_info l on e.id = l.req_id and l.field_id='int_fee'
				where 
					a.username in ($userlist) and a.status in ('C','T') and 
					a.lud >= '$start_dt' and a.lud <= '$end_dt'
			";
		$this->result = Yii::app()->db->createCommand($sql)->queryAll();

		return true;	}

//	public function getReportName() {
//		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
//		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
//	}
	
	public function submitEmail($user, $msg) {
		$date = $this->criteria['TARGET_DT'];
		
		if (!empty($user)) $mgr[] = $user;
		$to = General::getEmailByUserIdArray($mgr);
		$to = General::dedupToEmailList($to);
		$cc = array();
		
		$subject = Yii::t('report','Daily Reimbursement Approval Summary').' - '.General::toDate($date);
		$desc = Yii::t('report','Daily Reimbursement Approval Summary').' ('.Yii::t('report','User').': '.$user.') - '.General::toDate($date);
		
		$param = array(
				'from_addr'=>Yii::app()->params['systemEmail'],
				'to_addr'=>json_encode($to),
				'cc_addr'=>json_encode($cc),
				'subject'=>$subject,
				'description'=>$desc,
				'message'=>$msg,
			);
		$connection = Yii::app()->db;
		$this->sendEmail($connection, $param);
	}
	
	public function printHeader() {
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('trans','Ref. No.')
				."</th><th>".Yii::t('misc','City')
				."</th><th>".Yii::t('trans','Requestor')
				."</th><th>".Yii::t('trans','Payee')
				."</th><th>".Yii::t('trans','Paid Item')
				."</th><th>".Yii::t('trans','Amount')
				."</th><th>".Yii::t('trans','Integrated Fee')
				."</th><th>".Yii::t('trans','Approver')
				."</th><th>".Yii::t('trans','Action')
				."</th></tr>";
		return $output;
	}

	public function printDetail($record) {
		$output = "<tr><td valign='top'>".$record['ref_no']
				."</td><td valign='top'>".$record['city_name']
				."</td><td valign='top'>".$record['requestor']
				."</td><td valign='top'>".$record['payee_name']
				."</td><td valign='top'>".$record['item_name']
				."</td><td valign='top'>".$record['amount']
				."</td><td valign='top'>".$record['int_fee']
				."</td><td valign='top'>".$record['approver']
				."</td><td valign='top'>".$record['action_desc']
				."</td></tr>";
		return $output;
	}
	
	public function PrintFooter() {
		$output = "</table>";
		return $output;
	}
}
?>