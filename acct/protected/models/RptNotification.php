<?php
class RptNotification extends CReport {
	protected $result;
	
	public function genReport() {
		$this->retrieveData();
		$output = '';
		$lu = '';
		foreach ($this->result as $record) {
			if ($lu=='') {
				$lu = $record['username'];
				$output = $this->printHeader();
			}
			if ($lu!=$record['username']) {
				$output .= $this->printFooter();
				$this->submitEmail($lu, $output);
				$lu = $record['username'];
				$output = $this->printHeader();
			}
			$output .= $this->printDetail($record);
		}
		if ($lu!='') {
			$output .= $this->printFooter();
			$this->submitEmail($lu, $output);
		}
		return $output;
	}
		public function retrieveData() {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		
		$sql = "select a.username, b.*
				from swoper$suffix.swo_notification_user a, swoper$suffix.swo_notification b 
				where a.note_id=b.id and b.system_id='acct' and a.status='N' 
				and a.lcd >= '$start_dt' and a.lcd <= '$end_dt'
				order by a.username, a.note_id
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
		
		$subject = Yii::t('report','Consolidated Notification Report').' - '.General::toDate($date);
		$desc = Yii::t('report','Consolidated Notification Report').' ('.Yii::t('report','User').': '.$user.') - '.General::toDate($date);
		
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
		$output .= "<tr><th>".Yii::t('report','Date')
				."</th><th>".Yii::t('report','Subject')
				."</th><th>".Yii::t('report','Title')
				."</th><th>".Yii::t('report','Content')
				."</th></tr>";
		return $output;
	}

	public function printDetail($record) {
		$output = "<tr><td valign='top'>".$record['lcd']
				."</td><td valign='top'>".$record['subject']
				."</td><td valign='top'>".$record['description']
				."</td><td valign='top'>".$record['message']
				."</td></tr>";
		return $output;
	}
	
	public function PrintFooter() {
		$output = "</table>";
		return $output;
	}
}
?>