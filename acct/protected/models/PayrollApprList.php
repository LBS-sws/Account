<?php

class PayrollApprList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'year_no'=>Yii::t('report','Year'),
			'month_no'=>Yii::t('report','Month'),
			'city_name'=>Yii::t('misc','City'),
			'wfstatusdesc'=>Yii::t('misc','Status'),
			'file1countdoc'=>Yii::t('trans','Files'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1, $type='P')
	{
//		$type = Yii::app()->user->validFunction('YN01') ? 'PA' : 'PH';
		
		$wf = new WorkflowPayroll;
		$wf->connection = Yii::app()->db;
		$list1 = $wf->getPendingRequestIdList('PAYROLL', 'PA', Yii::app()->user->id);
		$list2 = $wf->getPendingRequestIdList('PAYROLL', 'PB', Yii::app()->user->id);
		$list = '0';
		if (!empty($list1)) $list = $list1;
		if (!empty($list2)) $list = $list=='0' ? $list2 : $list.','.$list2;
		
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.*, b.name as city_name, 
					docman$suffix.countdoc('PAYFILE1',a.id) as file1countdoc,
					workflow$suffix.RequestStatusDesc('PAYROLL',a.id,a.lcd) as wfstatusdesc
				from acc_payroll_file_hdr a, security$suffix.sec_city b 
				where a.city in ($city) and a.city=b.code 
				and a.id in ($list)
			";
		$sql2 = "select count(a.id)
				from acc_payroll_file_hdr a, security$suffix.sec_city b 
				where a.city in ($city) and a.city=b.code 
				and a.id in ($list)
			";

		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'year_no':
					$clause .= General::getSqlConditionClause('a.year_no', $svalue);
					break;
				case 'month_no':
					$clause .= General::getSqlConditionClause('a.month_no', $svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('b.name', $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else
			$order = " order by a.year_no desc, a.month_no desc, a.city";

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
						'year_no'=>$record['year_no'],
						'month_no'=>$record['month_no'],
						'city'=>$record['city'],
						'city_name'=>$record['city_name'],
						'wfstatusdesc'=>(empty($record['wfstatusdesc'])?Yii::t('misc','Draft'):$record['wfstatusdesc']),
						'file1countdoc'=>$record['file1countdoc'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xs06'] = $this->getCriteria();
		return true;
	}

}