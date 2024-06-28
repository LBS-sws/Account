<?php

class ExpenseSetNameList extends CListPageModel
{
    public static $type_str="expense";
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('give','Name'),
			'z_index'=>Yii::t('give','z_index'),
			'display'=>Yii::t('give','display'),
			'return_value'=>Yii::t('give','return city'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $type_str = self::$type_str;
		$sql1 = "select * 
				from acc_set_name 
				where type_str='{$type_str}' 
			";
		$sql2 = "select count(id)
				from acc_set_name 
				where type_str='{$type_str}' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'name':
					$clause .= General::getSqlConditionClause('name',$svalue);
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
						'name'=>$record['name'],
						'return_value'=>$record['return_value'],
						'z_index'=>$record['z_index'],
                        'display'=>$record['z_display']==1?Yii::t('give',"show"):Yii::t('give',"none"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['expenseSetName_c01'] = $this->getCriteria();
		return true;
	}

}
