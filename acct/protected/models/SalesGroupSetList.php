<?php

class SalesGroupSetList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'start_date'=>Yii::t('group','start date'),
			'end_date'=>Yii::t('group','end date'),
			'employee_id'=>Yii::t('group','staff'),
			'employee_code'=>Yii::t('group','employee code'),
			'employee_name'=>Yii::t('group','employee name'),
            'employee_type'=>Yii::t('group','common type'),
			'group_staff_name'=>Yii::t('group','manage staff'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
		$sql1 = "select a.*,b.code,b.name 
				from acc_group_set a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id
				where a.id>0 
			";
		$sql2 = "select count(a.id)
				from acc_group_set a  
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id
				where a.id>0  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'employee_code':
					$clause .= General::getSqlConditionClause('b.code',$svalue);
					break;
				case 'employee_name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
				case 'group_staff_name':
					$clause .= General::getSqlConditionClause('a.group_staff_name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by start_date desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'employee_code'=>$record['code'],
						'employee_name'=>$record['name'],
						'start_date'=>$record['start_date'],
						'end_date'=>$record['end_date'],
						'group_staff_name'=>$record['group_staff_name'],
                    );
			}
		}
		$session = Yii::app()->session;
		$session['salesGroupSet_c01'] = $this->getCriteria();
		return true;
	}

}
