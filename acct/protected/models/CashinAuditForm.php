<?php

class CashinAuditForm extends CListPageModel
{
	public $hdr_id = 0;
	public $acct_id;
	public $audit_dt;
	public $req_user;
	public $req_user_name;
	public $balance;
	public $audit_user;
	public $audit_user_name;
	public $audit_user_pwd;
	public $city;
	public $city_name;
	public $id_list;
	public $int_fee;
	public $rec_amt;
	
	public function rules()	{
		$rtn1 = parent::rules();
		$rtn2 = array(
			array('audit_dt, req_user', 'required'),
			array('audit_user', 'safe'),
			array('audit_user','validateAuditRight'),
			array('audit_user_pwd','validatePassword'),
			array('acct_id, city, city_name, balance, hdr_id, id_list, req_user_name, int_fee','safe'),
		);
		return array_merge($rtn1, $rtn2);
	}

	public function init() {
		$this->audit_dt = date("Y/m/d");
		$this->acct_id = 2;
		$this->noOfItem = 0;
		$this->rec_amt = 0;
		$this->city = Yii::app()->user->city();
		parent::init();
	}
	
	public function attributeLabels() {
		return array(	
			'audit_dt'=>Yii::t('trans','Check Date'),
			'city'=>Yii::t('misc','City'),
			'acct_name'=>Yii::t('trans','Account Name'),
			'req_user_name'=>Yii::t('trans','Cashier'),
			'audit_user_name'=>Yii::t('trans','A/C Staff'),
			'acct_no'=>Yii::t('trans','Account No.'),
			'balance'=>Yii::t('trans','Curr. Balance'),
			'trans_dt'=>Yii::t('trans','Trans. Date'),
			'trans_type_desc'=>Yii::t('trans','Trans. Type'),
			'amount_in'=>Yii::t('trans','Amount(In)'),
			'amount_out'=>Yii::t('trans','Amount(Out)'),
			'cheque_no'=>Yii::t('trans','Cheque No.'),
			'invoice_no'=>Yii::t('trans','China Invoice No.'),
			'trans_desc'=>Yii::t('trans','Remarks'),
			'audit_user'=>Yii::t('user','User ID'),
			'audit_user_pwd'=>Yii::t('user','Password'),
			'pay_subject'=>Yii::t('trans','Payer').'/'.Yii::t('trans','Payee'),
			'int_fee'=>Yii::t('trans','Integrated Fee'),
			'rec_amt'=>Yii::t('trans','Rec. Amount'),
		);
	}
	
	public function validateAuditRight($attribute, $params) {
		if ($this->scenario=='confirm') {
			$user=User::model()->find('LOWER(username)=?',array($this->audit_user));
			$flag = empty($user) ;
			if (!$flag) {
				$access = $user->accessRights();
				$sid = Yii::app()->user->system();
				$flag = (strpos($access['control'][$sid],'CN01')===false);
			}
			if ($flag) {
				$this->addError($attribute, Yii::t('trans','Access denied'));
			}
		}
	}

	public function validatePassword($attribute, $params) {
		if ($this->scenario=='confirm') {
			$code = General::authenticate($this->audit_user,$this->audit_user_pwd);
			switch ($code) {
				case UserIdentity::ERROR_PASSWORD_INVALID:
				case UserIdentity::ERROR_USERNAME_INVALID:
					$this->addError($attribute, Yii::t('dialog','Incorrect username or password.'));
					break;
				default:
			}
		}
	}
	
	public function newData() {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$date = General::toMyDate($this->audit_dt);
		$city = Yii::app()->user->city();

//		$acctId = $this->acct_id;
		$sql = "select acct_id from acc_trans_type_def where trans_type_code='CASHIN' and city='$city'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$acctId = ($row===false) ? $this->acct_id : $row['acct_id'];

		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=e.city ' : '');
		$sql = "select AccountBalance(a.id,'$city','2010-01-01','$date') as balance, 
				a.id, a.acct_no, a.acct_name, a.bank_name, b.name as city_name,
				c.disp_name as req_user_name
				from acc_account a
				inner join security$suffix.sec_city b on b.code='$city' 
				inner join security$suffix.sec_user c on c.username='$user'
				where a.id=$acctId
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			//$this->hdr_id = 0;
			$this->acct_id = $row['id'];
			$this->balance = $row['balance'];
			if ($this->hdr_id==0) {
				$this->req_user = $user;
				$this->req_user_name = $row['req_user_name'];
				$this->city = $city;
				$this->city_name = $row['city_name'];
			}
			$this->audit_user = '';
			$this->audit_user_name = '';
			$this->balance = $row['balance'];
		}

		$sql = "select a.id, a.trans_dt, e.trans_type_desc, a.status, b.field_value as pay_subject, 
				c.field_value as cheque_no, d.field_value as invoice_no, e.trans_cat, a.trans_desc, g.field_value as int_fee, 
				if(e.trans_cat='IN',a.amount,null) as amount_in,
				if(e.trans_cat='IN',null,a.amount) as amount_out,
				docman$suffix.countdoc('TRANS',a.id) as no_of_attm,
				e.adj_type
				from acc_trans a
				left outer join acc_trans_audit_dtl x on a.id=x.trans_id 
				inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr
				left outer join acc_trans_info b on a.id=b.trans_id and b.field_id='payer_name'
				left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='cheque_no'
				left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='invoice_no'
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where x.trans_id is null and a.acct_id=$acctId and a.city='$city' and a.status<>'V'
			";
		$list = array();
		$this->attr = array();
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			$this->totalRow = count($records);
			$this->id_list = '';
			foreach ($records as $k=>$record) {
				$amti = empty($record['amount_in']) ? 0 : $record['amount_in'];
				$amti = $record['adj_type']=='Y' ? $amti*-1 : $amti;
				$amto = empty($record['amount_out']) ? 0 : $record['amount_out'];
				$amto = $record['adj_type']=='Y' ? $amto*-1 : $amto;
				$this->attr[] = array(
					'id'=>$record['id'],
					'trans_dt'=>General::toDate($record['trans_dt']),
					'trans_type_desc'=>$record['trans_type_desc'],
					'amount_in'=>$amti,
					'amount_out'=>$amto,
					'cheque_no'=>$record['cheque_no'],
					'invoice_no'=>$record['invoice_no'],
					'pay_subject'=>$record['pay_subject'],
					'trans_desc'=>$record['trans_desc'],
					'status'=>General::getTransStatusDesc($record['status']),
					'no_of_attm'=>$record['no_of_attm'],
					'trans_cat'=>$record['trans_cat'],
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
				);
				$this->rec_amt += $amti; //- $amto;
				$this->id_list .= ($this->id_list=='') ? $record['id'] : ','.$record['id'];
			}
		}
		return ($row!==false);
	}
	
	public function retrieveData($index) {
		return ($this->retrieveHeaderData($index) && $this->retrieveDataByPage());
	}
	
	public function retrieveHeaderData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$date = General::toMyDate($this->audit_dt);
//		$acctId = $this->acct_id;

		$city = Yii::app()->user->city();
		$sql = "select acct_id from acc_trans_type_def where trans_type_code='CASHIN' and city='$city'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$acctId = ($row===false) ? $this->acct_id : $row['acct_id'];

		$sql = "select x.id, y.disp_name as req_user_name, z.disp_name as audit_user_name, x.balance, 
				x.acct_id, a.acct_no, a.acct_name, a.bank_name, b.name as city_name, x.city, x.audit_dt,
				x.req_user, x.audit_user
				from acc_trans_audit_hdr x 
				inner join acc_account a on x.acct_id=a.id 
				inner join security$suffix.sec_city b on b.code=x.city 
				inner join acc_account_type c on a.acct_type_id=c.id
				left outer join security$suffix.sec_user y on x.req_user=y.username
				left outer join security$suffix.sec_user z on x.audit_user=z.username
				where (a.city in ($citylist) or a.city = '99999')
				and x.id=$index
			";
		// 由于不知道acct_id有什么用，不做限制（2024/01/03）
		// and x.id=$index and x.acct_id=$acctId
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->hdr_id = $row['id'];
			$this->audit_dt = General::toDate($row['audit_dt']);
			$this->acct_id = $row['acct_id'];
//			$this->acct_no = $row['acct_no'];
//			$this->acct_name = $row['acct_name'];
//			$this->bank_name = $row['bank_name'];
			$this->city_name = $row['city_name'];
			$this->balance = $row['balance'];
			$this->req_user = $row['req_user'];
			$this->req_user_name = $row['req_user_name'];
			$this->audit_user = $row['audit_user'];
			$this->audit_user_name = $row['audit_user_name'];
			$this->balance = $row['balance'];
			$this->city = $row['city'];
		}
		return ($row!==false);
	}
	
	public function retrieveDataByPage($pageNum=1) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$city = $this->city;
		$version = Yii::app()->params['version'];
		$citystr = ($version=='intl' ? ' and a.city=e.city ' : '');

		$hdrId = $this->hdr_id;
		$sql1 = "select a.id, a.trans_dt, e.trans_type_desc, a.status, b.field_value as pay_subject, 
				c.field_value as cheque_no, d.field_value as invoice_no, e.trans_cat, a.trans_desc,
				g.field_value as int_fee,
				if(e.trans_cat='IN',a.amount,null) as amount_in,
				if(e.trans_cat='IN',null,a.amount) as amount_out,
				docman$suffix.countdoc('TRANS',a.id) as no_of_attm,
				e.adj_type
				from acc_trans_audit_dtl x
				inner join acc_trans a on x.trans_id = a.id
				inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr 
				left outer join acc_trans_info b on a.id=b.trans_id and b.field_id='payer_name'
				left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='cheque_no'
				left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='invoice_no'
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where x.hdr_id = $hdrId
			";
		$sql2 = "select count(x.trans_id)
				from acc_trans_audit_dtl x
				inner join acc_trans a on x.trans_id = a.id
				inner join acc_trans_type e on a.trans_type_code=e.trans_type_code $citystr 
				left outer join acc_trans_info b on a.id=b.trans_id and b.field_id='payer_name'
				left outer join acc_trans_info c on a.id=c.trans_id and c.field_id='cheque_no'
				left outer join acc_trans_info d on a.id=d.trans_id and d.field_id='invoice_no'
				left outer join acc_trans_info g on a.id=g.trans_id and g.field_id='int_fee'
				where x.hdr_id = $hdrId
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'trans_type_desc':
					$clause .= General::getSqlConditionClause('e.trans_type_desc',$svalue);
					break;
				case 'trans_desc':
					$clause .= General::getSqlConditionClause('a.trans_desc',$svalue);
					break;
				case 'pay_subject':
					$clause .= General::getSqlConditionClause('b.field_value',$svalue);
					break;
				case 'cheque_no':
					$clause .= General::getSqlConditionClause('c.field_value',$svalue);
					break;
				case 'invoice_no':
					$clause .= General::getSqlConditionClause('d.field_value',$svalue);
					break;
				case 'int_fee':
					$field = "(select case g.field_value when 'Y' then '".Yii::t('misc','Yes')."' 
							else '".Yii::t('misc','No')."' 
						end) ";
					$clause .= General::getSqlConditionClause($field,$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'trans_dt': $orderf = 'a.trans_dt'; break;
				case 'trans_type_desc': $orderf = 'e.trans_type_desc'; break;
				case 'pay_subject': $orderf = 'b.field_value'; break;
				case 'cheque_no': $orderf = 'c.field_value'; break;
				case 'invoice_no': $orderf = 'd.field_value'; break;
				case 'int_fee': $orderf = 'g.field_value'; break;
				default: $orderf = $this->orderField; break;
			}
			$order .= " order by ".$orderf." ";
			if ($this->orderType=='D') $order .= "desc ";
		}
		if ($order=="") $order = "order by a.trans_dt desc, a.id desc";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
//		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		$this->id_list = '';
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$amti = empty($record['amount_in']) ? 0 : $record['amount_in'];
				$amti = $record['adj_type']=='Y' ? $amti*-1 : $amti;
				$amto = empty($record['amount_out']) ? 0 : $record['amount_out'];
				$amto = $record['adj_type']=='Y' ? $amto*-1 : $amto;
				$this->attr[] = array(
					'id'=>$record['id'],
					'trans_dt'=>General::toDate($record['trans_dt']),
					'trans_type_desc'=>$record['trans_type_desc'],
					'amount_in'=>$amti,
					'amount_out'=>$amto,
					'cheque_no'=>$record['cheque_no'],
					'invoice_no'=>$record['invoice_no'],
					'pay_subject'=>$record['pay_subject'],
					'trans_desc'=>$record['trans_desc'],
					'status'=>General::getTransStatusDesc($record['status']),
					'no_of_attm'=>$record['no_of_attm'],
					'trans_cat'=>$record['trans_cat'],
					'int_fee'=>($record['int_fee']=='Y' ? Yii::t('misc','Yes') : Yii::t('misc','No')),
				);
				$this->rec_amt += $amti; //- $amto;
				$this->id_list .= ($this->id_list=='') ? $record['id'] : ','.$record['id'];
			}
		}
		$session = Yii::app()->session;
		$session['criteria_xe04_2'] = $this->getCriteria();
		return true;
	}

	public function confirm() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	public function save() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveHeader(&$connection) {
		$sql = $this->hdr_id==0 
			? "insert into acc_trans_audit_hdr(
					audit_dt, acct_id, balance, req_user, audit_user, city, lcu, luu
				) values (
					:audit_dt, :acct_id, :balance, :req_user, :audit_user, :city, :lcu, :luu
				)"
			: "update acc_trans_audit_hdr set 
					audit_dt = :audit_dt, acct_id = :acct_id, balance = :balance, req_user = :req_user, audit_user = :audit_user, luu = :luu
				where id = :id
				";
		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->hdr_id,PDO::PARAM_INT);
		if (strpos($sql,':audit_dt')!==false)
			$command->bindParam(':audit_dt',$this->audit_dt,PDO::PARAM_STR);
		if (strpos($sql,':acct_id')!==false)
			$command->bindParam(':acct_id',$this->acct_id,PDO::PARAM_INT);
		if (strpos($sql,':balance')!==false) {
			$amt = $this->balance==0 ? '0.00' : General::toMyNumber($this->balance);
			$command->bindParam(':balance',$amt,PDO::PARAM_STR);
		}
		if (strpos($sql,':req_user')!==false)
			$command->bindParam(':req_user',$this->req_user,PDO::PARAM_STR);
		if (strpos($sql,':audit_user')!==false)
			$command->bindParam(':audit_user',$this->audit_user,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->hdr_id==0)
			$this->hdr_id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveDetail(&$connection) {
		$hdrId = $this->hdr_id;
		$idList = $this->id_list;
		$uid = Yii::app()->user->id;
		if (!empty($idList)) {
			$sql = "delete from acc_trans_audit_dtl where hdr_id=$hdrId";
			$connection->createCommand($sql)->execute();

			$sql = "insert into acc_trans_audit_dtl(
						hdr_id, trans_id, lcu, luu
					)  select $hdrId, id, '$uid', '$uid' from acc_trans where id in ($idList)
					";
			$connection->createCommand($sql)->execute();
		}
		return true;
	}

	public function isReadOnly() {
		return ($this->scenario=='view') || !empty($this->audit_user);
	}
}
