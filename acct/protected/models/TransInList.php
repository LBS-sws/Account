<?php

class TransInList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'trans_dt'=>Yii::t('trans','Trans. Date'),
			'trans_type_desc'=>Yii::t('trans','Trans. Type'),
			'acct_type_desc'=>Yii::t('trans','Account Type'),
			'bank_name'=>Yii::t('trans','Bank'),
			'acct_no'=>Yii::t('trans','Account No.'),
			'amount'=>Yii::t('trans','Amount'),
			'city_name'=>Yii::t('misc','City'),
			'status'=>Yii::t('trans','Status'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),
		);
	}
	
	public function searchColumns() {
		$search = array(
				'trans_dt'=>"date_format(a.trans_dt,'%Y/%m/%d')",
				'trans_type_desc'=>'e.trans_type_desc',
				'acct_type_desc'=>'c.acct_type_desc',
				'bank_name'=>'d.bank_name',
				'acct_no'=>'d.acct_no',
				'amount'=>'a.amount',
				'int_fee'=>"(select case g.field_value when 'Y' then '".Yii::t('misc','Yes')."' 
							else '".Yii::t('misc','No')."' 
						end) ",
				'status'=>"(select case a.status when 'V' then '".Yii::t('app','Void')."' 
							else '' 
						end) ",
		);
		if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
		return $search;
	}

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=e.city ' : '');
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.trans_dt, e.trans_type_desc, c.acct_type_desc, d.bank_name, d.acct_no, 
					b.name as city_name, a.amount, a.status, g.field_value as int_fee      
				from acc_trans a
				inner join security$suffix.sec_city b on a.city=b.code 
				inner join acc_account d on a.acct_id = d.id
				inner join acc_account_type c on d.acct_type_id=c.id
				inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where a.city in ($city)
				and a.trans_type_code<>'OPEN' 
				and e.trans_cat='IN' 
			";
		$sql2 = "select count(a.id)
				from acc_trans a
				inner join security$suffix.sec_city b on a.city=b.code 
				inner join acc_account d on a.acct_id = d.id
				inner join acc_account_type c on d.acct_type_id=c.id 
				inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where a.city in ($city)
				and a.trans_type_code<>'OPEN' 
				and e.trans_cat='IN' 
			";
		$clause = "";
		if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$columns = $this->searchColumns();
				$clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
			}
		}
		$clause .= $this->getDateRangeCondition('a.trans_dt');
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'trans_type_name': $orderf = 'e.trans_type_desc'; break;
				case 'acct_type_desc': $orderf = 'c.acct_type_desc'; break;
				case 'acct_no': $orderf = 'd.acct_no'; break;
				case 'bank_name': $orderf = 'd.bank_name'; break;
				case 'amount': $orderf = 'a.amount'; break;
				case 'status': $orderf = 'a.status'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by a.id desc";

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
					'acct_type_desc'=>$record['acct_type_desc'],
					'amount'=>$record['amount'],
					'acct_no'=>$record['acct_no'],
					'bank_name'=>$record['bank_name'],
					'city_name'=>$record['city_name'],
					'status'=>($record['status']=='A'?'':General::getTransStatusDesc($record['status'])),
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}
