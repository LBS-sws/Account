<?php

class SalesTableList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
            'employee_code'=>Yii::t('app','employee_code'),
            'employee_name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'user_name'=>Yii::t('app','user_name'),
            'time'=>Yii::t('app','Time'),
		);
	}
	
	public function searchColumns() {
		$search = array(
				'employee_code'=>"a.employee_code",
				'employee_name'=>'a.employee_name',
				'city'=>'e.name',
				'user_name'=>'c.name',

		);
		return $search;
	}

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
        $sql1 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,d.renewal_amount,d.renewalend_amount,e.name as cityname from acc_service_comm_hdr a
                 inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                 inner join  hr$suffix.hr_dept c on b.position=c.id      
                 inner join security$suffix.sec_city e on a.city=e.code 		  
                 left outer join  acc_service_comm_dtl d on a.id=d.hdr_id            
			     where   a.city in ($city) and b.city in ($city)
			";
        $sql2 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,d.renewal_amount,d.renewalend_amount,e.name as cityname from acc_service_comm_hdr a
                 inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                 inner join  hr$suffix.hr_dept c on b.position=c.id      
                 inner join security$suffix.sec_city e on a.city=e.code 		  
                 left outer join  acc_service_comm_dtl d on a.id=d.hdr_id            
			     where   a.city in ($city) and b.city in ($city)
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
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'employee_code': $orderf = 'a.employee_code'; break;
				case 'employee_name': $orderf = 'a.employee_name'; break;
				case 'city': $orderf = 'e.name'; break;
				case 'user_name': $orderf = 'c.name'; break;
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
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $str=str_replace('(','',$record['employee_code']);
                $str=str_replace(')','',$str);
//                $arr=$record['new_amount']+$record['edit_amount']+$record['end_amount']+$record['performance_amount']+$record['performanceedit_amount']+$record['performanceend_amount']+$record['renewal_amount']+$record['renewalend_amount'];
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'employee_code'=>$str,
                    'employee_name'=>$record['employee_name'],
                    'city'=>$record['cityname'],
                    'time'=>$record['year_no']."/".$record['month_no'],
                    'user_name'=>$record['name'],
//                    'comm_total_amount'=>$arr,

                );
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}