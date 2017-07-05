<?php

class TransEnq2List extends CListPageModel
{
	public $fm_dt;
	public $to_dt;
	public $acct_id;
	public $city;
	
	public $acct_no;
	public $acct_name;
	public $bank_name;
	public $city_name;
	public $balance;
	
	public function rules()	{
		$rtn1 = parent::rules();
		$rtn2 = array(
			array('fm_dt, to_dt','date','allowEmpty'=>false,
				'format'=>array('yyyy/MM/dd','yyyy-MM-dd','yyyy/M/d','yyyy-M-d',),
			),
			array('acct_id, city, acct_no, acct_name, bank_name, city_name, balance','safe'),
		);
		return array_merge($rtn1, $rtn2);
	}

	public function init() {
		$this->to_dt = date("Y/m/d");
		$this->fm_dt = date("Y", strtotime($this->to_dt)).'/'.date("m", strtotime($this->to_dt)).'/01';
		parent::init();
	}
	
	public function attributeLabels()
	{
		return array(	
			'fm_dt'=>Yii::t('misc','Start Date'),
			'to_dt'=>Yii::t('misc','End Date'),
			'pay_subject'=>Yii::t('trans','Payer').'/'.Yii::t('trans','Payee'),
			'city'=>Yii::t('misc','City'),
			'acct_name'=>Yii::t('trans','Account Name'),
			'bank_name'=>Yii::t('trans','Bank Name'),
			'acct_no'=>Yii::t('trans','Account No.'),
			'balance'=>Yii::t('trans','Current Balance'),
			'trans_dt'=>Yii::t('trans','Trans. Date'),
			'trans_type_desc'=>Yii::t('trans','Trans. Type'),
			'amount_in'=>Yii::t('trans','Amount(In)'),
			'amount_out'=>Yii::t('trans','Amount(Out)'),
			'cheque_no'=>Yii::t('trans','Cheque No.'),
			'invoice_no'=>Yii::t('trans','China Invoice No.'),
			'trans_desc'=>Yii::t('trans','Remarks'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),
		);
	}
	
	public function retrieveHeaderData($index,$city) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.id, a.acct_no, a.acct_name, a.bank_name, b.name as city_name,
				AccountBalance(a.id,'$city','2010-01-01 00:00:00',now()) as balance
				from acc_account a
				inner join security$suffix.sec_city b on b.code='$city' 
				inner join acc_account_type c on a.acct_type_id=c.id
				where (a.city = '$city' or a.city = '99999')
				and a.id=$index
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->acct_id = $row['id'];
			$this->acct_no = $row['acct_no'];
			$this->acct_name = $row['acct_name'];
			$this->bank_name = $row['bank_name'];
			$this->city_name = $row['city_name'];
			$this->balance = $row['balance'];
			$this->city = $city;
		}
		return;
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$city = $this->city;

		$acct_id = $this->acct_id;
		$fdt = General::toMyDate($this->fm_dt);
		$tdt = General::toMyDate($this->to_dt);
		$sql1 = "select a.id, a.trans_dt, e.trans_type_desc, a.status, b.field_value as pay_subject, 
				c.field_value as cheque_no, d.field_value as invoice_no, e.trans_cat, a.trans_desc, g.field_value as int_fee, 
				if(e.trans_cat='IN',a.amount,null) as amount_in,
				if(e.trans_cat='IN',null,a.amount) as amount_out,
				docman$suffix.countdoc('TRANS',a.id) as no_of_attm, a.trans_type_code
				from acc_trans a inner join acc_trans_type e on a.trans_type_code=e.trans_type_code
				left outer join acc_trans_info b on a.id=b.trans_id and b.field_id='payer_name'
				left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='cheque_no'
				left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='invoice_no'
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where a.status<>'V'
				and a.trans_dt >= '$fdt' and a.trans_dt <= '$tdt'
				and a.acct_id = $acct_id and a.city='$city'
			";
		$sql2 = "select count(a.id)
				from acc_trans a inner join acc_trans_type e on a.trans_type_code=e.trans_type_code
				left outer join acc_trans_info b on a.id=b.trans_id and b.field_id='payer_name'
				left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='cheque_no'
				left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='invoice_no'
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where a.status<>'V'
				and a.trans_dt >= '$fdt' and a.trans_dt <= '$tdt'
				and a.acct_id = $acct_id and a.city='$city'
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'trans_type_desc':
					$clause .= General::getSqlConditionClause('e.trans_type_desc',$svalue);
					break;
				case 'trans_desc':
					$clause .= General::getSqlConditionClause('a.trans_desc',$svalue);
					break;
				case 'pay_subject':
					$clause .= General::getSqlConditionClause('b.field_value',$svalue);
					break;
				case 'cheque_no':
					$clause .= General::getSqlConditionClause('c.field_value',$svalue);
					break;
				case 'invoice_no':
					$clause .= General::getSqlConditionClause('d.field_value',$svalue);
					break;
				case 'int_fee':
					$field = "(select case g.field_value when 'Y' then '".Yii::t('misc','Yes')."' 
							else '".Yii::t('misc','No')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'trans_dt': $orderf = 'a.trans_dt'; break;
				case 'trans_type_desc': $orderf = 'e.trans_type_desc'; break;
				case 'pay_subject': $orderf = 'b.field_value'; break;
				case 'cheque_no': $orderf = 'c.field_value'; break;
				case 'invoice_no': $orderf = 'd.field_value'; break;
				case 'int_fee': $orderf = 'g.field_value'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by a.trans_dt desc, a.id desc";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
					'id'=>$record['id'],
					'trans_dt'=>General::toDate($record['trans_dt']),
					'trans_type_desc'=>$record['trans_type_desc'],
					'amount_in'=>$record['amount_in'],
					'amount_out'=>$record['amount_out'],
					'cheque_no'=>$record['cheque_no'],
					'invoice_no'=>$record['invoice_no'],
					'pay_subject'=>$record['pay_subject'],
					'trans_desc'=>$record['trans_desc'],
					'status'=>General::getTransStatusDesc($record['status']),
					'no_of_attm'=>$record['no_of_attm'],
					'trans_cat'=>$record['trans_cat'],
					'trans_type_code'=>$record['trans_type_code'],
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe02_2'] = $this->getCriteria();
		return true;
	}

	public function getCriteria() {
		$rtn1 = parent::getCriteria();
		$rtn2 = array(
				'fm_dt'=>$this->fm_dt,
				'to_dt'=>$this->to_dt,
				'acct_id'=>$this->acct_id,
			);
		return array_merge($rtn1, $rtn2);
	}
}
