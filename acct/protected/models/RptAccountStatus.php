<?php
class RptAccountStatus extends CReport {
	protected $result1;

	protected $result1_1;
	
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
		$month_start_dt = date("Y", strtotime($start_dt)).'/'.date("m", strtotime($start_dt)).'/01 00:00:00';
		$year_start_dt = date("Y", strtotime($start_dt)).'/01/01 00:00:00';
		$wk = date("Y", strtotime($start_dt))."W".date("W", strtotime($start_dt));
		$week_start_dt = date("Y-m-d",strtotime($wk)).' 00:00:00';

		$city = $this->criteria['CITY'];
		
		$suffix = Yii::app()->params['envSuffix'];
		
		$sql = "select a.id, c.acct_type_desc, a.acct_no, a.acct_name, a.bank_name, a.city,  
					AccountBalanceByLCD(a.id,'$city','2010-01-01 00:00:00',('$start_dt' - interval 1 Minute)) as balance_last,
					TransAmountByLCDWoIntTrf('IN',a.id,'$city','$start_dt','$end_dt') as balance_in,
					TransAmountByLCDWoIntTrf('IN',a.id,'$city','$week_start_dt','$end_dt') as balance_wtd_in,
					TransAmountByLCDWoIntTrf('IN',a.id,'$city','$month_start_dt','$end_dt') as balance_mtd_in,
					TransAmountByLCDWoIntTrf('IN',a.id,'$city','$year_start_dt','$end_dt') as balance_ytd_in,
					TransAmountByLCDWoIntTrf('OUT',a.id,'$city','$start_dt','$end_dt') as balance_out,
					TransAmountByLCDWoIntTrf('OUT',a.id,'$city','$week_start_dt','$end_dt') as balance_wtd_out,
					TransAmountByLCDWoIntTrf('OUT',a.id,'$city','$month_start_dt','$end_dt') as balance_mtd_out,
					TransAmountByLCDWoIntTrf('OUT',a.id,'$city','$year_start_dt','$end_dt') as balance_ytd_out,
					AccountBalanceByLCD(a.id,'$city','2010-01-01 00:00:00','$end_dt') as balance_today
				from acc_account a, acc_account_type c 
				where (a.city='$city' or a.city='99999') and a.acct_type_id=c.id
				order by a.acct_type_id, a.acct_name
			";
		$this->result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select swoper$suffix.IncomeYTD('00002','$city','$start_dt') as income_ytd,
					swoper$suffix.IncomeMTD('00002','$city',('$month_start_dt' - interval 1 Minute)) as income_mtd
			";
		$this->result1_1 = Yii::app()->db->createCommand($sql)->queryRow();
		
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=b.city ' : '');
		$sql = "select a.*, k.acct_no, k.acct_name, k.bank_name, 
					b.trans_type_desc, 
					c.field_value as payer_type,  
					d.field_value as payer_name,
					e.field_value as year_no,
					f.field_value as month_no,
					g.field_value as united_inv_no,
					h.field_value as handle_staff_name,
					i.field_value as item_code,
					j.field_value as int_fee
				from acc_trans a inner join acc_trans_type b on a.trans_type_code=b.trans_type_code $citystr 
					left outer join acc_account k on a.acct_id=k.id 
					left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='payer_type'
					left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='payer_name'
					left outer join acc_trans_info e on a.id=e.trans_id and e.field_id='year_no'
					left outer join acc_trans_info f on a.id=f.trans_id and f.field_id='month_no'
					left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='united_inv_no'
					left outer join acc_trans_info h on a.id=h.trans_id and h.field_id='handle_staff_name'
					left outer join acc_trans_info i on a.id=i.trans_id and i.field_id='item_code'
					left outer join acc_trans_info j on a.id=j.trans_id and j.field_id='int_fee'
				where a.city='$city' and a.status <> 'V'
					and a.lcd >= '$start_dt' and a.lcd <= '$end_dt'
					and b.trans_cat = 'IN' 
					and (i.field_value not in ('BI0016','BI0002') or i.field_value is null)
				order by a.trans_dt desc, a.id desc
			";
		$this->result2 = Yii::app()->db->createCommand($sql)->queryAll();
		
		return true;
	}

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
		
		$staff = $this->getUserWithRights($city, 'XB04');
		if (!empty($staff)) {
			$mgr = array_merge($mgr, $staff);
		}

		$to = General::getEmailByUserIdArray($mgr);
		$to = General::dedupToEmailList($to);
// Remove Joe Yiu from to address
		$tmp = $to;
		$to = array();
		foreach($tmp as $itm) {
			if ($itm != 'joeyiu@lbsgroup.com.cn') $to[] = $itm;
		}
//
		$cc = array();
		
		$subject = Yii::t('report','Payment Receive Daily Report').' ('.General::getCityName($city).') - '.General::toDate($date);
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
	
	protected function getUserWithRights($city, $right) {
		$rtn = array();
		
		$citylist = City::model()->getAncestorList($city);
		$citylist = ($citylist=='' ? $citylist : $citylist.',')."'$city'";
		
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where a.a_read_only like '%$right%' or a.a_read_write like '%$right%'
				and a.username=b.username and (FIND_IN_SET('{$city}',b.look_city) or b.city in ($citylist)) and b.status='A'
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
		$output1 = $this->printSection1();
		$output2 = $this->printSection2();
		return $output1.'<br><br>'.$output2;
	}
	
	protected function printSection1() {
		$output = "<table border=1>";
		$output .= "<tr><th colspan=2>".Yii::t('report','Item')
				."</th><th>".Yii::t('report','Last Balance')
				."</th><th>".Yii::t('report','YTD Paid')
				."</th><th>".Yii::t('report','YTD Received')
				."</th><th>".Yii::t('report','MTD Paid')
				."</th><th>".Yii::t('report','MTD Received')
				."</th><th>".Yii::t('report','WTD Paid')
				."</th><th>".Yii::t('report','WTD Received')
				."</th><th>".Yii::t('report','Curr. Paid')
				."</th><th>".Yii::t('report','Curr. Received')
				."</th><th>".Yii::t('report','Curr. Balance')
				."</th></tr>\n";
		$type = '';
		$section = '';
		$cnt = 0;
		$total = array(
					'last'=>0,
					'in'=>0,
					'out'=>0,
					'wtd_in'=>0,
					'wtd_out'=>0,
					'mtd_in'=>0,
					'mtd_out'=>0,
					'ytd_in'=>0,
					'ytd_out'=>0,
					'today'=>0,
				);
		$gtotal = array(
					'last'=>0,
					'in'=>0,
					'out'=>0,
					'wtd_in'=>0,
					'wtd_out'=>0,
					'mtd_in'=>0,
					'mtd_out'=>0,
					'ytd_in'=>0,
					'ytd_out'=>0,
					'today'=>0,
				);
		foreach ($this->result1 as $record) {
			if (empty($type)) $type = $record['acct_type_desc'];
			if ($type != $record['acct_type_desc']) {
				$output .= "<tr><td rowspan=$cnt>$type</td>";
				$output .= $section;
				$output .= "<tr><td colspan=2>".Yii::t('report','Total')
					."</td><td align='right'>".number_format($total['last'],2)
					."</td><td align='right'>".number_format($total['ytd_out'],2)
					."</td><td align='right'>".number_format($total['ytd_in'],2)
					."</td><td align='right'>".number_format($total['mtd_out'],2)
					."</td><td align='right'>".number_format($total['mtd_in'],2)
					."</td><td align='right'>".number_format($total['wtd_out'],2)
					."</td><td align='right'>".number_format($total['wtd_in'],2)
					."</td><td align='right'>".number_format($total['out'],2)
					."</td><td align='right'>".number_format($total['in'],2)
					."</td><td align='right'>".number_format($total['today'],2)
					."</td></tr>\n";
				
				$section = '';
				$cnt = 0;
				$total['last'] = 0;
				$total['in'] = 0;
				$total['out'] = 0;
				$total['wtd_in'] = 0;
				$total['wtd_out'] = 0;
				$total['mtd_in'] = 0;
				$total['mtd_out'] = 0;
				$total['ytd_in'] = 0;
				$total['ytd_out'] = 0;
				$total['today'] = 0;
				$type = $record['acct_type_desc'];
			}
			
			$total['last'] += $record['balance_last'];
			$total['in'] += $record['balance_in'];
			$total['out'] += $record['balance_out'];
			$total['wtd_in'] += $record['balance_wtd_in'];
			$total['wtd_out'] += $record['balance_wtd_out'];
			$total['mtd_in'] += $record['balance_mtd_in'];
			$total['mtd_out'] += $record['balance_mtd_out'];
			$total['ytd_in'] += $record['balance_ytd_in'];
			$total['ytd_out'] += $record['balance_ytd_out'];
			$total['today'] += $record['balance_today'];
			
			$gtotal['last'] += $record['balance_last'];
			$gtotal['in'] += $record['balance_in'];
			$gtotal['out'] += $record['balance_out'];
			$gtotal['wtd_in'] += $record['balance_wtd_in'];
			$gtotal['wtd_out'] += $record['balance_wtd_out'];
			$gtotal['mtd_in'] += $record['balance_mtd_in'];
			$gtotal['mtd_out'] += $record['balance_mtd_out'];
			$gtotal['ytd_in'] += $record['balance_ytd_in'];
			$gtotal['ytd_out'] += $record['balance_ytd_out'];
			$gtotal['today'] += $record['balance_today'];
			
			$cnt++;
			
			if (!empty($section)) $section .= "<tr>";
			$section .= "<td>".$record['acct_name']
					."</td><td align='right'>".number_format($record['balance_last'],2)
					."</td><td align='right'>".number_format($record['balance_ytd_out'],2)
					."</td><td align='right'>".number_format($record['balance_ytd_in'],2)
					."</td><td align='right'>".number_format($record['balance_mtd_out'],2)
					."</td><td align='right'>".number_format($record['balance_mtd_in'],2)
					."</td><td align='right'>".number_format($record['balance_wtd_out'],2)
					."</td><td align='right'>".number_format($record['balance_wtd_in'],2)
					."</td><td align='right'>".number_format($record['balance_out'],2)
					."</td><td align='right'>".number_format($record['balance_in'],2)
					."</td><td align='right'>".number_format($record['balance_today'],2)
					."</td></tr>";
		}
		if (empty($this->result1)) {
			$output .= "<tr><td colspan=12 align='center'>".Yii::t('report','No Record')."</td></tr>\n";
		} else {
			$output .= "<tr><td rowspan=$cnt>$type</td>";
			$output .= $section;
			$output .= "<tr><td colspan=2>".Yii::t('report','Total')
				."</td><td align='right'>".number_format($total['last'],2)
				."</td><td align='right'>".number_format($total['ytd_out'],2)
				."</td><td align='right'>".number_format($total['ytd_in'],2)
				."</td><td align='right'>".number_format($total['mtd_out'],2)
				."</td><td align='right'>".number_format($total['mtd_in'],2)
				."</td><td align='right'>".number_format($total['wtd_out'],2)
				."</td><td align='right'>".number_format($total['wtd_in'],2)
				."</td><td align='right'>".number_format($total['out'],2)
				."</td><td align='right'>".number_format($total['in'],2)
				."</td><td align='right'>".number_format($total['today'],2)
				."</td></tr>\n";

			$ytd_in_pct = $this->result1_1['income_ytd']==0 ? 0 : round($gtotal['ytd_in']/$this->result1_1['income_ytd']*100,2);
			$mtd_in_pct = $this->result1_1['income_mtd']==0 ? 0 : round($gtotal['mtd_in']/$this->result1_1['income_mtd']*100,2);
			
			$output .= "<tr><td colspan=12 align='center'>&nbsp;</td></tr>";
			$output .= "<tr><td colspan=2>".Yii::t('report','Grand Total')
				."</td><td align='right'>".number_format($gtotal['last'],2)
				."</td><td align='right'>".number_format($gtotal['ytd_out'],2)
				."</td><td align='right'>".number_format($gtotal['ytd_in'],2)." (".$ytd_in_pct."%)"
				."</td><td align='right'>".number_format($gtotal['mtd_out'],2)
				."</td><td align='right'>".number_format($gtotal['mtd_in'],2)." (".$mtd_in_pct."%)"
				."</td><td align='right'>".number_format($gtotal['wtd_out'],2)
				."</td><td align='right'>".number_format($gtotal['wtd_in'],2)
				."</td><td align='right'>".number_format($gtotal['out'],2)
				."</td><td align='right'>".number_format($gtotal['in'],2)
				."</td><td align='right'>".number_format($gtotal['today'],2)
				."</td></tr>\n";
		}
		$output .= "</table>";
		
		return $output;
	}

	protected function printSection2() {
		$acctcode = General::getAcctItemList();
		
		$output = "<table border=1>";
		$output .= "<tr><th>".Yii::t('report','Date')
				."</th><th>".Yii::t('report','Account Name')
				."</th><th>".Yii::t('report','Customer Name')
				."</th><th>".Yii::t('report','Charge Item')
				."</th><th>".Yii::t('report','Service Month')
				."</th><th>".Yii::t('report','United Invoice No.')
				."</th><th>".Yii::t('report','Amount')
				."</th><th>".Yii::t('report','Paid Method')
				."</th><th>".Yii::t('report','Payee')
				."</th><th>".Yii::t('trans','Integrated Fee')
				."</th></tr>\n";
		foreach ($this->result2 as $record) {
			$tdate = General::toDate($record['trans_dt']);
			$account = $record['acct_name'].'('.$record['acct_no'].')';
			$custname = $record['payer_name'];
			$chgitem = empty($record['item_code']) ? '' : $acctcode[$record['item_code']];
			$servicedt = $record['year_no'].'/'.$record['month_no'];
			$uinvno = $record['united_inv_no'];
			$amount = number_format($record['amount'],2);
			$method = $record['trans_type_desc'];
			$payee = $record['handle_staff_name'];
			$intfee = ($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No'));
			
			$output .= "<tr><td>".$tdate
					."</td><td>".$account
					."</td><td>".$custname
					."</td><td>".$chgitem
					."</td><td>".$servicedt
					."</td><td>".$uinvno
					."</td><td align='right'>".$amount
					."</td><td>".$method
					."</td><td>".$payee
					."</td><td>".$intfee
					."</td></tr>\n";
		}
		if (empty($this->result2)) 
			$output .= "<tr><td colspan=10 align='center'>".Yii::t('report','No Record')."</td></tr>\n";
		$output .= "</table>";
		
		return $output;
	}
}
?>
