<?php

class AccountList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'acct_type_desc'=>Yii::t('code','Type'),
			'acct_no'=>Yii::t('code','Account No.'),
			'acct_name'=>Yii::t('code','Account Name'),
			'bank_name'=>Yii::t('code','Bank'),
			'city_name'=>Yii::t('code','City'),
			'coa'=>Yii::t('code','COA'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$sql1 = "select a.id, a.acct_no, a.acct_name, a.bank_name, b.name as city_name, c.acct_type_desc, a.coa, a.city   
				from acc_account a 
				inner join acc_account_type c on a.acct_type_id=c.id
				left outer join security$suffix.sec_city b on a.city=b.code
				where (a.city = '$city' or a.city='99999')
			";
		$sql2 = "select count(a.id)
				from acc_account a 
				inner join acc_account_type c on a.acct_type_id=c.id
				left outer join security$suffix.sec_city b on a.city=b.code
				where (a.city = '$city' or a.city='99999')
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
				case 'coa':
					$clause .= General::getSqlConditionClause('a.coa',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}

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
					'city'=>$record['city'],
					'coa'=>$record['coa'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xc02'] = $this->getCriteria();
		return true;
	}

}
