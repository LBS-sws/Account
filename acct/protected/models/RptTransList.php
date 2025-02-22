<?php
class RptTransList extends CReport {	protected function fields() {		return array(			'trans_dt'=>array('label'=>Yii::t('trans','Trans. Date'),'width'=>22,'align'=>'C'),
			'trans_cat'=>array('label'=>Yii::t('trans','Type'),'width'=>15,'align'=>'C'),
			'trans_type_desc'=>array('label'=>Yii::t('trans','Trans. Type'),'width'=>30,'align'=>'L'),
			'account_name'=>array('label'=>Yii::t('trans','Account'),'width'=>30,'align'=>'L'),
			'payer_type'=>array('label'=>Yii::t('trans','Payer').'/'.Yii::t('trans','Payee').' '.Yii::t('trans','Type'),'width'=>20,'align'=>'C'),
			'payer_name'=>array('label'=>Yii::t('trans','Payer').'/'.Yii::t('trans','Payee').' '.Yii::t('code','Name'),'width'=>30,'align'=>'L'),
			'cheque_no'=>array('label'=>Yii::t('trans','Cheque No.'),'width'=>20,'align'=>'L'),
			'invoice_no'=>array('label'=>Yii::t('trans','China Invoice No.'),'width'=>30,'align'=>'L'),
			'handle_staff_name'=>array('label'=>Yii::t('trans','Handling Staff'),'width'=>30,'align'=>'L'),
			'item_code_desc'=>array('label'=>Yii::t('trans','Paid Item'),'width'=>30,'align'=>'L'),
			'acct_code_desc'=>array('label'=>Yii::t('trans','Account Code'),'width'=>30,'align'=>'L'),
			'service_dt'=>array('label'=>Yii::t('trans','Service Fee Date'),'width'=>22,'align'=>'C'),
			'united_inv_no'=>array('label'=>Yii::t('trans','United Invoice No.'),'width'=>30,'align'=>'L'),
			'approve_dt'=>array('label'=>Yii::t('trans','Approval Date'),'width'=>30,'align'=>'C'),
			'req_ref_no'=>array('label'=>Yii::t('trans','Ref. No.'),'width'=>30,'align'=>'L'),
			'int_fee'=>array('label'=>Yii::t('trans','Integrated Fee'),'width'=>20,'align'=>'C'),
			'amount'=>array('label'=>Yii::t('trans','Amount'),'width'=>20,'align'=>'R'),			'detail'=>array('label'=>Yii::t('trans','Details'),'width'=>40,'align'=>'L'),
			'item_desc'=>array('label'=>Yii::t('trans','Remarks 1'),'width'=>40,'align'=>'L'),			'remarks'=>array('label'=>Yii::t('trans','Remarks 2'),'width'=>40,'align'=>'L'),
			'city_name'=>array('label'=>Yii::t('misc','City'),'width'=>15,'align'=>'C'),
		);	}	
	public function genReport() {
		$this->retrieveData();
		$this->title = $this->getReportName();
		$this->subtitle = Yii::t('report','Date').':'.$this->criteria['START_DT'].' - '.$this->criteria['END_DT'].' / '
			.Yii::t('code','Type').':'.$this->getTypeDesc($this->criteria['TRANS_CAT']).' / '
			.Yii::t('trans','Account').':'.$this->getAccountName($this->criteria['ACCT_ID'])
			;
		return $this->exportExcel();
	}

	protected function getTypeDesc($value) {
		$desc = array('ALL'=>Yii::t('report','-- All --'), 'IN'=>Yii::t('code','In'),'OUT'=>Yii::t('code','Out'));
		return isset($desc[$value]) ? $desc[$value] : '';
	}
	
	protected function getAccountName($value) {
		$city = $this->criteria['CITY'];
		$list0 = array(0=>Yii::t('report','-- All --'));
		$citylist = City::model()->getDescendantList($city);
		$citylist .= empty($citylist) ? "'".$city."'" : ",'".$city."'";
		$list1 = General::getAccountList($citylist);
		$list = $list0 + $list1;
		return isset($list[$value]) ? $list[$value] : '';
	}
	
	public function retrieveData() {
		$start_dt = $this->criteria['START_DT'];
		$end_dt = $this->criteria['END_DT'];
		$city = $this->criteria['CITY'];
		$trans_cat = $this->criteria['TRANS_CAT'];
		$account = $this->criteria['ACCT_ID'];
		$citylist = City::model()->getDescendantList($city);
		$citylist .= empty($citylist) ? "'".$city."'" : ",'".$city."'";
		$suffix = Yii::app()->params['envSuffix'];
		
		$condition = ($trans_cat=='ALL' ? "" : " and b.trans_cat='$trans_cat' ")
					.($account==0 ? "" : " and a.acct_id=$account ");

		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=b.city ' : '');
		$sql = "select a.*, 
					b.trans_type_desc, 
					b.trans_cat,
					c.name as city_name
				from acc_trans a inner join acc_trans_type b on a.trans_type_code=b.trans_type_code $citystr 
				inner join security$suffix.sec_city c on a.city=c.code
				where a.city in($citylist) and a.status <> 'V'
					and a.trans_dt >= '$start_dt' and a.trans_dt <= '$end_dt'
					$condition 
				order by a.trans_dt desc, a.id desc 
			";
		$mrows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($mrows) > 0) {
			$idlist = '';
			foreach ($mrows as $row) {
				$idlist .= empty($idlist) ? $row['id'] : ','.$row['id'];
			}
			
			$sql = "select * from acc_trans_info
					where trans_id in ($idlist)
					order by trans_id
				";
			$drows = Yii::app()->db->createCommand($sql)->queryAll();
			$dtl = array();
			if (count($drows) > 0) {
				$tid = 0;
				$ctnt = array();
				foreach ($drows as $row) {
					if ($tid==0) $tid = $row['trans_id'];
					
					if ($tid!=$row['trans_id']) {
						$dtl[$tid] = $ctnt;
						$tid = $row['trans_id'];
						$ctnt = array();
					}
					
					$ctnt[$row['field_id']] = $row['field_value'];
				}
				$dtl[$tid] = $ctnt;
			}
			
			$acctlist = General::getAccountList($citylist);
			$ptypelist = General::getPayerTypeList();
			$acctitemlist = General::getAcctItemList();
			$acctcodelist = General::getAcctCodeList();
			
			foreach ($mrows as $row) {
				$temp = array();
				$temp['trans_dt'] = General::toDate($row['trans_dt']);
				$temp['trans_cat'] = empty($row['trans_cat']) ? '' : ($row['trans_cat']=='IN' ? Yii::t('trans','In') : Yii::t('trans','Out'));
				$temp['trans_type_desc'] = $row['trans_type_desc'];
				$temp['account_name'] = empty($row['acct_id']) ? '' : $acctlist[$row['acct_id']];

				$temp['payer_type'] = isset($dtl[$row['id']]['payer_type']) ? (empty($dtl[$row['id']]['payer_type']) ? '' : $ptypelist[$dtl[$row['id']]['payer_type']]) : '';
				$temp['payer_name'] = isset($dtl[$row['id']]['payer_name']) ? $dtl[$row['id']]['payer_name'] : '';
				$temp['cheque_no'] = isset($dtl[$row['id']]['cheque_no']) ? $dtl[$row['id']]['cheque_no'] : '';
				$temp['invoice_no'] = isset($dtl[$row['id']]['invoice_no']) ? $dtl[$row['id']]['invoice_no'] : '';
				$temp['handle_staff_name'] = isset($dtl[$row['id']]['handle_staff_name']) ? $dtl[$row['id']]['handle_staff_name'] : '';
				$temp['item_code_desc'] = isset($dtl[$row['id']]['item_code']) 
						? (empty($dtl[$row['id']]['item_code']) || !isset($acctitemlist[$dtl[$row['id']]['item_code']]) ? '' : $acctitemlist[$dtl[$row['id']]['item_code']]) 
						: '';
				$temp['acct_code_desc'] = isset($dtl[$row['id']]['acct_code']) 
						? (empty($dtl[$row['id']]['acct_code']) || !isset($acctcodelist[$dtl[$row['id']]['acct_code']]) ? '' : $acctcodelist[$dtl[$row['id']]['acct_code']]) 
						: '';
				$temp['service_dt'] = (isset($dtl[$row['id']]['year_no']) ? $dtl[$row['id']]['year_no'] : '').'/'
					.(isset($dtl[$row['id']]['month_no']) ? $dtl[$row['id']]['month_no'] : '');
				$temp['united_inv_no'] = isset($dtl[$row['id']]['united_inv_no']) ? $dtl[$row['id']]['united_inv_no'] : '';
				$temp['int_fee'] = isset($dtl[$row['id']]['int_fee']) ? ($dtl[$row['id']]['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')) : Yii::t('misc','No');
				$temp['amount'] = number_format((isset($row['amount']) ? $row['amount'] : '0'),2,'.','');
				$temp['detail'] = isset($row['detail']) ? $row['detail'] : '';
				$temp['item_desc'] = isset($row['trans_desc']) ? $row['trans_desc'] : '';
				$temp['remarks'] = isset($row['remarks']) ? $row['remarks'] : '';
				$temp['city_name'] = isset($row['city_name']) ? $row['city_name'] : '';
				$temp['req_ref_no'] = isset($dtl[$row['id']]['req_ref_no']) ? $dtl[$row['id']]['req_ref_no'] : '';
				$temp['approve_dt'] = $this->getApproveDate($temp['req_ref_no']);
				$this->data[] = $temp;
			}
		}
		return true;	}

	protected function getApproveDate($ref_no) {
		if (empty($ref_no)) return '';
		
		$suffix = Yii::app()->params['envSuffix'];
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=b.city ' : '');
		$sql = "select a.req_id, b.req_dt from acc_request_info a, acc_request b
				where a.req_id=b.id and a.field_id='ref_no' and a.field_value='$ref_no'
				limit 1
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$dt = $row['req_dt'];
			$id = $row['req_id'];
			$sql = "select b.id from workflow$suffix.wf_process a, workflow$suffix.wf_process_version b 
					where a.code='PAYMENT' and a.id = b.process_id
					and b.start_dt <= '$dt' and b.end_dt >= '$dt' 
					order by b.id desc limit 1
				";
			$row0 = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row0!==false) {
				$procId = $row0['id'];
				$sql = "select b.lcd 
						from workflow$suffix.wf_request a, 
							workflow$suffix.wf_request_transit_log b,
							workflow$suffix.wf_state c
						where a.proc_ver_id=$procId and a.doc_id=$id and a.id=b.request_id 
						and b.new_state=c.id and c.code in ('A', 'S')
						order by b.id desc limit 1 
					";
				$rs = Yii::app()->db->createCommand($sql)->queryRow();
				return ($rs!==false ? $rs['lcd'] : '');
			}
		}
		
		return '';
	}

	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
}
?>