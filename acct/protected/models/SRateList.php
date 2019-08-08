<?php

class SRateList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'city_name'=>Yii::t('misc','City'),
			'start_dt'=>Yii::t('service','Start Date'),
            'name'=>Yii::t('service','Name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.city, a.start_dt, b.name as city_name 
				from acc_service_rate_hdr a
				left outer join security$suffix.sec_city b on a.city=b.code				
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from acc_service_rate_hdr a
				left outer join security$suffix.sec_city b on a.city=b.code		  
				where a.city in ($citylist)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'start_dt':
					$clause .= General::getSqlConditionClause('a.start_dt',$svalue);
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
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order = " order by a.city, a.start_dt desc";
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
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'start_dt'=>General::toDate($record['start_dt']),
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xg01'] = $this->getCriteria();
		return true;
	}

}
