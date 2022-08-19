<?php

class ConsultSetList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'good_name'=>Yii::t('consult','good name'),
			'z_index'=>Yii::t('consult','z_index'),
			'z_display'=>Yii::t('consult','display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from acc_consult_set 
				where 1=1 
			";
		$sql2 = "select count(id)
				from acc_consult_set 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'good_name':
					$clause .= General::getSqlConditionClause('good_name',$svalue);
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
						'good_name'=>$record['good_name'],
						'z_index'=>$record['z_index'],
                        'z_display'=>$record['z_display']==1?Yii::t("consult","show"):Yii::t("consult","hide"),
					);
			}
		}
		$session = Yii::app()->session;
		$session['consultSet_c01'] = $this->getCriteria();
		return true;
	}

}
