<?php

class ExpenseAuditForm extends ExpenseApplyForm
{

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('tableDetail,id,exp_code,employee_id,apply_date,city,status_type,amt_money,remark,reject_note','safe'),
			array('employee_id,apply_date','required'),
            array('id','validateID'),
            array('employee_id','validateEmployee'),
            array('reject_note','required','on'=>array("reject")),
		);
	}

    public function validateEmployee($attribute, $params) {
        $uid = Yii::app()->user->id;
        if($this->current_username!=$uid){
            $this->addError($attribute, "权限异常，请刷新重试");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $uid = Yii::app()->user->id;
        $city_allow = Yii::app()->user->city_allow();
        if($this->getScenario()!="new"){
            $row = Yii::app()->db->createCommand()->select("id,city,employee_id,amt_money,audit_user,audit_json,current_num,current_username")->from("acc_expense")
                ->where("id=:id and table_type={$this->table_type} and status_type=2 and FIND_IN_SET('{$uid}',audit_user)",array(":id"=>$id))->queryRow();
            if($row){
                $this->city = $row["city"];
                $this->employee_id = $row["employee_id"];
                $this->audit_user = $row["audit_user"];
                $this->audit_json = json_decode($row["audit_json"],true);
                $this->current_num = $row["current_num"];
                $this->current_username = $row["current_username"];
                $this->amt_money = floatval($row["amt_money"]);
                $this->infoDetail=array();
                $infoRows = Yii::app()->db->createCommand()->select("*")->from("acc_expense_info")
                    ->where("exp_id=:exp_id",array(":exp_id"=>$id))->queryAll();
                if($infoRows){
                    foreach ($infoRows as $infoRow){
                        $this->infoDetail[]=array(
                            "id"=>$infoRow["id"],
                            "expId"=>$infoRow["exp_id"],
                            "setId"=>$infoRow["set_id"],
                            "tripId"=>$infoRow["trip_id"],
                            "infoDate"=>General::toDate($infoRow["info_date"]),
                            "amtType"=>$infoRow["amt_type"],
                            "infoRemark"=>$infoRow["info_remark"],
                            "infoAmt"=>$infoRow["info_amt"],
                            "infoJson"=>$infoRow["info_json"],
                            "uflag"=>"N",
                        );
                    }
                }
            }else{
                $this->addError($attribute, "报销单不存在，请刷新重试");
                return false;
            }
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $city_allow = Yii::app()->user->city_allow();
		$sql = "select *,docman$suffix.countdoc('expen',id) as expendoc from acc_expense where id='".$index."' and table_type={$this->table_type} and status_type in (2,4,6) and FIND_IN_SET('{$uid}',audit_user)";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $index;
			$this->employee_id = $row['employee_id'];
			$this->exp_code = $row['exp_code'];
			$this->apply_date = General::toDate($row['apply_date']);
            $this->city = $row['city'];
            $this->status_type = $row['status_type'];
            $this->current_username = $row['current_username'];
            $this->audit_json = json_decode($row["audit_json"],true);
            $this->amt_money = $row['amt_money'];
            $this->remark = $row['remark'];
            $this->reject_note = $row['reject_note'];
            $this->payment_date = $row['payment_date'];
            $this->payment_type = $row['payment_type'];
            $this->payment_id = $row['payment_id'];
            $this->acc_id = $row['acc_id'];
            $this->no_of_attm['expen'] = $row['expendoc'];
            $sql = "select * from acc_expense_info where exp_id='".$index."'";
            $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($infoRows){
                $this->infoDetail=array();
                foreach ($infoRows as $infoRow){
                    $this->infoDetail[]=array(
                        "id"=>$infoRow["id"],
                        "expId"=>$infoRow["exp_id"],
                        "setId"=>$infoRow["set_id"],
                        "tripId"=>$infoRow["trip_id"],
                        "infoDate"=>General::toDate($infoRow["info_date"]),
                        "amtType"=>$infoRow["amt_type"],
                        "infoRemark"=>$infoRow["info_remark"],
                        "infoAmt"=>$infoRow["info_amt"],
                        "infoJson"=>$infoRow["info_json"],
                        "uflag"=>"N",
                    );
                }
            }

            if(!empty($this->fileList)){
                $tableDetailList = ExpenseFun::getExpenseTableDetailForID($index);
                foreach ($this->fileList as $detailRow){
                    if(key_exists($detailRow["field_id"],$tableDetailList)){
                        $this->tableDetail[$detailRow["field_id"]] = $tableDetailList[$detailRow["field_id"]]["field_value"];
                    }else{
                        $this->tableDetail[$detailRow["field_id"]] = "";
                    }
                }
            }
            return true;
		}else{
		    return false;
        }
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
            $data = $this->curlPaymentJD($transaction);//发送消息给金蝶系统
            if($data["code"]==200){
                return true;
            }else{
                $this->addError("id", $data["message"]);
                return false;
            }
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    //发送消息给金蝶系统
    protected function curlPaymentJD(&$transaction){
        $arr=array("code"=>200,"message"=>"");
	    if($this->status_type==6){
            $curlModel = new CurlForPayment();
            $arr = $curlModel->sendJDCurlForPayment($this);
            if($arr["code"]==200){
                $transaction->commit();
            }else{
                $transaction->rollback();
            }
            $curlModel->saveTableForArr();
        }else{
            $transaction->commit();
        }
        return $arr;
    }

	protected function setAuditFinish(){
	    $this->current_num++;
	    $auditUser = explode(",",$this->audit_user);
	    if(count($auditUser)>$this->current_num){
	        //需要继续审核
            $this->current_username = $auditUser[$this->current_num];
        }else{
            //审核完成
            $this->status_type=6;
        }
    }

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'audit':
			    $this->setAuditFinish();
				$sql = "update acc_expense set 
					status_type = :status_type,
					current_username = :current_username,
					current_num = :current_num,
					luu = :luu
					where id = :id and table_type=:table_type";
				break;
			case 'reject':
				$sql = "update acc_expense set 
					status_type = :status_type,
					reject_note = :reject_note,
					luu = :luu
					where id = :id and table_type=:table_type";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':table_type')!==false)
			$command->bindParam(':table_type',$this->table_type,PDO::PARAM_INT);
		if (strpos($sql,':reject_note')!==false)
			$command->bindParam(':reject_note',$this->reject_note,PDO::PARAM_STR);
		if (strpos($sql,':current_num')!==false)
			$command->bindParam(':current_num',$this->current_num,PDO::PARAM_INT);
		if (strpos($sql,':current_username')!==false)
			$command->bindParam(':current_username',$this->current_username,PDO::PARAM_STR);
		if (strpos($sql,':audit_user')!==false)
			$command->bindParam(':audit_user',$this->audit_user,PDO::PARAM_STR);
		if (strpos($sql,':status_type')!==false)
			$command->bindParam(':status_type',$this->status_type,PDO::PARAM_INT);

		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        $this->saveHistory($connection);
		return true;
	}

    protected function saveHistory($connection){
        $uid = Yii::app()->user->id;
        $currentNum = $this->current_num-1;
        $audit_str = isset($this->audit_json[$currentNum])?$this->audit_json[$currentNum]["audit_tag"]:"";
	    switch ($this->status_type){
            case 2://待确定
                $history_text=array();
                $history_text[]="<span>已审核，等待下次审核</span>";
                $history_text[]="<span>下次审核人：{$this->current_username}</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_type"=>2,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>$uid
                ));
                $connection->createCommand()->insert("acc_expense_audit", array(
                    "exp_id"=>$this->id,
                    "audit_user"=>$uid,
                    "audit_str"=>$audit_str,
                    "audit_date"=>date_format(date_create(),"Y/m/d"),
                    "lcu"=>$uid
                ));
                break;
            case 3://已拒绝
                $history_text=array();
                $history_text[]="<span>已拒绝</span>";
                $history_text[]="<span>拒绝原因：{$this->reject_note}</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>$uid
                ));
                break;
            case 4://已审核
                $history_text=array();
                $history_text[]="<span>已审核，等待填写银行</span>";
                $history_text[]="<span>扣款城市：".General::getCityName($this->city)."</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_type"=>2,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>$uid
                ));
                $connection->createCommand()->insert("acc_expense_audit", array(
                    "exp_id"=>$this->id,
                    "audit_user"=>$uid,
                    "audit_str"=>$audit_str,
                    "audit_date"=>date_format(date_create(),"Y/m/d"),
                    "lcu"=>$uid
                ));
                break;
            case 6://等待金蝶系统扣款
                $history_text=array();
                $history_text[]="<span>已审核，等待金蝶系统扣款</span>";
                $history_text[]="<span>扣款城市：".General::getCityName($this->city)."</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_type"=>2,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>$uid
                ));
                $connection->createCommand()->insert("acc_expense_audit", array(
                    "exp_id"=>$this->id,
                    "audit_user"=>$uid,
                    "audit_str"=>$audit_str,
                    "audit_date"=>date_format(date_create(),"Y/m/d"),
                    "lcu"=>$uid
                ));
                break;
        }
    }

	public function readonly(){
        return true;
    }
}