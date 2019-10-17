<?php

class ApprReqList extends CListPageModel
{
	public $type;
	public $showtaxonly = false;
	
	public function rules() {
		$rtn = parent::rules();
		$rtn[] = array('type, showtaxonly','safe');
		$rtn[] = array('attr','validateForBatchSign');
		return $rtn;
	}
	
	public function getCriteria() {
		$rtn = parent::getCriteria();
		$rtn['type'] = $this->type;
		$rtn['showtaxonly'] = $this->showtaxonly;
		return $rtn;
	}
	
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
			'payreqcountdoc'=>Yii::t('misc','Attachment'),
			'taxcountdoc'=>Yii::t('trans','Tax Slip'),
			'acct_type_desc'=>Yii::t('trans','Paid Account'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1, $type='P', $showtax=false)
	{
		$this->type = $type;
		$this->showtaxonly = $showtax;
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = ($this->type=='P')
				? $wf->getPendingRequestIdList('PAYMENT', 'PA', Yii::app()->user->id)
				: $wf->getPendingStandbyRequestIdList('PAYMENT', 'PA', Yii::app()->user->id);
		if (empty($list)) $list = '0';
		
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.id, a.req_dt, e.trans_type_desc, a.item_desc, a.payee_name, c.disp_name as user_name,
					b.name as city_name, a.amount, a.status, f.field_value as ref_no,g.field_value as int_fee,
					h.field_value as item_code, k.acct_type_desc,
					docman$suffix.countdoc('payreq',a.id) as payreqcountdoc,
					docman$suffix.countdoc('tax',a.id) as taxcountdoc
				from acc_request a inner join security$suffix.sec_city b on a.city=b.code
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code 
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
					inner join acc_trans_type e on a.trans_type_code=e.trans_type_code 
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
		if ($this->showtaxonly) {
			$sql1 .= " and docman$suffix.countdoc('tax',a.id) > 0 ";
			$sql2 .= " and docman$suffix.countdoc('tax',a.id) > 0 ";
		}
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
					'type'=>$type,
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
					'select'=>'N',
					'pitem_desc'=>(isset($acctitemlist[$record['item_code']]) ? $acctitemlist[$record['item_code']] : ''),
					'payreqcountdoc'=>$record['payreqcountdoc'],
					'taxcountdoc'=>$record['taxcountdoc'],
					'acct_type_desc'=>$record['acct_type_desc'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xa05'] = $this->getCriteria();
		return true;
	}

	public function validateForBatchSign($attribute, $params) {
		foreach ($this->attr as $record) {
			if (isset($record['select']) && $record['select']=='Y') {
				$count = $record['taxcountdoc'];
				if (empty($count) || $count==0) {
					$refno = $record['ref_no'];
					$this->addError($attribute, Yii::t('trans','No Tax Slip').' ('.Yii::t('trans','Ref. No.').': '.$refno.')');
				}
			}
		}
	}

	public function batchApprove()
	{
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			foreach ($this->attr as $record) {
				if (isset($record['select']) && $record['select']=='Y') {
					if ($wf->startProcess('PAYMENT',$record['id'],$record['req_dt'])) {
						$wf->saveRequestData('APPROVER',Yii::app()->user->id);
						$wf->takeAction('APPROVE');
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

	public function batchSign() {
		$wf = new WorkflowPayment;
		$connection = $wf->openConnection();
		try {
			foreach ($this->attr as $record) {
				if (isset($record['select']) && $record['select']=='Y') {
					$this->saveInfo($connection,$record);
					if ($wf->startProcess('PAYMENT',$record['id'],$record['req_dt'])) {
						$wf->takeAction('APPRNSIGN');
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

	protected function saveInfo(&$connection,$data) {
		$sql = "insert into acc_request_info(
					req_id, field_id, field_value, luu, lcu) values (
					:id, 'trans_dt', :field_value, :luu, :lcu)
					on duplicate key update
					field_value = :field_value
				";

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$data['id'],PDO::PARAM_INT);
		if (strpos($sql,':field_value')!==false) {
			$value = General::toMyDate($data['req_dt']);
			$command->bindParam(':field_value',$value,PDO::PARAM_STR);
		}
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		$command->execute();

		return true;
	}
}
