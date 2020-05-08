<?php

class T3AuditList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'audit_year'=>Yii::t('trans','Year'),
			'audit_month'=>Yii::t('trans','Month'),
			'req_user_name'=>Yii::t('trans','Cashier'),
			'audit_user_name'=>Yii::t('trans','A/C Staff'),
			'city_name'=>Yii::t('misc','City'),
			'req_dt'=>Yii::t('trans','Req. Date'),
			'audit_dt'=>Yii::t('trans','Check Date'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.lcd, a.audit_year, a.audit_month, b.disp_name as req_user_name, c.disp_name as audit_user_name,
					a.req_dt, a.audit_dt, 
					d.name as city_name, a.city, a.bal_diff
				from acc_t3_audit_hdr a 
				left outer join security$suffix.sec_user b on a.req_user=b.username
				left outer join security$suffix.sec_user c on a.audit_user=c.username
				left outer join security$suffix.sec_city d on a.city=d.code
				where a.city in ($city)
			";
		$sql2 = "select count(a.id)
				from acc_t3_audit_hdr a 
				left outer join security$suffix.sec_user b on a.req_user=b.username
				left outer join security$suffix.sec_user c on a.audit_user=c.username
				left outer join security$suffix.sec_city d on a.city=d.code
				where a.city in ($city)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'audit_year':
					$clause .= General::getSqlConditionClause('a.audit_year',$svalue);
					break;
				case 'audit_month':
					$clause .= General::getSqlConditionClause('a.audit_month',$svalue);
					break;
				case 'req_dt':
					$clause .= General::getSqlConditionClause('a.req_dt',$svalue);
					break;
				case 'audit_dt':
					$clause .= General::getSqlConditionClause('a.audit_dt',$svalue);
					break;
				case 'audit_user_name':
					$clause .= General::getSqlConditionClause('c.disp_name',$svalue);
					break;
				case 'req_user_name':
					$clause .= General::getSqlConditionClause('b.disp_name',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('d.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'd.name'; break;
				case 'audit_dt': $orderf = 'a.audit_dt'; break;
				case 'req_dt': $orderf = 'a.req_dt'; break;
				case 'audit_user_name': $orderf = 'c.disp_name'; break;
				case 'req_user_name': $orderf = 'b.disp_name'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by a.id desc";

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
					'audit_year'=>$record['audit_year'],
					'audit_month'=>$record['audit_month'],
					'req_dt'=>General::toDate($record['req_dt']),
					'audit_dt'=>General::toDate($record['audit_dt']),
					'req_user_name'=>$record['req_user_name'],
					'audit_user_name'=>$record['audit_user_name'],
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'bal_diff'=>$record['bal_diff'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe05'] = $this->getCriteria();
		return true;
	}

	public function hasLatestRecord() {
		$end_dt = strtotime("last day of previous month");
		$year = date('Y',$end_dt);
		$month = date('m',$end_dt);
		$city = Yii::app()->user->city();
		$sql = "select id from acc_t3_audit_hdr where city='$city' and audit_year=$year and audit_month=$month";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row!==false);
	}
}
