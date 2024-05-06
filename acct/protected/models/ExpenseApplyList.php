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
				where a.lcu='{$uid}' 
			";
		$sql2 = "select count(a.id)
				from acc_expense a 
				LEFT JOIN hr{$suffix}.hr_employee b ON a.employee_id=b.id
				LEFT JOIN hr{$suffix}.hr_dept f ON b.department=f.id
				LEFT JOIN security{$suffix}.sec_city g ON g.code=a.city
				where a.lcu='{$uid}' 
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
                    'color'=>self::getColorForStatusType($record['status_type']),
                    'status_str'=>self::getStatusStrForStatusType($record['status_type']),
                );
			}
		}
		$session = Yii::app()->session;
		$session['expenseApply_c01'] = $this->getCriteria();
		return true;
	}

	public static function getColorForStatusType($status_type){
	    $list = array(
	        0=>" ",//草稿
	        1=>" text-primary",//待确认
	        2=>" text-primary",//待审核
	        7=>" text-danger",//已拒绝
	        8=>" text-muted",//已审核
	        9=>" text-muted",//已完成
        );
	    if(key_exists($status_type,$list)){
	        return $list[$status_type];
        }else{
            return "";
        }
    }

	public static function getStatusStrForStatusType($status_type){
	    $list = array(
	        0=>Yii::t("give","draft"),//草稿
	        1=>Yii::t("give","wait confirm"),//待确认
	        2=>Yii::t("give","wait audit"),//待审核
	        7=>Yii::t("give","rejected"),//已拒绝
	        8=>Yii::t("give","audited"),//已审核
	        9=>Yii::t("give","finish"),//已完成
        );
        if(key_exists($status_type,$list)){
            return $list[$status_type];
        }else{
            return $status_type;
        }
    }

    public function getCountConsult(){
        //$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $sql = "select count(id)
				from acc_expense 
				where lcu='{$uid}' and status_type=7  
			";
        $rtn = Yii::app()->db->createCommand($sql)->queryScalar();
        return $rtn;
    }
}
