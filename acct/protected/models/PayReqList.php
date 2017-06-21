<?php

class PayReqList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'ref_no'=>Yii::t('trans','Ref. No.'),
			'req_dt'=>Yii::t('trans','Req. Date'),
			'trans_type_desc'=>Yii::t('trans','Trans. Type'),
			'payee_name'=>Yii::t('trans','Payee'),
			'amount'=>Yii::t('trans','Amount'),
			'item_desc'=>Yii::t('trans','Details'),
			'city_name'=>Yii::t('misc','City'),
			'status'=>Yii::t('trans','Status'),
			'wfstatusdesc'=>Yii::t('trans','Flow Status'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql1 = "select a.id, a.req_dt, e.trans_type_desc, a.item_desc, a.payee_name,
					b.name as city_name, a.amount, a.status, f.field_value as ref_no, a.req_user, 
					(select case workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)
							when '' then '0DF' 
							when 'PC' then '1PC' 
							when 'PA' then '2PA' 
							when 'PR' then '3PR' 
							when 'PS' then '4PS' 
							when 'ED' then '5ED' 
					end) as wfstatus,
					workflow$suffix.RequestStatusDesc('PAYMENT',a.id,a.req_dt) as wfstatusdesc
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code 
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='REF_NO'
				where a.city in ($city) 
				and e.trans_cat='OUT' 
			";
		$sql2 = "select count(a.id)
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code 
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='REF_NO'
				where ((a.city in ($city) and workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>'') or a.req_user='$user')
				and e.trans_cat='OUT' 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'trans_type_desc':
					$clause .= General::getSqlConditionClause('e.trans_type_desc',$svalue);
					break;
				case 'item_desc':
					$clause .= General::getSqlConditionClause('a.item_desc',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
				case 'payee_name':
					$clause .= General::getSqlConditionClause('a.payee_name',$svalue);
					break;
				case 'ref_no':
					$clause .= General::getSqlConditionClause('f.field_value',$svalue);
					break;
				case 'wfstatusdesc':
					$clause .= General::getSqlConditionClause("workflow$suffix.RequestStatusDesc('PAYMENT',a.id,a.req_dt)",$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'req_dt': $orderf = 'a.req_dt'; break;
				case 'trans_type_desc': $orderf = 'e.trans_type_desc'; break;
				case 'payee_name': $orderf = 'a.payee_name'; break;
				case 'item_desc': $orderf = 'a.item_desc'; break;
				case 'ref_no': $orderf = 'f.field_value'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by wfstatus, req_dt desc";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$wfstatus = (empty($record['wfstatus'])?'0DF':$record['wfstatus']);
				if (($wfstatus=='0DF' && $record['req_user']==$user) || $wfstatus!='0DF') {
					$this->attr[] = array(
						'id'=>$record['id'],
						'req_dt'=>General::toDate($record['req_dt']),
						'trans_type_desc'=>$record['trans_type_desc'],
						'payee_name'=>$record['payee_name'],
						'amount'=>$record['amount'],
						'item_desc'=>str_replace("\n","<br>",$record['item_desc']),
						'city_name'=>$record['city_name'],
						'status'=>($record['status']=='A'?'':General::getTransStatusDesc($record['status'])),
						'wfstatusdesc'=>(empty($record['wfstatusdesc'])?Yii::t('misc','Draft'):$record['wfstatusdesc']) ,
						'ref_no'=>$record['ref_no'],
						'wfstatus'=> $wfstatus,
						'req_user'=>$record['req_user'],
					);
				}
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xa04'] = $this->getCriteria();
		return true;
	}

}
