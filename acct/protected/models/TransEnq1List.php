<?php

class TransEnq1List extends CListPageModel
{
	public $city;
	
	public function rules()	{
		$rtn1 = parent::rules();
		$rtn2 = array(
			array('city','safe'),
		);
		return array_merge($rtn1, $rtn2);
	}

	public function init() {
		$city = Yii::app()->user->city_allow();
		$a_city = explode(',',$city);
		$this->city = str_replace("'","",$a_city[0]);
		parent::init();
	}
	
	public function attributeLabels()
	{
		return array(	
			'acct_name'=>Yii::t('trans','Account Name'),
			'bank_name'=>Yii::t('trans','Bank'),
			'acct_no'=>Yii::t('trans','Account No.'),
			'acct_type_desc'=>Yii::t('trans','Account Type'),
			'balance'=>Yii::t('trans','Current Balance'),
			'city_name'=>Yii::t('misc','City'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.acct_no, a.acct_name, a.bank_name, b.name as city_name, c.acct_type_desc, a.city, b.code as trans_city, 
				AccountBalance(a.id,b.code,'2010-01-01 00:00:00',now()) as balance
				from security$suffix.sec_city b
				left outer join acc_account a on (b.code=a.city or a.city='99999') 
				left outer join acc_account_type c on a.acct_type_id=c.id
				where b.code in ($city)
			";
		$sql2 = "select count(a.id)
				from security$suffix.sec_city b
				left outer join acc_account a on (b.code=a.city or a.city='99999') 
				left outer join acc_account_type c on a.acct_type_id=c.id
				where b.code in ($city)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'acct_type_desc':
					$clause .= General::getSqlConditionClause('c.acct_type_desc',$svalue);
					break;
				case 'acct_no':
					$clause .= General::getSqlConditionClause('a.acct_no',$svalue);
					break;
				case 'bank_name':
					$clause .= General::getSqlConditionClause('a.bank_name',$svalue);
					break;
				case 'acct_name':
					$clause .= General::getSqlConditionClause('a.acct_name',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'acct_name': $orderf = 'c.acct_type_desc'; break;
				case 'acct_type_desc': $orderf = 'c.acct_type_desc'; break;
				case 'acct_no': $orderf = 'a.acct_no'; break;
				case 'bank_name': $orderf = 'a.bank_name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by b.code, a.id desc";

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
					'acct_type_desc'=>$record['acct_type_desc'],
					'acct_no'=>$record['acct_no'],
					'acct_name'=>$record['acct_name'],
					'bank_name'=>$record['bank_name'],
					'city_name'=>$record['city_name'],
					'balance'=>$record['balance'],
					'city'=>$record['city'],
					'trans_city'=>$record['trans_city'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe02_1'] = $this->getCriteria();
		return true;
	}
}
