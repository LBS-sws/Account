<?php
class RptReimbReminder extends CReport {
	protected $result;

	public function genReport() {
		if ($this->retrieveData()) {
			$output = $this->printReport();
			$this->submitEmail($output);
		} else {
			$output = '';
		}
		return $output;
	}
		public function retrieveData() {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		$month_start_dt = date("Y", strtotime($start_dt)).'/'.date("m", strtotime($start_dt)).'/01 00:00:00';
		$year_start_dt = date("Y", strtotime($start_dt)).'/01/01 00:00:00';
		$wk = date("Y", strtotime($start_dt))."W".date("W", strtotime($start_dt));
		$week_start_dt = date("Y-m-d",strtotime($wk)).' 00:00:00';

		$city = $this->criteria['CITY'];
		
		$suffix = Yii::app()->params['envSuffix'];
		
		$sql = "SELECT a.*,f.field_value,
				workflow$suffix.RequestStatusDesc('PAYMENT',a.id,a.req_dt) as wfstatusdesc
				FROM acc_request a 
				left outer join acc_request_info f on f.req_id = a.id and f.field_id='ref_no'
				WHERE workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>'ED' 
				and workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>''
				and datediff('$start_dt', a.req_dt) > 59
				and a.city='$city' and a.status='Y'
				order by a.id
			";
		$this->result = Yii::app()->db->createCommand($sql)->queryAll();

		return !empty($this->result);	}

	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
	
	public function submitEmail($msg) {
		$city = $this->criteria['CITY'];
		$date = $this->criteria['TARGET_DT'];
		
		$mgr = City::model()->getAncestorInChargeList($city);
		$usr = City::model()->findByPk($city)->incharge;
		if (!empty($usr)) $mgr[] = $usr;
		
		$staff = $this->getUserWithRights($city, 'XA06', true);
		if (!empty($staff)) {
			$mgr = array_merge($mgr, $staff);
		}

		$acct = $this->getUserWithRights($city, 'CN01');
		if (!empty($acct)) {
			$mgr = array_merge($mgr, $acct);
		}
		
		$to = General::getEmailByUserIdArray($mgr);
		$to = General::dedupToEmailList($to);
// Remove Joe Yiu from to address
//		$tmp = $to;
//		$to = array();
//		foreach($tmp as $itm) {
//			if ($itm != 'joeyiu@lbsgroup.com.cn') $to[] = $itm;
//		}
//
		$cc = array();
		
		$subject = Yii::t('report','Summary Report - Reimbursement Not Completed Over 2 Months').' ('.General::getCityName($city).') - '.General::toDate($date);
		$desc = Yii::t('report','Summary Report - Reimbursement Not Completed Over 2 Months').' ('.General::getCityName($city).') - '.General::toDate($date);
		
		$param = array(
				'from_addr'=>Yii::app()->params['systemEmail'],
				'to_addr'=>json_encode($to),
				'cc_addr'=>json_encode($cc),
				'subject'=>$subject,
				'description'=>$desc,
				'message'=>$msg,
//				'test'=>true,
			);
		$connection = Yii::app()->db;
		$this->sendEmail($connection, $param);
	}
	
	protected function getUserWithRights($city, $right, $rw=false) {
		$rtn = array();
		
		$citylist = City::model()->getAncestorList($city);
		$citylist = ($citylist=='' ? $citylist : $citylist.',')."'$city'";
		
		$suffix = Yii::app()->params['envSuffix'];
		$sql = $rw ?
			"select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where a.a_read_write like '%$right%'
				and a.username=b.username and b.city in ($citylist) and b.status='A'
				and a.system_id='acct'
			"
			:
			"select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where (a.a_read_only like '%$right%' or a.a_read_write like '%$right%'
				or a.a_control like '%$right%')
				and a.username=b.username and b.city in ($citylist) and b.status='A'
				and a.system_id='acct'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn[] = $row['username'];
			}
		}
		return $rtn;
	}
	
	public function printReport() {
		$output = $this->printSection();
		return $output;
	}
	
	protected function printSection() {
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('report','Ref. No.')
				."</th><th>".Yii::t('report','Request Date')
				."</th><th>".Yii::t('report','Amount')
				."</th><th>".Yii::t('report','Status')
				."</th></tr>\n";
		foreach ($this->result as $record) {
			$rdate = General::toDate($record['req_dt']);
			$status = $record['wfstatusdesc'];
			$refno = $record['field_value'];
			$amount = number_format($record['amount'],2);
			
			$output .= "<tr><td>".$refno
					."</td><td>".$rdate
					."</td><td align='right'>".$amount
					."</td><td>".$status
					."</td></tr>\n";
		}
		$output .= "</table>";
		
		return $output;
	}
}
?>
