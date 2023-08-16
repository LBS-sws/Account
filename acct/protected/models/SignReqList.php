<?php

class SignReqList extends CListPageModel
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
			'user_name'=>Yii::t('trans','Requestor'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),
			'pitem_desc'=>Yii::t('trans','Paid Item'),
			'payreqcountdoc'=>Yii::t('trans','Req. Attachment'),
			'payrealcountdoc'=>Yii::t('misc','Attachment'),
			'taxcountdoc'=>Yii::t('trans','Tax Slip'),
			'acct_type_desc'=>Yii::t('trans','Paid Account'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PS', Yii::app()->user->id);
		if (empty($list)) $list = '0';
		
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=e.city ' : '');
		$sql1 = "select a.id, a.req_dt, e.trans_type_desc, a.item_desc, a.payee_name, c.disp_name as user_name,
					b.name as city_name, a.amount, a.status, f.field_value as ref_no, g.field_value as int_fee, 
					h.field_value as item_code, k.acct_type_desc,
					a.doc_count_real as payrealcountdoc,
					a.doc_count_req as payreqcountdoc,
					a.doc_count_tax as taxcountdoc
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr 
					inner join security$suffix.sec_user c on a.req_user = c.username
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='ref_no'
					left outer join acc_request_info g on a.id=g.req_id and g.field_id='int_fee'
					left outer join acc_request_info h on a.id=h.req_id and h.field_id='item_code'
					left outer join acc_request_info i on a.id=i.req_id and i.field_id='acct_id'
					left outer join acc_account j on j.id=i.field_value
					left outer join acc_account_type k on k.id=j.acct_type_id
				where a.city in ($city)
				and a.id in ($list)
				and e.trans_cat='OUT' 
			";
		$sql2 = "select count(a.id)
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr 
					inner join security$suffix.sec_user c on a.req_user = c.username
					left outer join acc_request_info f on a.id=f.req_id and f.field_id='ref_no'
					left outer join acc_request_info g on a.id=g.req_id and g.field_id='int_fee'
					left outer join acc_request_info h on a.id=h.req_id and h.field_id='item_code'
					left outer join acc_request_info i on a.id=i.req_id and i.field_id='acct_id'
					left outer join acc_account j on j.id=i.field_value
					left outer join acc_account_type k on k.id=j.acct_type_id
				where a.city in ($city)
				and a.id in ($list)
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
				case 'int_fee':
					$field = "(select case g.field_value when 'Y' then '".Yii::t('misc','Yes')."' 
							else '".Yii::t('misc','No')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
				case 'acct_type_desc': 
					$orderf = 'k.acct_type_desc'; 
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
				case 'int_fee': $orderf = 'g.field_value'; break;
				case 'acct_type_desc': $orderf = 'k.acct_type_desc'; break;
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
			$acctitemlist = General::getAcctItemList();
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
					'id'=>$record['id'],
					'req_dt'=>General::toDate($record['req_dt']),
					'trans_type_desc'=>$record['trans_type_desc'],
					'payee_name'=>$record['payee_name'],
					'amount'=>$record['amount'],
					'item_desc'=>str_replace("\n","<br>",$record['item_desc']),
					'city_name'=>$record['city_name'],
					'status'=>($record['status']=='A'?'':General::getTransStatusDesc($record['status'])),
					'user_name'=>$record['user_name'],
					'ref_no'=>$record['ref_no'],
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
					'select'=>'N',
					'pitem_desc'=>(isset($acctitemlist[$record['item_code']]) ? $acctitemlist[$record['item_code']] : ''),
					'payrealcountdoc'=>$record['payrealcountdoc'],
					'payreqcountdoc'=>$record['payreqcountdoc'],
					'taxcountdoc'=>$record['taxcountdoc'],
					'acct_type_desc'=>$record['acct_type_desc'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xa07'] = $this->getCriteria();
		return true;
	}

	public function batchSign() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			foreach ($this->attr as $record) {
				if (isset($record['select']) && $record['select']=='Y') {
					if ($wf->startProcess('PAYMENT',$record['id'],$record['req_dt'])) {
						$wf->takeAction('REIMAPPR');
					}
				}
			}
			$wf->transaction->commit();
		}
		catch(Exception $e) {
			$wf->transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
}
