<?php

class BalAdjList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'audit_year'=>Yii::t('trans','Year'),
			'audit_month'=>Yii::t('trans','Month'),
			'city_name'=>Yii::t('misc','City'),
			'acct_name'=>Yii::t('trans','Account Name'),
			'bal_month_end'=>Yii::t('trans','Balance'),
			'bal_t3'=>Yii::t('trans','T3 Balance'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.audit_year, a.audit_month, b.acct_id, 
					d.name as city_name, a.city, c.acct_name, b.bal_month_end, b.bal_t3,
					c.bank_name, e.acct_type_desc
				from acc_t3_audit_hdr a inner join acc_t3_audit_dtl b on a.id=b.hdr_id
				inner join acc_account c on b.acct_id=c.id
				left outer join security$suffix.sec_city d on a.city=d.code
				left outer join acc_account_type e on c.acct_type_id=e.id
				where a.city in ($city)
			";
		$sql2 = "select count(a.id)
				from acc_t3_audit_hdr a inner join acc_t3_audit_dtl b on a.id=b.hdr_id 
				inner join acc_account c on b.acct_id=c.id
				left outer join security$suffix.sec_city d on a.city=d.code
				left outer join acc_account_type e on c.acct_type_id=e.id
				where a.city in ($city)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'audit_year':
					$clause .= General::getSqlConditionClause('a.audit_year',$svalue);
					break;
				case 'audit_month':
					$clause .= General::getSqlConditionClause('a.audit_month',$svalue);
					break;
				case 'acct_name':
					$clause .= General::getSqlConditionClause("concat(ifnull(e.acct_type_desc,''),' ',ifnull(c.acct_name,''),' ',ifnull(c.bank_name,''))",$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('d.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'd.name'.($this->orderType=='D' ? ' desc' : ''); break;
				case 'acct_name': 
					$orderf = 'e.acct_type_desc'.($this->orderType=='D' ? ' desc,' : ',')
						.'c.acct_name'.($this->orderType=='D' ? ' desc,' : ',')
						.'c.bank_name'.($this->orderType=='D' ? ' desc' : ''); 
					break;
				default: $orderf = $this->orderField.($this->orderType=='D' ? ' desc' : ''); break;
			}
			$order .= " order by ".$orderf." ";
		}
		if ($order=="") $order = "order by a.audit_year desc, a.audit_month desc, e.acct_type_desc, c.acct_name, c.bank_name";

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
					'audit_year'=>$record['audit_year'],
					'audit_month'=>$record['audit_month'],
					'acct_id'=>$record['acct_id'],
					'acct_name'=>'('.$record['acct_type_desc'].') '.$record['acct_name']
						.(empty($record['bank_name']) ? '' : ' - ').$record['bank_name'],
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'bal_month_end'=>$record['bal_month_end'],
					'bal_t3'=>$record['bal_t3'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe06'] = $this->getCriteria();
		return true;
	}
}
