<?php

class IqueueList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
			'import_type'=>Yii::t('import','Import Type'),
			'req_dt'=>Yii::t('import','Req. Date'),
			'fin_dt'=>Yii::t('import','Comp. Date'),
			'status'=>Yii::t('import','Status'),
			'id'=>Yii::t('import','ID'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$uid = Yii::app()->user->id;
		$sql1 = "select a.*
				from acc_import_queue a 
				where a.username='".$uid."' 
			";
		$sql2 = "select count(a.id)
				from acc_import_queue a 
				where a.username='".$uid."' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'import_type':
					$clause .= General::getSqlConditionClause('a.import_type',$svalue);
					break;
				case 'status':
					$field = "(select case a.status when 'P' then '".General::getJobStatusDesc('P')."' 
							when 'I' then '".General::getJobStatusDesc('I')."' 
							when 'C' then '".General::getJobStatusDesc('C')."' 
							when 'F' then '".General::getJobStatusDesc('F')."' 
							when 'E' then '".General::getJobStatusDesc('E')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field, $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order .= " order by a.req_dt desc ";
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
					'import_type'=>Yii::t('import',$record['import_type']),
					'req_dt'=>$record['req_dt'],
					'fin_dt'=>$record['fin_dt'],
					'status'=>General::getJobStatusDesc($record['status']),
					'sts'=>$record['status'],
					'ts'=>$record['ts'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xc08'] = $this->getCriteria();
		return true;
	}

	public function getLogContent($id) {
		$sql = "select param_text from acc_import_queue_param where queue_id=$id and param_field='LOG'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row===false) ? '' : $row['param_text'];
	}
}
