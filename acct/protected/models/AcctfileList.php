<?php

class AcctfileList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'year_no'=>Yii::t('report','Year'),
			'month_no'=>Yii::t('report','Month'),
			'city_name'=>Yii::t('misc','City'),
			'status'=>Yii::t('user','Email'),
			'file1countdoc'=>Yii::t('trans','General AC'),
			'file2countdoc'=>Yii::t('trans','Basic AC'),
			'file3countdoc'=>Yii::t('trans','Other AC'),
			'file4countdoc'=>Yii::t('trans','Other Files'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$sql1 = "select a.*, b.name as city_name , c.data_value as mail_dt,
				docman$suffix.countdoc('ACCTFILE1',a.id) as file1countdoc,
				docman$suffix.countdoc('ACCTFILE2',a.id) as file2countdoc,
				docman$suffix.countdoc('ACCTFILE3',a.id) as file3countdoc,
				docman$suffix.countdoc('ACCTFILE4',a.id) as file4countdoc
				from acc_account_file_hdr a inner join security$suffix.sec_city b on a.city=b.code 
				left outer join acc_account_file_dtl c on a.id=c.hdr_id and c.data_field='mail_dt'
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from acc_account_file_hdr a inner join security$suffix.sec_city b on a.city=b.code 
				left outer join acc_account_file_dtl c on a.id=c.hdr_id and c.data_field='mail_dt'
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
					$this->attr[] = array(
						'id'=>$record['id'],
						'year_no'=>$record['year_no'],
						'month_no'=>$record['month_no'],
						'city'=>$record['city'],
						'city_name'=>$record['city_name'],
						'file1countdoc'=>$record['file1countdoc'],
						'file2countdoc'=>$record['file2countdoc'],
						'file3countdoc'=>$record['file3countdoc'],
						'file4countdoc'=>$record['file4countdoc'],
						'status'=>(empty($record['mail_dt']) ? Yii::t('trans','Not Sent') : Yii::t('trans','Sent')),
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe07'] = $this->getCriteria();
		return true;
	}

}
