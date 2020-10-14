<?php

class PayrollList extends CListPageModel
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
	
	public function retrieveDataByPage($pageNum=1) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$version = Yii::app()->params['version'];
		$cityarg = ($version=='intl' ? 'a.city,' : '');
		$sql1 = "select a.*, b.name as city_name , 
					docman$suffix.countdoc('PAYFILE1',a.id) as file1countdoc,
					(select case workflow$suffix.RequestStatus($cityarg 'PAYROLL',a.id,a.lcd)
							when '' then '4DF' 
							when 'PB' then '1PB' 
							when 'PA' then '2PA' 
							when 'PS' then '0PS' 
							when 'ED' then '3ED' 
					end) as wfstatus,
					workflow$suffix.RequestStatusDesc($cityarg 'PAYROLL',a.id,a.lcd) as wfstatusdesc
				from acc_payroll_file_hdr a inner join security$suffix.sec_city b on a.city=b.code 
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from acc_payroll_file_hdr a inner join security$suffix.sec_city b on a.city=b.code 
				where a.city in ($citylist)
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
		$clause .= $this->getDateRangeCondition('a.lcd');
		
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
		
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$wfstatus = (empty($record['wfstatus'])?'0DF':$record['wfstatus']);
					$this->attr[] = array(
						'id'=>$record['id'],
						'year_no'=>$record['year_no'],
						'month_no'=>$record['month_no'],
						'city'=>$record['city'],
						'city_name'=>$record['city_name'],
						'wfstatusdesc'=>(empty($record['wfstatusdesc'])?Yii::t('misc','Draft'):$record['wfstatusdesc']) ,
						'wfstatus'=> $wfstatus,
						'file1countdoc'=>$record['file1countdoc'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xs05'] = $this->getCriteria();
		return true;
	}
}
