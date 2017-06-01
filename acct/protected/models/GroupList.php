<?php

class GroupList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'temp_id'=>Yii::t('group','Template ID'),
			'temp_name'=>Yii::t('group','Template Name'),
			'system_name'=>Yii::t('group','System Name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$a_sys = Yii::app()->params['systemMapping'];
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select temp_id, temp_name, system_id
				from security$suffix.sec_template 
			";
		$sql2 = "select count(temp_id)
				from security$suffix.sec_template 
			";
		$clause = "where temp_id > 0 ";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'temp_name':
					$clause .= General::getSqlConditionClause('temp_name',$svalue);
					break;
				case 'system_name':
					$field = "(select case system_id";
					foreach ($a_sys as $sid=>$value) {
						$field .= " when '$sid' then '".Yii::t('app',$value['name'])."' ";
					}
					$field .= " end) ";
					$clause .= General::getSqlConditionClause($field, $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
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
					'temp_id'=>$record['temp_id'],
					'temp_name'=>$record['temp_name'],
					'system_name'=>Yii::t('app',$a_sys[$record['system_id']]['name']),
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_gl'] = $this->getCriteria();
		return true;
	}

}
