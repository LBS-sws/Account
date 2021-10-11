<?php

class IDLadderList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'city_name'=>Yii::t('misc','City'),
			'only_num'=>Yii::t('service','judge general'),
			'start_dt'=>Yii::t('service','Start Date'),
            'name'=>Yii::t('service','Name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.city,a.name, a.only_num, a.start_dt, b.name as city_name 
				from acc_serviceID_rate_hdr a
				left outer join security$suffix.sec_city b on a.city=b.code				
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from acc_serviceID_rate_hdr a
				left outer join security$suffix.sec_city b on a.city=b.code		  
				where a.city in ($citylist)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
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
			$order = " order by a.city, a.id desc";
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
					'name'=>$record['name'],
					'only_num'=>$record['only_num']==1?Yii::t("service","general"):"",
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'start_dt'=>General::toDate($record['start_dt']),
				);
			}
		}
		$session = Yii::app()->session;
		$session['IDLadderList_xg01'] = $this->getCriteria();
		return true;
	}

}
