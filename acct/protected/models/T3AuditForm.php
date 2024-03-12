<?php

class T3AuditForm extends CFormModel 
{
	public $id;
	public $req_dt;
	public $audit_year;
	public $audit_month;
	public $audit_dt;
	public $req_user;
	public $req_user_name;
	public $audit_user;
	public $audit_user_name;
	public $audit_user_pwd;
	public $city;
	public $city_name;
	public $remarks;
	public $bal_diff;
	public $record = array();

	public $files;

	public $docMasterId = array(
							't3bal'=>0,
							't3cash'=>0,
						);
	public $removeFileId = array(
							't3bal'=>0,
							't3cash'=>0,
						);
	public $no_of_attm = array(
							't3bal'=>0,
							't3cash'=>0,
						);
	
	public function rules()	{
		return array(
			array('req_user', 'required'),
			array('audit_dt, audit_user', 'safe'),
			array('audit_user','validateAuditRight'),
			array('audit_user_pwd','validatePassword'),
			array('record','validateRecord'),
			array('id, city, city_name, req_user_name, audit_user_name, audit_year, audit_month, remarks, bal_diff','safe'),
			array('files, removeFileId, docMasterId, no_of_attm','safe'), 
			array ('no_of_attm','validateAttachment'),
		);
	}

	public function init() {
		$this->req_dt = date("Y/m/d");
		$this->bal_diff = 'N';
		$this->city = Yii::app()->user->city();
	}
	
	public function attributeLabels() {
		return array(	
			'audit_year'=>Yii::t('trans','Year'),
			'audit_month'=>Yii::t('trans','Month'),
			'audit_dt'=>Yii::t('trans','Check Date'),
			'req_dt'=>Yii::t('trans','Req. Date'),
			'city'=>Yii::t('misc','City'),
			'acct_name'=>Yii::t('trans','Account Name'),
			'req_user_name'=>Yii::t('trans','Cashier'),
			'audit_user'=>Yii::t('user','User ID'),
			'audit_user_pwd'=>Yii::t('user','Password'),
			'audit_user_name'=>Yii::t('trans','A/C Staff'),
			'acct_no'=>Yii::t('trans','Account No.'),
			'bal_month_end'=>Yii::t('trans','Balance'),
			'bal_t3'=>Yii::t('trans','T3 Balance'),
			'remarks'=>Yii::t('trans','Remarks'),
			'adjtype'=>Yii::t('trans','Type'),
			'amount'=>Yii::t('trans','Amount'),
			'bal_adj'=>Yii::t('trans','Balance After Adj.'),
			'bal_t3'=>Yii::t('trans','T3 Balance'),
			'bal_lbs'=>Yii::t('trans','LBS Balance'),
			'tot_tr_lnr'=>Yii::t('trans','T3 Rec. LBS Not'),
			'tot_tp_lnp'=>Yii::t('trans','T3 Paid LBS Not'),
			'tot_lr_tnr'=>Yii::t('trans','LBS Rec. T3 Not'),
			'tot_lp_tnp'=>Yii::t('trans','LBS Paid T3 Not'),
			'tot_adj_t'=>Yii::t('trans','T3 Adj. Balance'),
			'tot_adj_l'=>Yii::t('trans','LBS Adj. Balance'),
		);
	}
	
	public function validateAuditRight($attribute, $params) {
		if (!empty($this->audit_dt)) {
			$user=User::model()->find('LOWER(username)=?',array($this->audit_user));
			$flag = empty($user) ;
			if (!$flag) {
				$access = $user->accessRights();
				$sid = Yii::app()->user->system();
				$flag = (strpos($access['control'][$sid],'CN08')===false);
			}
			if ($flag) {
				$this->addError($attribute, Yii::t('trans','Access denied'));
			}
		}
	}

	public function validatePassword($attribute, $params) {
		if (!empty($this->audit_dt) && !empty($this->audit_user)) {
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
		$end_dt = strtotime("last day of previous month");
		
		$this->id = 0;
		$this->req_dt = date('Y/m/d');
		$this->audit_year = date('Y', $end_dt);
		$this->audit_month = date('m',$end_dt);
		$this->req_user = Yii::app()->user->id;
		$this->req_user_name = Yii::app()->user->user_display_name();
		$this->city = Yii::app()->user->city();
		$this->city_name = Yii::app()->user->city_name();

		$date = date('Y-m-d',$end_dt);
		$city = $this->city;
		$sql = "select AccountBalance(a.id,'$city','2010-01-01','$date') as balance, 
				a.id, a.acct_no, a.acct_name, a.bank_name, b.acct_type_desc 
				from acc_account a
				left outer join acc_account_type b on a.acct_type_id=b.id
				where a.city='$city' or a.city='99999'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $i=>$row) {
				$this->record[] = array(
									'acct_id'=>$row['id'],
									'acct_type_desc'=>$row['acct_type_desc'],
									'acct_name'=>$row['acct_name'],
									'bank_name'=>$row['bank_name'],
									'bal_month_end'=>$row['balance'],
									'bal_t3'=>0,
									'bal_lbs'=>$row['balance'],
									'bal_adj'=>0,
									'bal_adj_id'=>0,
									'tot_adj_t'=>0,
									'tot_tr_lnr'=>0,
									'tot_tp_lnp'=>0,
									'tot_adj_l'=>0,
									'tot_lr_tnr'=>0,
									'tot_lp_tnp'=>0,
									'lbsrecord'=>array(array('id'=>'0','adjtype'=>'','amount'=>'','remarks'=>'','uflag'=>'N')),
									't3record'=>array(array('id'=>'0','adjtype'=>'','amount'=>'','remarks'=>'','uflag'=>'N')),
								);
			}
		}
		return true;
	}
	
	protected function readHeader($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$citylist = Yii::app()->user->city_allow();
		
		$sql = "select a.*, b.disp_name as req_user_name, c.disp_name as audit_user_name, 
				docman$suffix.countdoc('t3bal',id) as t3balcountdoc,
				docman$suffix.countdoc('t3cash',id) as t3cashcountdoc
				from acc_t3_audit_hdr a
				left outer join security$suffix.sec_user b on a.req_user=b.username
				left outer join security$suffix.sec_user c on a.audit_user=c.username
				where a.id=$index and a.city in ($citylist)
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->req_dt = General::toMyDate($row['req_dt']);
			$this->audit_dt = General::toMyDate($row['audit_dt']);
			$this->audit_year = $row['audit_year'];
			$this->audit_month = $row['audit_month'];
			$this->req_user = $row['req_user'];
			$this->req_user_name = $row['req_user_name'];
			$this->audit_user = $row['audit_user'];
			$this->audit_user_name = $row['audit_user_name'];
			$this->city = Yii::app()->user->city();
			$this->city_name = Yii::app()->user->city_name();
			$this->remarks = $row['remarks'];
			$this->no_of_attm['t3bal'] = $row['t3balcountdoc'];
			$this->no_of_attm['t3cash'] = $row['t3cashcountdoc'];
		}
		return ($row!==false);
	}
	
	protected function readDetail() {
		$suffix = Yii::app()->params['envSuffix'];
		$idlist = '';
		$index = $this->id;
        $city = $this->city;
        $tdt = $this->audit_year.'/'.$this->audit_month.'/1 00:00:00';
        $date = date('Y/m/d', strtotime(date('Y-m-d H:i:s', strtotime($tdt.' +1 month')).' -1 day'));

        $sql = "select AccountBalance(b.id,'$city','2010-01-01','$date') as balance,a.*, b.bank_name, b.acct_name, c.acct_type_desc
				from acc_t3_audit_dtl a
				left outer join acc_account b on a.acct_id=b.id
				left outer join acc_account_type c on b.acct_type_id=c.id
				where a.hdr_id=$index
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $i=>$row) {
				$this->record[] = array(
									'acct_id'=>$row['acct_id'],
									'acct_type_desc'=>$row['acct_type_desc'],
									'acct_name'=>$row['acct_name'],
									'bank_name'=>$row['bank_name'],
									'bal_month_end'=>$row['bal_month_end'],
									'bal_t3'=>$row['bal_t3'],
									'bal_lbs'=>$row['bal_month_end'],
									'balance_test'=>$row['balance'],
									'bal_adj'=>0,
									'bal_adj_id'=>0,
									'tot_adj_t'=>0,
									'tot_tr_lnr'=>0,
									'tot_tp_lnp'=>0,
									'tot_adj_l'=>0,
									'tot_lr_tnr'=>0,
									'tot_lp_tnp'=>0,
									'lbsrecord'=>array(),
									't3record'=>array(),
								);
				$idlist .= $idlist=='' ? $row['acct_id'] : ','.$row['acct_id'];
			}
		}
		if ($this->isReadOnly()) return true;

		$sql = "select AccountBalance(a.id,'$city','2010-01-01','$date') as balance, 
				a.id, a.acct_no, a.acct_name, a.bank_name, b.acct_type_desc 
				from acc_account a
				left outer join acc_account_type b on a.acct_type_id=b.id
				where (a.city='$city' or a.city='99999')
			";
		$idlist!='' && $sql .= " and a.id not in ($idlist)";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $i=>$row) {
                $this->record[] = array(
                    'acct_id'=>$row['id'],
                    'acct_type_desc'=>$row['acct_type_desc'],
                    'acct_name'=>$row['acct_name'],
                    'bank_name'=>$row['bank_name'],
                    'bal_month_end'=>$row['balance'],
                    'balance_test'=>$row['balance'],
                    'bal_t3'=>0,
                    'bal_lbs'=>$row['balance'],
                    'bal_adj'=>0,
                    'bal_adj_id'=>0,
                    'tot_adj_t'=>0,
                    'tot_tr_lnr'=>0,
                    'tot_tp_lnp'=>0,
                    'tot_adj_l'=>0,
                    'tot_lr_tnr'=>0,
                    'tot_lp_tnp'=>0,
                    'lbsrecord'=>array(),
                    't3record'=>array(),
                );
			}
		}
			
		return true;
	}

	protected function readAdjust() {
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$citylist = Yii::app()->user->city_allow();
		$year = $this->audit_year;
		$month = $this->audit_month;

		foreach ($this->record as $i=>$row) {
			$acctid = $row['acct_id'];				
			$sql = "select a.* 
					from acc_balance_adj a
					where a.city='$city' and a.audit_year=$year
					and a.audit_month=$month and a.acct_id=$acctid
					and a.city in ($citylist)
				";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				foreach ($rows as $row) {
					$temp = array();
					$temp['id'] = $row['id'];
					$temp['adjtype'] = $row['adj_type'];
					$temp['amount'] = $row['amount'];
					$temp['remarks'] = $row['remarks'];
					$temp['uflag'] = 'N';
					
					switch ($row['adj_type']) {
						case 'ADJ':
							$this->record[$i]['bal_adj'] = $row['amount'];
							$this->record[$i]['bal_adj_id'] = $row['id'];
							break;
						case 'L1':
							$this->record[$i]['tot_lr_tnr'] += $row['amount'];
							$this->record[$i]['lbsrecord'][] = $temp;
							break;
						case 'L2':
							$this->record[$i]['tot_lp_tnp'] += $row['amount'];
							$this->record[$i]['lbsrecord'][] = $temp;
							break;
						case 'T1':
							$this->record[$i]['tot_tr_lnr'] += $row['amount'];
							$this->record[$i]['t3record'][] = $temp;
							break;
						case 'T2':
							$this->record[$i]['tot_tp_lnp'] += $row['amount'];
							$this->record[$i]['t3record'][] = $temp;
							break;
					}
				}

				$this->record[$i]['tot_adj_t'] = $this->record[$i]['bal_t3'] - $this->record[$i]['tot_tr_lnr'] + $this->record[$i]['tot_tp_lnp'];
				$this->record[$i]['tot_adj_l'] = $this->record[$i]['bal_lbs'] - $this->record[$i]['tot_lr_tnr'] + $this->record[$i]['tot_lp_tnp'];
			}
			//empty($this->record[$i]['t3record']) && $this->record[$i]['t3record'] = array(array('id'=>'0','adjtype'=>'','amount'=>'','remarks'=>'','uflag'=>'N'));
			//empty($this->record[$i]['lbsrecord']) && $this->record[$i]['lbsrecord'] = array(array('id'=>'0','adjtype'=>'','amount'=>'','remarks'=>'','uflag'=>'N'));
		}
		return true;
	}
	
	public function retrieveData($index) {
		return ($this->readHeader($index) && $this->readDetail() && $this->readAdjust());
	}

	public function confirm() {
		$this->save();
	}
	
	public function save() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$this->saveDetail($connection);
			$this->saveAdjust($connection);
			$this->updateDocman($connection,'T3BAL');
			$this->updateDocman($connection,'T3CASH');
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
	
	protected function saveHeader(&$connection) {
		switch ($this->scenario) {
			case 'new':
				$sql = "insert into acc_t3_audit_hdr(
							audit_year, audit_month, req_user, req_dt, audit_user, audit_dt, remarks, bal_diff, city, lcu, luu
						) values (
						:audit_year, :audit_month, :req_user, :req_dt, :audit_user, :audit_dt, :remarks, :bal_diff, :city, :lcu, :luu
					)";
				break;
			case 'edit':
				$sql = "update acc_t3_audit_hdr set 
							audit_user = :audit_user, audit_dt = :audit_dt, remarks = :remarks, bal_diff = :bal_diff, luu = :luu
						where id = :id
					";
		}
		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$this->bal_diff = $this->isBalanceDiff() ? 'Y' : 'N';

		$command=$connection->createCommand($sql);
		if (strpos($sql,':audit_year')!==false)
			$command->bindParam(':audit_year',$this->audit_year,PDO::PARAM_INT);
		if (strpos($sql,':audit_month')!==false)
			$command->bindParam(':audit_month',$this->audit_month,PDO::PARAM_INT);
		if (strpos($sql,':req_user')!==false)
			$command->bindParam(':req_user',$this->req_user,PDO::PARAM_STR);
		if (strpos($sql,':req_dt')!==false)
			$command->bindParam(':req_dt',$this->req_dt,PDO::PARAM_STR);
		if (strpos($sql,':audit_user')!==false)
			$command->bindParam(':audit_user',$this->audit_user,PDO::PARAM_STR);
		if (strpos($sql,':audit_dt')!==false) {
			$adate = General::toMyDate($this->audit_dt);
			$command->bindParam(':audit_dt',$adate,PDO::PARAM_STR);
		}
		if (strpos($sql,':remarks')!==false)
			$command->bindParam(':remarks',$this->remarks,PDO::PARAM_STR);
		if (strpos($sql,':bal_diff')!==false)
			$command->bindParam(':bal_diff',$this->bal_diff,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveDetail(&$connection) {
		$sql = "insert into acc_t3_audit_dtl 
					(hdr_id, acct_id, bal_month_end, bal_t3, lcu, luu)
				values
					(:hdr_id, :acct_id, :bal_month_end, :bal_t3, :lcu, :luu)
				on duplicate key update
					bal_month_end = :bal_month_end, bal_t3 = :bal_t3, luu = :luu
			";
		$uid = Yii::app()->user->id;
		
		foreach ($this->record as $k=>$row) {
			$command=$connection->createCommand($sql);
			
			if (strpos($sql,':hdr_id')!==false) 
				$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':acct_id')!==false) 
				$command->bindParam(':acct_id',$row['acct_id'],PDO::PARAM_INT);
			if (strpos($sql,':bal_month_end')!==false) {
				$amt1 = $row['bal_month_end']==0 ? '0.00' : General::toMyNumber($row['bal_month_end']);
				$command->bindParam(':bal_month_end',$amt1,PDO::PARAM_STR);
			}
			if (strpos($sql,':bal_t3')!==false) {
				$amt2 = $row['bal_t3']==0 ? '0.00' : General::toMyNumber($row['bal_t3']);
				$command->bindParam(':bal_t3',$amt2,PDO::PARAM_STR);
			}
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			
			$command->execute();
		}
		return true;
	}
	
	protected function saveAdjust(&$connection) {
		$sql_i = "insert into acc_balance_adj 
					(audit_year, audit_month, acct_id, adj_type, amount, remarks, city, lcu, luu)
				values
					(:audit_year, :audit_month, :acct_id, :adj_type, :amount, :remarks, :city, :uid, :uid)
			";
		$sql_u = "update acc_balance_adj 
					set adj_type=:adj_type, amount=:amount, remarks=:remarks, luu=:uid
				where 
					id = :id
			";
		$sql_d = "delete from acc_balance_adj where id = :id";
		
		$uid = Yii::app()->user->id;
		
		foreach ($this->record as $k=>$row) {
			$records = array_merge($row['t3record'], $row['lbsrecord']);
			$records[] = array('id'=>$row['bal_adj_id'], 'remarks'=>'', 'amount'=>$row['bal_adj'], 'adjtype'=>'ADJ', 'uflag'=>'Y');
			
			foreach ($records as $record) {
				if (!empty($record['uflag']) && $record['uflag']!='N') {
					$sql = '';
					if ($record['uflag']=='D') {
						$sql = $record['id']==0 ? '' : $sql_d;
					} else {	
						$sql = $record['id']==0 ? $sql_i : $sql_u;
					}
				
					if (!empty($sql)) {
						$command=$connection->createCommand($sql);
						if (strpos($sql,':id')!==false) 
							$command->bindParam(':id',$record['id'],PDO::PARAM_INT);
						if (strpos($sql,':audit_year')!==false) 
							$command->bindParam(':audit_year',$this->audit_year,PDO::PARAM_INT);
						if (strpos($sql,':audit_month')!==false) 
							$command->bindParam(':audit_month',$this->audit_month,PDO::PARAM_INT);
						if (strpos($sql,':acct_id')!==false) 
							$command->bindParam(':acct_id',$row['acct_id'],PDO::PARAM_INT);
						if (strpos($sql,':adj_type')!==false) 
							$command->bindParam(':adj_type',$record['adjtype'],PDO::PARAM_STR);
						if (strpos($sql,':amount')!==false) {
							$amt1 = $record['amount']==0 ? '0.00' : General::toMyNumber($record['amount']);
							$command->bindParam(':amount',$amt1,PDO::PARAM_STR);
						}
						if (strpos($sql,':remarks')!==false) 
							$command->bindParam(':remarks',$record['remarks'],PDO::PARAM_STR);
						if (strpos($sql,':city')!==false) 
							$command->bindParam(':city',$this->city,PDO::PARAM_STR);
						if (strpos($sql,':uid')!==false)
							$command->bindParam(':uid',$uid,PDO::PARAM_STR);
						$command->execute();
					}	
				}
			}
		}
		return true;
	}

	protected function updateDocman(&$connection, $doctype) {
		$docidx = strtolower($doctype);
		if ($this->docMasterId[$docidx] > 0) {
			$docman = new DocMan($doctype,$this->id,get_class($this));
			$docman->masterId = $this->docMasterId[$docidx];
			$docman->updateDocId($connection, $this->docMasterId[$docidx]);
		}
	}

	protected function isBalanceDiff() {
		foreach ($this->record as $k=>$row) {
			$amt1 = $row['bal_month_end']==0 ? '0.00' : General::toMyNumber($row['bal_month_end']);
			$amt2 = $row['bal_t3']==0 ? '0.00' : General::toMyNumber($row['bal_t3']);
			if ($amt1!=$amt2) return true;
		}
		return false;
	}
	
	public function validateAttachment($attribute, $params) {
		if (!empty($this->audit_dt) && !empty($this->audit_user)) {
			$count = $this->no_of_attm['t3bal'];
			if (empty($count) || $count==0) {
				$this->addError($attribute, Yii::t('trans','Please upload Balance screen dump'));
			}
			$count = $this->no_of_attm['t3cash'];
			if (empty($count) || $count==0) {
				$this->addError($attribute, Yii::t('trans','Please upload Cash Audit Table'));
			}
		}
	}

	public function validateRecord($attribute, $params){
		$message = '';
		$flag = false;
		if (!empty($this->audit_dt) && !empty($this->audit_user)) {
			foreach ($this->record as $data) {
				if (!isset($data['bal_month_end']) || !isset($data['bal_t3']) || $data['bal_month_end']!=$data['bal_t3']) {
//					$str = '('.$data['acct_type_desc'].') '.$data['acct_name']
//						.(empty($data['bank_name']) ? '' : ' - ').$data['bank_name'].' '.Yii::t('trans','Balance');
//					$message = $str.Yii::t('trans','and T3 balance are not the same');
//					$this->addError($attribute,$message);

// Lines below are for modification
					if (empty($this->remarks)) {
						$message = Yii::t('trans','Account balance and T3 balance are not the same. Please fill in remarks.');
						$this->addError($attribute,$message);
						$flag = true;
					}
					
					
					if (!$flag) {
						$nodata = true;
						
						foreach ($data['t3record'] as $items) {
							if (!empty($items['amount']) && !empty($items['remarks'])) {
								$nodata = false;
								break;
							}
						}
						
						if ($nodata) {
							foreach ($data['lbsrecord'] as $items) {
								if (!empty($items['amount']) && !empty($items['remarks'])) {
									$nodata = false;
									break;
								}
							}
						}

						if ($nodata || (empty($data['t3record']) && empty($data['lbsrecord']))) {
							$message = Yii::t('trans','Account balance and T3 balance are not the same. Please fill in balance adjustment detail.');
							$this->addError($attribute,$message);
							$flag = true;
						}
					}

					if (!$flag) {
						if (round(abs(($data["bal_t3"]-$data["tot_tr_lnr"]+$data["tot_tp_lnp"])-($data["bal_lbs"]-$data["tot_lr_tnr"]+$data["tot_lp_tnp"])),2)-round(abs($data["bal_adj"]),2)!=0) {
							$message = Yii::t('trans','Account balance and T3 balance are not the same. Please check balance adjustment detail.');
							$this->addError($attribute,$message);
							$flag = true;
						}
					}

					if ($flag) break;
				}
			}
		}
	}
	
	public function validateAdjustRecord($index){
		$message = '';
		$flag = true;

		foreach ($this->record[$index]['t3record'] as $data) {
			if ($data['uflag']=='Y') {
				if (empty($data['adjtype'])) {
					$message = Yii::t('trans','T3 Items: Type cannot be none');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
				if (empty($data['remarks'])) {
					$message = Yii::t('trans','T3 Items: Remarks cannot be empty');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
				if (!is_numeric($data['amount'])) {
					$message = Yii::t('trans','T3 Items: Amount value is incorrect');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
			}
		}

		foreach ($this->record[$index]['lbsrecord'] as $data) {
			if ($data['uflag']=='Y') {
				if (empty($data['adjtype'])) {
					$message = Yii::t('trans','LBS Items: Type cannot be none');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
				if (empty($data['remarks'])) {
					$message = Yii::t('trans','LBS Items: Remarks cannot be empty');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
				if (!is_numeric($data['amount'])) {
					$message = Yii::t('trans','LBS Items: Amount value is incorrect');
					$this->addError('record',$message);
					$flag = false;
					break;
				}
			}
		}
		
		return $flag;
	}

	public function isReadOnly() {
		return ($this->scenario=='view' || !empty($this->audit_dt));
	}
}
