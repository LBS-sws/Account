<?php

class QueueList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
			'rpt_desc'=>Yii::t('queue','Report'),
			'req_dt'=>Yii::t('queue','Req. Date'),
			'fin_dt'=>Yii::t('queue','Comp. Date'),
			'status'=>Yii::t('queue','Status'),
			'id'=>Yii::t('queue','ID'),
			'rpt_type'=>Yii::t('queue','Format'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$uid = Yii::app()->user->id;
		$sql1 = "select a.*
				from acc_queue a 
				where a.username='".$uid."' 
			";
		$sql2 = "select count(a.id)
				from acc_queue a 
				where a.username='".$uid."' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'rpt_desc':
					$clause .= General::getSqlConditionClause('a.rpt_desc',$svalue);
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
				switch ($record['rpt_type']) {
					case 'FEED': $type_name = Yii::t('queue','Feedback'); break;
					case 'EMAIL': $type_name = Yii::t('queue','Email'); break;
					case 'MTHRPT': $type_name = 'EXCEL'; break;
					default: $type_name = $record['rpt_type'];
				}
				$this->attr[] = array(
					'id'=>$record['id'],
					'rpt_desc'=>Yii::t('report',$record['rpt_desc']),
					'req_dt'=>$record['req_dt'],
					'fin_dt'=>$record['fin_dt'],
					'status'=>General::getJobStatusDesc($record['status']),
					'sts'=>$record['status'],
					'rpt_type'=>$type_name,
					'ts'=>$record['ts'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xb01'] = $this->getCriteria();
		return true;
	}

}
