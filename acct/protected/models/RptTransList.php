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
			'int_fee'=>array('label'=>Yii::t('trans','Integrated Fee'),'width'=>20,'align'=>'C'),
			'amount'=>array('label'=>Yii::t('trans','Amount'),'width'=>20,'align'=>'R'),			'detail'=>array('label'=>Yii::t('trans','Detail'),'width'=>40,'align'=>'L'),
			'item_desc'=>array('label'=>Yii::t('trans','Remarks 1'),'width'=>40,'align'=>'L'),			'remarks'=>array('label'=>Yii::t('trans','Remarks 2'),'width'=>40,'align'=>'L'),
		);	}	
	public function genReport() {
		$this->retrieveData();
		$this->title = $this->getReportName();
		$this->subtitle = Yii::t('report','Date').':'.$this->criteria['START_DT'].' - '.$this->criteria['END_DT'];
		return $this->exportExcel();
	}

	public function retrieveData() {
		$start_dt = $this->criteria['START_DT'];
		$end_dt = $this->criteria['END_DT'];
		$city = $this->criteria['CITY'];

		$sql = "select a.*, 
					b.trans_type_desc, 
					b.trans_cat
				from acc_trans a inner join acc_trans_type b on a.trans_type_code=b.trans_type_code 
				where a.city='$city' and a.status <> 'V'
					and a.trans_dt >= '$start_dt' and a.trans_dt <= '$end_dt'
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
			
			$acctlist = General::getAccountList($city);
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
				$temp['item_code_desc'] = isset($dtl[$row['id']]['item_code']) ? (empty($dtl[$row['id']]['item_code']) ? '' : $acctitemlist[$dtl[$row['id']]['item_code']]) : '';
				$temp['acct_code_desc'] = isset($dtl[$row['id']]['acct_code']) ? (empty($dtl[$row['id']]['acct_code']) ? '' : $acctcodelist[$dtl[$row['id']]['acct_code']]) : '';
				$temp['service_dt'] = (isset($dtl[$row['id']]['year_no']) ? $dtl[$row['id']]['year_no'] : '').'/'
					.(isset($dtl[$row['id']]['month_no']) ? $dtl[$row['id']]['month_no'] : '');
				$temp['united_inv_no'] = isset($dtl[$row['id']]['united_inv_no']) ? $dtl[$row['id']]['united_inv_no'] : '';
				$temp['int_fee'] = isset($dtl[$row['id']]['int_fee']) ? ($dtl[$row['id']]['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')) : Yii::t('misc','No');
				$temp['amount'] = number_format((isset($row['amount']) ? $row['amount'] : '0'),2,'.','');
				$temp['detail'] = isset($row['detail']) ? $row['detail'] : '';
				$temp['item_desc'] = isset($row['trans_desc']) ? $row['trans_desc'] : '';
				$temp['remarks'] = isset($row['remarks']) ? $row['remarks'] : '';
				$this->data[] = $temp;
			}
		}
		return true;	}

	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
}
?>