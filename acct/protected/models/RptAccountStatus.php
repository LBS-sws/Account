<?php
class RptAccountStatus extends CReport {
	protected $result1;
	
	protected $result2; 

	public function genReport() {
		$this->retrieveData();
		$output = $this->printReport();
		$this->submitEmail($output);
		return $output;
	}
		public function retrieveData() {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		$city = $this->criteria['CITY'];
		
		$suffix = Yii::app()->params['envSuffix'];
		
		$sql = "select a.id, c.acct_type_desc, a.acct_no, a.acct_name, a.bank_name, a.city,  
					AccountBalance(a.id,'$city','2010-01-01 00:00:00',('$start_dt' - interval 1 Minute)) as balance_last,
					AccountTransAmount('IN',a.id,'$city','$start_dt','$end_dt') as balance_in,
					AccountTransAmount('OUT',a.id,'$city','$start_dt','$end_dt') as balance_out
				from acc_account a, acc_account_type c 
				where (a.city='$city' or a.city='99999') and a.acct_type_id=c.id
				order by a.acct_type_id, a.acct_name
			";
		$this->result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select a.*, 
					b.trans_type_desc, 
					c.field_value as payer_type,  
					d.field_value as payer_name,
					e.field_value as year_no,
					f.field_value as month_no,
					g.field_value as united_inv_no,
					h.field_value as handle_staff_name,
					i.field_value as acct_code
				from acc_trans a inner join acc_trans_type b on a.trans_type_code=b.trans_type_code 
					left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='payer_type'
					left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='payer_name'
					left outer join acc_trans_info e on a.id=e.trans_id and e.field_id='year_no'
					left outer join acc_trans_info f on a.id=f.trans_id and f.field_id='month_no'
					left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='united_inv_no'
					left outer join acc_trans_info h on a.id=h.trans_id and h.field_id='handle_staff_name'
					left outer join acc_trans_info i on a.id=i.trans_id and i.field_id='acct_code'
				where a.city='$city' and a.status <> 'V'
					and a.trans_dt >= '$start_dt' and a.trans_dt <= '$end_dt'
					and a.acct_id = 2 
				order by a.trans_dt desc, a.id desc
			";
		$this->result2 = Yii::app()->db->createCommand($sql)->queryAll();
		
		return true;	}

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
		$to = General::getEmailByUserIdArray($mgr);
		$to = General::dedupToEmailList($to);
		$cc = array();
		
		$subject = Yii::t('report','Customer Cash In Daily Report').' ('.General::getCityName($city).') - '.General::toDate($date);
		$desc = Yii::t('report','Customer Cash In Daily Report').' ('.General::getCityName($city).') - '.General::toDate($date);
		
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
	
	public function printReport() {
		$output1 = $this->printSection1();
		$output2 = $this->printSection2();
		return $output1.'<br><br>'.$output2;
	}
	
	protected function printSection1() {
		$output = "<table border=1>";
		$output .= "<tr><th colspan=2>".Yii::t('report','Item')
				."</th><th>".Yii::t('report','Last Balance')
				."</th><th>".Yii::t('report','Curr. Paid')
				."</th><th>".Yii::t('report','Curr. Received')
				."</th><th>".Yii::t('report','Curr. Balance')
				."</th></tr>";
		$type = '';
		$section = '';
		$cnt = 0;
		$total = array(
					'last'=>0,
					'in'=>0,
					'out'=>0,
				);
		$gtotal = array(
					'last'=>0,
					'in'=>0,
					'out'=>0,
				);
		foreach ($this->result1 as $record) {
			if (empty($type)) $type = $record['acct_type_desc'];
			if ($type != $record['acct_type_desc']) {
				$output .= "<tr><td rowspan=$cnt>$type</td>";
				$output .= $section;
				$output .= "<tr><td colspan=2>".Yii::t('report','Total')
					."</td><td align='right'>".number_format($total['last'],2)
					."</td><td align='right'>".number_format($total['out'],2)
					."</td><td align='right'>".number_format($total['in'],2)
					."</td><td align='right'>".number_format($total['last']+$total['in']-$total['out'],2)
					."</td></tr>";
				
				$section = '';
				$cnt = 0;
				$total['last'] = 0;
				$total['in'] = 0;
				$total['out'] = 0;
				$type = $record['acct_type_desc'];
			}
			
			$total['last'] += $record['balance_last'];
			$total['in'] += $record['balance_in'];
			$total['out'] += $record['balance_out'];
			
			$gtotal['last'] += $record['balance_last'];
			$gtotal['in'] += $record['balance_in'];
			$gtotal['out'] += $record['balance_out'];
			
			$cnt++;
			
			if (!empty($section)) $section .= "<tr>";
			$section .= "<td>".$record['acct_name']
					."</td><td align='right'>".number_format($record['balance_last'],2)
					."</td><td align='right'>".number_format($record['balance_out'],2)
					."</td><td align='right'>".number_format($record['balance_in'],2)
					."</td><td align='right'>".number_format($record['balance_last']+$record['balance_in']-$record['balance_out'],2)
					."</td></tr>";
		}
		if (empty($this->result1)) {
			$output .= "<tr><td colspan=6 align='center'>".Yii::t('report','No Record')."</td></tr>";
		} else {
			$output .= "<tr><td rowspan=$cnt>$type</td>";
			$output .= $section;
			$output .= "<tr><td colspan=2>".Yii::t('report','Total')
				."</td><td align='right'>".number_format($total['last'],2)
				."</td><td align='right'>".number_format($total['out'],2)
				."</td><td align='right'>".number_format($total['in'],2)
				."</td><td align='right'>".number_format($total['last']+$total['in']-$total['out'],2)
				."</td></tr>";

			$output .= "<tr><td colspan=6 align='center'>&nbsp;</td></tr>";
			$output .= "<tr><td colspan=2>".Yii::t('report','Grand Total')
				."</td><td align='right'>".number_format($gtotal['last'],2)
				."</td><td align='right'>".number_format($gtotal['out'],2)
				."</td><td align='right'>".number_format($gtotal['in'],2)
				."</td><td align='right'>".number_format($gtotal['last']+$gtotal['in']-$gtotal['out'],2)
				."</td></tr>";
		}
		$output .= "</table>";
		
		return $output;
	}

	protected function printSection2() {
		$acctcode = General::getAcctCodeList();
		
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('report','Date')
				."</th><th>".Yii::t('report','Customer Name')
				."</th><th>".Yii::t('report','Charge Item')
				."</th><th>".Yii::t('report','Service Month')
				."</th><th>".Yii::t('report','United Invoice No.')
				."</th><th>".Yii::t('report','Amount')
				."</th><th>".Yii::t('report','Paid Method')
				."</th><th>".Yii::t('report','Payee')
				."</th></tr>";
		foreach ($this->result2 as $record) {
			$tdate = General::toDate($record['trans_dt']);
			$custname = $record['payer_name'];
			$chgitem = empty($record['acct_code']) ? '' : $acctcode[$record['acct_code']];
			$servicedt = $record['year_no'].'/'.$record['month_no'];
			$uinvno = $record['united_inv_no'];
			$amount = number_format($record['amount'],2);
			$method = $record['trans_type_desc'];
			$payee = $record['handle_staff_name'];
			
			$output .= "<tr><td>".$tdate
					."</td><td>".$custname
					."</td><td>".$chgitem
					."</td><td>".$servicedt
					."</td><td>".$uinvno
					."</td><td align='right'>".$amount
					."</td><td>".$method
					."</td><td>".$payee
					."</td></tr>";
		}
		if (empty($this->result2)) 
			$output .= "<tr><td colspan=8 align='center'>".Yii::t('report','No Record')."</td></tr>";
		$output .= "</table>";
		
		return $output;
	}
}
?>