<?php

class PlaneSetOtherList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'set_name'=>Yii::t('plane','Other Name'),
			'z_display'=>Yii::t('plane','display'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
		$sql1 = "select * 
				from acc_plane_set_other 
				where city='{$city}'  
			";
		$sql2 = "select count(id)
				from acc_plane_set_other 
				where city='{$city}'  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'set_name':
					$clause .= General::getSqlConditionClause('set_name',$svalue);
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
						'set_name'=>$record['set_name'],
						'z_display'=>$record['z_display']==1?Yii::t("misc","Yes"):Yii::t("misc","No"),
                    );
			}
		}
		$session = Yii::app()->session;
		$session['planeSetOther_c01'] = $this->getCriteria();
		return true;
	}

}
