<?php
class RptReceiptOverview extends CReport {
	protected $result;
	
	protected $day_no;
	
	protected $cities;

	public function genReport() {
		$this->getTargetCities();
		$this->retrieveData();
		$users = $this->getTargetUsers();
		foreach ($users as $user) {
			$output = $this->printReport($user);
			if (!empty($output)) $this->submitEmail($user, $output);
		}
		return 'Done';
	}
	
	protected function getTargetCities() {
		$whitelist = $this->criteria['WHITELIST'];
		$blacklist = $this->criteria['BLACKLIST'];
		
		$this->cities = array();
		$cities = General::getCityListWithNoDescendant();
		foreach ($cities as $city=>$name) {
			if (!empty($whitelist)) {
				$flag = (strpos($whitelist,$city)!==false);
			} else {
				$flag = (empty($blacklist) || (strpos($blacklist,$city)===false));
			}
			if ($flag) $this->cities[$city] = $name;
		}
	}
	
	protected function getTargetUsers() {
		$users = array();

/*		
		foreach ($this->cities as $city=>$name) {
			foreach (City::model()->getAncestorInChargeList($city) as $mgr) {
				if (!in_array($mgr, $users)) $users[] = $mgr;
			}
			
			$usr = City::model()->findByPk($city)->incharge;
			if (!empty($usr) && !in_array($mgr, $users)) $users[] = $usr;
			
		}
*/
		$suffix = Yii::app()->params['envSuffix'];
		$right = 'XB05';
		$sql = "select a.username from security$suffix.sec_user_access a, security$suffix.sec_user b
				where a.a_read_only like '%$right%' or a.a_read_write like '%$right%'
				and a.username=b.username
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			if (!in_array($row['username'],$users)) $users[] = $row['username'];
		}
		
		return $users;
	}
	
	public function retrieveData() {
		$start_dt = $this->criteria['TARGET_DT'].' 00:00:00';
		$end_dt = $this->criteria['TARGET_DT'].' 23:59:59';
		$month_start_dt = date("Y", strtotime($start_dt)).'/'.date("m", strtotime($start_dt)).'/01 00:00:00';
		$year_start_dt = date("Y", strtotime($start_dt)).'/01/01 00:00:00';
        $wkno = date("W", strtotime($start_dt));
        $mthno = date("n", strtotime($start_dt));
        $wk = (($wkno=='01' && $mthno==12) ? date("Y", strtotime($start_dt.' +1 years')) : date("Y", strtotime($start_dt)))."W".$wkno;
		$week_start_dt = date("Y-m-d",strtotime($wk)).' 00:00:00';
		$this->day_no = date("d", strtotime($start_dt));

		$suffix = Yii::app()->params['envSuffix'];
		
		$this->result = array();
		foreach ($this->cities as $city=>$name) {
			$this->result[$city] = array(
									'name'=>$name,
									'balance_in'=>0,
									'balance_wtd_in'=>0,
									'balance_mtd_in'=>0,
									'balance_ytd_in'=>0,
									'income_ytd'=>0,
									'income_mtd'=>0,
								);

			$sql = "select a.id, c.acct_type_desc, a.acct_no, a.acct_name, a.bank_name, a.city,  
						TransAmountByLCDWoIntTrf('IN',a.id,'$city','$start_dt','$end_dt') as balance_in,
						TransAmountByLCDWoIntTrf('IN',a.id,'$city','$week_start_dt','$end_dt') as balance_wtd_in,
						TransAmountByLCDWoIntTrf('IN',a.id,'$city','$month_start_dt','$end_dt') as balance_mtd_in,
						TransAmountByLCDWoIntTrf('IN',a.id,'$city','$year_start_dt','$end_dt') as balance_ytd_in
					from acc_account a, acc_account_type c 
					where (a.city='$city' or a.city='99999') and a.acct_type_id=c.id
					order by a.city, a.acct_type_id, a.acct_name
			";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				foreach ($rows as $row) {
					$this->result[$city]['balance_in'] += $row['balance_in'];
					$this->result[$city]['balance_wtd_in'] += $row['balance_wtd_in'];
					$this->result[$city]['balance_mtd_in'] += $row['balance_mtd_in'];
					$this->result[$city]['balance_ytd_in'] += $row['balance_ytd_in'];
				}
			}
			
			$sql = "select operation$suffix.IncomeYTD('10011','$city','$start_dt') as income_ytd,
					operation$suffix.IncomeMTD('10011','$city',('$month_start_dt' - interval 1 Minute)) as income_mtd
				";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row!==false) {
				$this->result[$city]['income_ytd'] = $row['income_ytd'];
				$this->result[$city]['income_mtd'] = $row['income_mtd'];
			}
		}
		
		return true;
	}

	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil'));
	}
	
	public function submitEmail($username, $msg) {
		$date = $this->criteria['TARGET_DT'];
		
		$to = General::getEmailByUserIdArray(array($username));
		$cc = array();
		
		$subject = Yii::t('report','Daily Receipt Overview Report').' - '.General::toDate($date);
		$desc = Yii::t('report','Date').' - '.General::toDate($date);
		
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
	
	protected function printReport($username) {
		$user=User::model()->find('LOWER(username)=?',array($username));
		if ($user===null) return '';

        $cstr = $user->city;
        $city_allow = str_replace(",","','",$user->look_city);//将,号替换成','
        $city_allow = empty($city_allow)? "'$cstr'" : "'{$city_allow}'";
		
		$output = "<table border=0 style='table-layout:fixed;width:350px;'>\n";
		$colcnt = 0;
		$line = '';
		foreach ($this->result as $key=>$value) {
			if ($colcnt==2) {
				$output .= "<tr>$line</tr>\n";
				$line = '';
				$colcnt = 0;
			}				
			if (strpos($city_allow,"'{$key}'")!==false) {
				$ytd_in_pct = $value['income_ytd']==0 ? 0 : round($value['balance_ytd_in']/$value['income_ytd']*100,2);
				$mtd_in_pct = $value['income_mtd']==0 ? 0 : round($value['balance_mtd_in']/$value['income_mtd']*100,2);
				
				$warning = ($this->day_no>=5 && $this->day_no<=14 && $mtd_in_pct < 10) ||
							($this->day_no>=15 && $this->day_no<=24 && $mtd_in_pct < 40) ||
							($this->day_no>=25 && $mtd_in_pct < 80)
							? " style='color:#FF0000;'"
							: "";
				
				$line .= "<td><table border=0 width='100%'>";
				$line .= "<tr><td colspan=2  bgcolor='#CCCCCC'>".$value['name']."</td></tr>";
				$line .= "<tr><td>".Yii::t('report','YTD Received')."</td><td align='right'>".$value['balance_ytd_in']."<br>(".$ytd_in_pct."%)"."</td></tr>";
				$line .= "<tr><td>".Yii::t('report','MTD Received')."</td><td align='right'".$warning.">".$value['balance_mtd_in']."<br>(".$mtd_in_pct."%)"."</td></tr>";
				$line .= "<tr><td>".Yii::t('report','WTD Received')."</td><td align='right'>".$value['balance_wtd_in']."</td></tr>";
				$line .= "<tr><td>".Yii::t('report','Curr. Received')."</td><td align='right'>".$value['balance_in']."</td></tr>";
				$line .= "</table></td>";
				$colcnt++;
			}
		}
		if ($line!='') $output .= "<tr>$line</tr>\n";
		$output .= "</table>\n";
		
		return $output;
	}
}
?>