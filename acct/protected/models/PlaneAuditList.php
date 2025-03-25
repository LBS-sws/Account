<?php

class PlaneAuditList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'code'=>Yii::t('plane','employee code'),
			'name'=>Yii::t('plane','employee name'),
			'city'=>Yii::t('plane','city'),
			'city_name'=>Yii::t('plane','city'),
			'job_num'=>Yii::t('plane','job num'),
			'money_num'=>Yii::t('plane','money num'),
			'year_num'=>Yii::t('plane','year num'),
			'other_sum'=>Yii::t('plane','other sum'),
			'plane_date'=>Yii::t('plane','plane date'),
			'plane_sum'=>Yii::t('plane','plane sum'),

			'entry_time'=>Yii::t('plane','entry time'),//入职日期
			'department'=>Yii::t('plane','department'),//部门
			'position'=>Yii::t('plane','position'),//职位
			'staff_leader'=>Yii::t('plane','staff leader'),//队长/组长
			'plane'=>Yii::t('plane','Plane Reward'),//直升机奖励
            'old_pay_wage'=>Yii::t('plane','old shall pay wages'),//原机制应发工资
            'difference'=>Yii::t('plane','difference'),//差額
            'plane_status'=>Yii::t('plane','plane status'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $cityList = Yii::app()->user->city_allow();
		$sql1 = "select f.*,a.code,a.name,b.name as city_name,(IFNULL(f.plane_sum,0)-IFNULL(f.old_pay_wage,0)) as difference
				from acc_plane f 
				LEFT JOIN hr{$suffix}.hr_employee a ON a.id=f.employee_id
				LEFT JOIN security$suffix.sec_city b ON b.code=f.city  
				where f.plane_status=1 and f.city in ({$cityList})  
			";
		$sql2 = "select count(f.id)
				from acc_plane f 
				LEFT JOIN hr{$suffix}.hr_employee a ON a.id=f.employee_id
				LEFT JOIN security$suffix.sec_city b ON b.code=f.city  
				where f.plane_status=1 and f.city in ({$cityList})  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'code':
					$clause .= General::getSqlConditionClause('a.code',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by f.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
			    $colorArr = PlaneAwardList::getPlaneStatusList($record["plane_status"]);
			    $this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'color'=>floatval($record['difference'])>0?"":"color:red",
                    'name'=>$record['name'],
                    'city_name'=>$record['city_name'],
                    'job_num'=>$record['job_num'],
                    'money_num'=>$record['money_num'],
                    'plane_date'=>$record['plane_year']."年".$record['plane_month']."月",
                    'other_sum'=>floatval($record['other_sum']),
                    'plane_sum'=>floatval($record['plane_sum']),
                    'old_pay_wage'=>floatval($record['old_pay_wage']),
                    'difference'=>floatval($record['difference']),
                    'style'=>$colorArr['color'],
                    'plane_status'=>$colorArr['str'],
                );
			}
		}
		$session = Yii::app()->session;
		$session['planeAudit_c01'] = $this->getCriteria();
		return true;
	}


    public function getCountConsult(){
        //$suffix = Yii::app()->params['envSuffix'];
        $cityList = Yii::app()->user->city_allow();
        $sql = "select count(id)
				from acc_plane 
				where plane_status=1 and city in ({$cityList}) 
			";
        $rtn = Yii::app()->db->createCommand($sql)->queryScalar();
        return $rtn;
    }
}
