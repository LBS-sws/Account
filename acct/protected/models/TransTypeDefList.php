<?php

class TransTypeDefList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'trans_type_desc'=>Yii::t('code','Trans. Type'),
			'city_name'=>Yii::t('code','City'),
			'account'=>Yii::t('code','Account'),
			'trans_cat'=>Yii::t('code','Type'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.*, c.code as city, c.name as city_name, b.acct_id, d.acct_no, d.acct_name 
				from acc_trans_type a 
				inner join security$suffix.sec_city c on c.code in ($city)
				left outer join acc_trans_type_def b on a.trans_type_code=b.trans_type_code and b.city=c.code
				left outer join acc_account d on d.id=b.acct_id 
				where a.adj_type='N' and a.trans_type_code<>'OPEN' 
			";
		$sql2 = "select count(a.trans_type_code)
				from acc_trans_type a 
				inner join security$suffix.sec_city c on c.code in ($city)
				left outer join acc_trans_type_def b on a.trans_type_code=b.trans_type_code and b.city=c.code
				left outer join acc_account d on d.id=b.acct_id 
				where a.adj_type='N' and a.trans_type_code<>'OPEN' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'account':
					$field = "concat(d.acct_name,' ',d.acct_no) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('c.name',$svalue);
					break;
				case 'trans_type_desc':
					$clause .= General::getSqlConditionClause('a.trans_type_desc',$svalue);
					break;
				case 'trans_cat':
					$field = "(select case trans_cat when 'IN' then '".Yii::t('code','In')."' 
							when 'OUT' then '".Yii::t('code','Out')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'c.name'; break;
				case 'trans_cat': $orderf = 'a.trans_cat'; break;
				case 'account': $orderf = 'd.acct_name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order = " order by c.name, a.trans_type_desc ";
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
					'trans_type_code'=>$record['trans_type_code'],
					'trans_type_desc'=>$record['trans_type_desc'],
					'trans_cat'=>$record['trans_cat']=='OUT'?Yii::t('code','Out'):Yii::t('code','In'),
					'account'=>$record['acct_name'].(empty($record['acct_no']) ? '' : ' ('.$record['acct_no'].')'),
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xc05'] = $this->getCriteria();
		return true;
	}

}
