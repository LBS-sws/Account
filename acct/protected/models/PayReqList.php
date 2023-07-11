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
			'int_fee'=>Yii::t('trans','Integrated Fee'),
			'acct_type_desc'=>Yii::t('trans','Paid Account'),
            'payreqcountdoc'=>Yii::t('misc','Attachment'),
            'taxcountdoc'=>Yii::t('trans','Tax Slip'),
		);
	}
	
	public function searchColumns() {
		$suffix = Yii::app()->params['envSuffix'];
		$search = array(
			'req_dt'=>"date_format(a.req_dt,'%Y/%m/%d')",
			'trans_type_desc'=>'e.trans_type_desc',
			'acct_type_desc'=>'j.acct_type_desc',
			'payee_name'=>'a.payee_name',
			'item_desc'=>'a.item_desc',
			'ref_no'=>'f.field_value',
			'int_fee'=>"(select case g.field_value when 'Y' then '".Yii::t('misc','Yes')."' 
							else '".Yii::t('misc','No')."' 
						end) ",
			'amount'=>'a.amount',
			'wfstatusdesc'=>"workflow$suffix.RequestStatusDesc('PAYMENT',a.id,a.req_dt)",
		);
		if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
		return $search;
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=e.city ' : '');
		$city = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql1 = "select a.id, a.req_dt, e.trans_type_desc, a.item_desc, a.payee_name,
					b.name as city_name, a.amount, a.status, f.field_value as ref_no, a.req_user, 
					g.field_value as int_fee, j.acct_type_desc, k.field_value as trans_id,
					(select case workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)
							when '' then '0DF' 
							when 'PC' then '1PC' 
							when 'PB' then '2PB' 
							when 'PA' then '3PA' 
							when 'QR' then '4QR' 
							when 'PR' then '5PR' 
							when 'PS' then '6PS' 
							when 'ED' then '7ED' 
					end) as wfstatus,
					workflow$suffix.RequestStatusDesc('PAYMENT',a.id,a.req_dt) as wfstatusdesc,
					docman$suffix.countdoc('payreq',a.id) as payreqcountdoc,
					docman$suffix.countdoc('tax',a.id) as taxcountdoc
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='ref_no'
					left outer join acc_request_info g on a.id=g.req_id and g.field_id='int_fee'
					left outer join acc_request_info h on a.id=h.req_id and h.field_id='acct_id'
					left outer join acc_account i on i.id=h.field_value
					left outer join acc_account_type j on j.id=i.acct_type_id
					left outer join acc_request_info k on a.id=k.req_id and k.field_id='trans_id'
				where ((a.city in ($city) and workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>'') or a.req_user='$user')
				and e.trans_cat='OUT' 
			";
		$sql2 = "select count(a.id)
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr 
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='ref_no'
					left outer join acc_request_info g on a.id=g.req_id and g.field_id='int_fee'
					left outer join acc_request_info h on a.id=h.req_id and h.field_id='acct_id'
					left outer join acc_account i on i.id=h.field_value
					left outer join acc_account_type j on j.id=i.acct_type_id
					left outer join acc_request_info k on a.id=k.req_id and k.field_id='trans_id'
				where ((a.city in ($city) and workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>'') or a.req_user='$user')
				and e.trans_cat='OUT' 
			";
		$clause = "";
		if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$columns = $this->searchColumns();
				$clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
			}
		}
		$clause .= $this->getDateRangeCondition('a.req_dt');
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'city_name': $orderf = 'b.name'; break;
				case 'req_dt': $orderf = 'a.req_dt'; break;
				case 'trans_type_desc': $orderf = 'e.trans_type_desc'; break;
				case 'payee_name': $orderf = 'a.payee_name'; break;
				case 'item_desc': $orderf = 'a.item_desc'; break;
				case 'ref_no': $orderf = 'f.field_value'; break;
				case 'int_fee': $orderf = 'g.field_value'; break;
				case 'acct_type_desc': $orderf = 'j.acct_type_desc'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by wfstatus, req_dt desc,a.id desc";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
//		echo $sql;
//		Yii::app()->end();
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
						'statusx'=>$record['status'],
						'wfstatusdesc'=>(empty($record['wfstatusdesc'])?Yii::t('misc','Draft'):$record['wfstatusdesc']) ,
						'ref_no'=>$record['ref_no'],
						'wfstatus'=> $wfstatus,
						'req_user'=>$record['req_user'],
						'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
						'acct_type_desc'=>$record['acct_type_desc'],
						'trans_id'=>$record['trans_id'],
                        'payreqcountdoc'=>$record['payreqcountdoc'],
                        'taxcountdoc'=>$record['taxcountdoc'],
					);
				}
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

}
