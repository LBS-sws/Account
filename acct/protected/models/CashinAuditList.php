<?php

class CashinAuditList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(	
			'audit_dt'=>Yii::t('trans','Check Date'),
			'acct_id'=>Yii::t('trans','Account'),
			'balance'=>Yii::t('trans','Curr. Balance'),
			'req_user_name'=>Yii::t('trans','Cashier'),
			'audit_user_name'=>Yii::t('trans','A/C Staff'),
			'city_name'=>Yii::t('misc','City'),
			'rec_amt'=>Yii::t('trans','Rec. Amount'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and w.city=x.city ' : '');
		$sql1 = "select a.id, a.audit_dt, a.balance, b.disp_name as req_user_name, c.disp_name as audit_user_name,
					d.name as city_name, a.city,
					(SELECT sum(if(x.adj_type='N',w.amount,-1*w.amount))
					FROM acc_trans w, acc_trans_type x, acc_trans_audit_dtl y
					WHERE w.trans_type_code = x.trans_type_code and w.id = y.trans_id and y.hdr_id = a.id $citystr 
					and x.trans_cat = 'IN'
					) as rec_amt
				from acc_trans_audit_hdr a 
				left outer join security$suffix.sec_user b on a.req_user=b.username
				left outer join security$suffix.sec_user c on a.audit_user=c.username
				left outer join security$suffix.sec_city d on a.city=d.code
				where a.city in ($city)
			";
		$sql2 = "select count(a.id)
				from acc_trans_audit_hdr a 
				left outer join security$suffix.sec_user b on a.req_user=b.username
				left outer join security$suffix.sec_user c on a.audit_user=c.username
				left outer join security$suffix.sec_city d on a.city=d.code
				where a.city in ($city)
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'req_user_name':
					$clause .= General::getSqlConditionClause('b.disp_name',$svalue);
					break;
				case 'audit_user_name':
					$clause .= General::getSqlConditionClause('c.disp_name',$svalue);
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
				case 'balance': $orderf = 'a.balance'; break;
				case 'req_user_name': $orderf = 'b.disp_name'; break;
				case 'audit_user_name': $orderf = 'c.disp_name'; break;
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
					'audit_dt'=>General::toDate($record['audit_dt']),
					'req_user_name'=>$record['req_user_name'],
					'audit_user_name'=>$record['audit_user_name'],
					'balance'=>$record['balance'],
					'city_name'=>$record['city_name'],
					'city'=>$record['city'],
					'rec_amt'=>empty($record['rec_amt']) ? '0.00' : $record['rec_amt'],
				);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe04'] = $this->getCriteria();
		return true;
	}

}
