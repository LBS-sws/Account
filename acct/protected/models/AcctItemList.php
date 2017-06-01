<?php

class AcctItemList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'code'=>Yii::t('code','Code'),
			'name'=>Yii::t('code','Description'),
			'item_type'=>Yii::t('code','Type'),
			'acct_code'=>Yii::t('code','Account Code'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.* 
				from acc_account_item a 
				where a.code <> '' 
			";
		$sql2 = "select count(a.code)
				from acc_account_item a 
				where a.code <> '' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('a.code',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'item_type':
					$field = "(select case a.item_type when 'I' then '".Yii::t('trans','In')."' 
							when 'O' then '".Yii::t('trans','Out')."' 
							when 'B' then '".Yii::t('trans','Both')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
				case 'acct_code':
					$clause .= General::getSqlConditionClause('a.acct_code',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'rtp_cat': $orderf = 'a.rpt_cat'; break;
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
					'code'=>$record['code'],
					'name'=>$record['name'],
					'item_type'=>($record['item_type']=='I' ? Yii::t('trans','In') :($record['item_type']=='O' ? Yii::t('trans','Out') :Yii::t('trans','Both'))),
					'acct_code'=>$record['acct_code'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xc06'] = $this->getCriteria();
		return true;
	}

}
