<?php

class ExpenseApplyList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'city'=>Yii::t('give','City'),
            'exp_code'=>Yii::t('give','expense code'),
			'apply_date'=>Yii::t('give','apply date'),
			'employee'=>Yii::t('give','apply user'),
            'department'=>Yii::t('give','department'),
			'amt_money'=>Yii::t('give','sum money'),
			'status_type'=>Yii::t('give','status type'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
		$sql1 = "select a.*,g.name as city_name,f.name as department_name,b.code as employee_code,b.name as employee_name 
				from acc_expense a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id
				LEFT JOIN hr{$suffix}.hr_dept f ON b.department=f.id
				LEFT JOIN security{$suffix}.sec_city g ON g.code=a.city
				where a.lcu='{$uid}' and a.table_type=1 
			";
		$sql2 = "select count(a.id)
				from acc_expense a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id
				LEFT JOIN hr{$suffix}.hr_dept f ON b.department=f.id
				LEFT JOIN security{$suffix}.sec_city g ON g.code=a.city
				where a.lcu='{$uid}' and a.table_type=1  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'city':
					$clause .= General::getSqlConditionClause('g.name',$svalue);
					break;
				case 'apply_date':
					$clause .= General::getSqlConditionClause('a.apply_date',$svalue);
					break;
				case 'exp_code':
					$clause .= General::getSqlConditionClause('a.exp_code',$svalue);
					break;
				case 'employee':
					$clause .= "and (b.code like '%{$svalue}%' or b.name like '%{$svalue}%') ";
					break;
                case 'department':
                    $clause .= General::getSqlConditionClause('f.name',$svalue);
                    break;
                case 'status_type':
                    $statusSql = ExpenseFun::getSearchStatusForStr($svalue);
                    $clause .= "and a.status_type in ({$statusSql})";
                    break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
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
                    'city'=>$record['city_name'],
                    'exp_code'=>$record['exp_code'],
                    'department'=>$record['department_name'],
                    'employee'=>$record['employee_name']." ({$record['employee_code']})",
                    'apply_date'=>General::toDate($record['apply_date']),
                    'amt_money'=>$record['amt_money'],
                    'status_type'=>$record['status_type'],
                    'color'=>ExpenseFun::getColorForStatusType($record['status_type']),
                    'status_str'=>ExpenseFun::getStatusStrForStatusType($record['status_type']),
                );
			}
		}
		$session = Yii::app()->session;
		$session['expenseApply_c01'] = $this->getCriteria();
		return true;
	}

    public function getCountConsult(){
        //$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $sql = "select count(id)
				from acc_expense 
				where lcu='{$uid}' and table_type=1 and status_type=7  
			";
        $rtn = Yii::app()->db->createCommand($sql)->queryScalar();
        return $rtn;
    }
}
