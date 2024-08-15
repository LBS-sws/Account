<?php

class ExpenseConfirmForm extends ExpenseApplyForm
{

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,exp_code,employee_id,apply_date,city,status_type,amt_money,remark,reject_note','safe'),
			array('employee_id,apply_date','required'),
            array('id','validateID'),
            array('employee_id','validateEmployee','on'=>array("audit")),
		);
	}

    public function validateEmployee($attribute, $params) {
	    $this->audit_user=array();
	    $this->audit_json=array();
	    $this->current_username="";
        $auditRows = Yii::app()->db->createCommand()->select("a.*")
            ->from("acc_set_audit_info a")
            ->leftJoin("acc_set_audit b","a.set_id=b.id")
            ->where("b.employee_id=:id",array(":id"=>$this->employee_id))
            ->order("a.z_index asc")->queryAll();
        if($auditRows){
            foreach ($auditRows as $row){
                if(empty($row["amt_bool"])){//不限制金额
                    $this->audit_user[]=$row["audit_user"];
                    $this->audit_json[]=array("audit_user"=>$row["audit_user"],"audit_tag"=>$row["audit_tag"]);
                }else{//限制金额
                    $amtMin = floatval($row["amt_min"]);
                    $amtMax = floatval($row["amt_max"]);
                    if($this->amt_money>=$amtMin&&$this->amt_money<=$amtMax){
                        $this->audit_user[]=$row["audit_user"];
                        $this->audit_json[]=array("audit_user"=>$row["audit_user"],"audit_tag"=>$row["audit_tag"]);
                    }
                }
            }
            if(empty($this->audit_user)){
                $this->addError($attribute, "报销金额（{$this->amt_money}）异常，请与管理员联系");
                return false;
            }
            $this->current_username = $this->audit_user[0];
            $this->audit_user = implode(",",$this->audit_user);
            $this->audit_json = json_encode($this->audit_json);
        }else{
            $this->addError($attribute, "该员工没有指定审核人，请与管理员联系");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $city_allow = Yii::app()->user->city_allow();
        if($this->getScenario()!="new"){
            $row = Yii::app()->db->createCommand()->select("id,city,employee_id,amt_money")->from("acc_expense")
                ->where("id=:id and table_type={$this->table_type} and status_type=1 and city in ({$city_allow})",array(":id"=>$id))->queryRow();
            if($row){
                $this->city = $row["city"];
                $this->employee_id = $row["employee_id"];
                $this->amt_money = floatval($row["amt_money"]);
            }else{
                $this->addError($attribute, "报销单不存在，请刷新重试");
                return false;
            }
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
		$sql = "select *,docman$suffix.countdoc('expen',id) as expendoc from acc_expense where id='".$index."' and table_type={$this->table_type} and city in ({$city_allow}) and status_type in (1,2,4,6)";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $index;
			$this->employee_id = $row['employee_id'];
			$this->exp_code = $row['exp_code'];
			$this->apply_date = General::toDate($row['apply_date']);
            $this->city = $row['city'];
            $this->status_type = $row['status_type'];
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
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'audit':
				$sql = "update acc_expense set 
					status_type = :status_type,
					audit_user = :audit_user,
					audit_json = :audit_json,
					current_username = :current_username,
					current_num = 0,
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
		if (strpos($sql,':current_username')!==false)
			$command->bindParam(':current_username',$this->current_username,PDO::PARAM_STR);
		if (strpos($sql,':audit_user')!==false)
			$command->bindParam(':audit_user',$this->audit_user,PDO::PARAM_STR);
		if (strpos($sql,':audit_json')!==false)
			$command->bindParam(':audit_json',$this->audit_json,PDO::PARAM_STR);
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
	    switch ($this->status_type){
            case 2://待确定
                $history_text=array();
                $history_text[]="<span>已确认，等待审核</span>";
                $history_text[]="<span>审核人：{$this->audit_user}</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>Yii::app()->user->id
                ));
                break;
            case 3://已拒绝
                $history_text=array();
                $history_text[]="<span>已拒绝</span>";
                $history_text[]="<span>拒绝原因：{$this->reject_note}</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>Yii::app()->user->id
                ));
                break;
        }
    }

	public function readonly(){
        return true;
    }
}