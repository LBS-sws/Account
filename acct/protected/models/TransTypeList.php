<?php

class TransTypeList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'trans_type_code'=>Yii::t('code','Code'),
			'trans_type_desc'=>Yii::t('code','Description'),
			'adj_type'=>Yii::t('code','Adj.'),
			'trans_cat'=>Yii::t('code','Type'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$city = Yii::app()->user->city_allow();
		$sql1 = "select * 
				from acc_trans_type 
				where trans_type_code <> '' 
			";
		$sql2 = "select count(trans_type_code)
				from acc_trans_type 
				where trans_type_code <> '' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'trans_type_code':
					$clause .= General::getSqlConditionClause('trans_type_code',$svalue);
					break;
				case 'trans_type_desc':
					$clause .= General::getSqlConditionClause('trans_type_desc',$svalue);
					break;
				case 'adj_type':
					$field = "(select case adj_type when 'N' then '".Yii::t('misc','No')."' 
							when 'Y' then '".Yii::t('misc','Yes')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
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
				case 'adj_cat': $orderf = 'adj_type'; break;
				case 'trans_cat': $orderf = 'trans_cat'; break;
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
					'trans_type_code'=>$record['trans_type_code'],
					'trans_type_desc'=>$record['trans_type_desc'],
					'adj_type'=>$record['adj_type']=='Y'?Yii::t('misc','Yes'):Yii::t('misc','No'),
					'trans_cat'=>$record['trans_cat']=='OUT'?Yii::t('code','Out'):Yii::t('code','In'),
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xc03'] = $this->getCriteria();
		return true;
	}

}
