<?php
class RptNotificationSp extends CReport {
	protected $result;
	
	protected $special = array(
							'joeyiu@lbsgroup.com.cn'=>array('审核人','Kitty'),
						);
	
	public function genReport() {
		$output = '';
		foreach ($this->special as $email=>$items) {
			$this->retrieveData($email);

			$content = '';
			foreach ($this->result as $record) {
				$flag = true;
				foreach ($items as $word) {
					if (strpos($record['message'], $word)===false) {
						$flag = false;
						break;
					}
				}
				if ($flag) $content .= $this->printDetail($record);
			}

			if (!empty($content)) {
				$output = $this->printHeader();
				$output .= $content;
				$output .= $this->printFooter();
				$this->submitEmail($email, $output);
			}
		}
		return 'Done';
	}
		public function retrieveData($email) {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		
		$sql = "select a.username, c.email, b.*
				from swoper$suffix.swo_notification_user a, swoper$suffix.swo_notification b,
				security$suffix.sec_user c
				where a.note_id=b.id and b.system_id='acct' and a.status='N' 
				and a.lcd >= '$start_dt' and a.lcd <= '$end_dt'
				and a.username=c.username and c.email='$email'
				order by a.note_id
			";
		$this->result = Yii::app()->db->createCommand($sql)->queryAll();

		return true;	}

//	public function getReportName() {
//		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
//		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
//	}
	
	public function submitEmail($email, $msg) {
		$date = $this->criteria['TARGET_DT'];
		
		$to = array($email);
		$cc = array();
		
		$subject = Yii::t('report','Consolidated Notification Report (Special)').' - '.General::toDate($date);
		$desc = Yii::t('report','Consolidated Notification Report (Special)').' - '.General::toDate($date);
		
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