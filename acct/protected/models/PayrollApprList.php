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
            'amt_total'=>Yii::t('trans','Total'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1, $type='P')
	{
		$wf = new WorkflowPayroll;
		$wf->connection = Yii::app()->db;
		$arrPix = array("PA","PB","PC","PD","PE","P1","P2","P3","P4");
        $list = "";
		foreach ($arrPix as $pix){
            $listTemp = $wf->getPendingRequestIdList('PAYROLL',$pix, Yii::app()->user->id);
            if(!empty($listTemp)){
                $list.=empty($list)?"":",";
                $list.=$listTemp;
            }
        }
        $list=empty($list)?0:$list;

		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$version = Yii::app()->params['version'];
		$cityarg = ($version=='intl' ? 'a.city,' : '');
		$sql1 = "select a.*, b.name as city_name,f.data_value as amt_total, 
					docman$suffix.countdoc('PAYFILE1',a.id) as file1countdoc,
					workflow$suffix.RequestStatusDesc($cityarg 'PAYROLL',a.id,a.lcd) as wfstatusdesc
				from acc_payroll_file_hdr a
				LEFT join security$suffix.sec_city b on a.city=b.code 
				LEFT join acc_payroll_file_dtl f on f.hdr_id=a.id and f.data_field='amt_total' 
				where a.city in ($city) 
				and a.id in ($list)
			";
		$sql2 = "select count(a.id)
				from acc_payroll_file_hdr a 
				LEFT join security$suffix.sec_city b on a.city=b.code 
				LEFT join acc_payroll_file_dtl f on f.hdr_id=a.id and f.data_field='amt_total' 
				where a.city in ($city) 
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
                    'amt_total'=>$record['amt_total'],
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
