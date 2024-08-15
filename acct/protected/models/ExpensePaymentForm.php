<?php

class ExpensePaymentForm extends ExpenseApplyForm
{
    public $shift_city;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,exp_code,employee_id,apply_date,city,status_type,amt_money,remark,reject_note,acc_id,payment_type,payment_date','safe'),
			array('employee_id,apply_date','required'),
            array('id','validateID'),
            array('acc_id,payment_type,payment_date','required','on'=>array("audit")),
            array('status_type','validateFiles','on'=>array("audit")),//验证审核时有没有上传附件
            array('reject_note','required','on'=>array("reject")),
            array('shift_city','required','on'=>array("shift")),
            array('no_of_attm, docType, files, removeFileId, docMasterId','safe'),
		);
	}

    public function validateFiles($attribute, $params) {
	    //SELECT count(b.id) INTO no_of_doc FROM dm_master a, dm_file b
        //WHERE a.id = b.mast_id AND a.doc_type_code = doctype AND a.doc_id = docid AND b.remove<>'Y';
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id")
            ->from("docman{$suffix}.dm_file b")
            ->leftJoin("docman{$suffix}.dm_master a","a.id = b.mast_id")
            ->where("a.doc_type_code=:doctype and a.doc_id = :docid AND b.remove<>'Y' and b.lcd>'{$this->lud}'",
                array(":doctype"=>$this->docType,":docid"=>$this->id)
            )->queryRow();
        if(!$row){
            $this->addError($attribute, "扣款时请上传附件");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $city = Yii::app()->user->city();
        if($this->getScenario()!="new"){
            $row = Yii::app()->db->createCommand()->select("*")->from("acc_expense")
                ->where("id=:id and table_type={$this->table_type} and status_type=4 and city='{$city}'",array(":id"=>$id))->queryRow();
            if($row){
                $this->city = $row["city"];
                $this->lud = $row["lud"];
                $this->exp_code = $row['exp_code'];
                $this->apply_date = General::toMyDate($row['apply_date']);
                $this->employee_id = $row["employee_id"];
                $this->amt_money = floatval($row["amt_money"]);
                $sql = "select * from acc_expense_info where exp_id='".$id."'";
                $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
                if($infoRows){
                    $this->infoDetail=array();
                    foreach ($infoRows as $infoRow){
                        $this->infoDetail[]=array(
                            "id"=>$infoRow["id"],
                            "expId"=>$infoRow["exp_id"],
                            "setId"=>$infoRow["set_id"],
                            "tripId"=>$infoRow["trip_id"],
                            "infoDate"=>General::toMyDate($infoRow["info_date"]),
                            "amtType"=>$infoRow["amt_type"],
                            "infoRemark"=>$infoRow["info_remark"],
                            "infoAmt"=>$infoRow["info_amt"],
                            "infoJson"=>$infoRow["info_json"],
                            "uflag"=>"N",
                        );
                    }
                }

                if(!empty($this->fileList)){
                    $tableDetailList = ExpenseFun::getExpenseTableDetailForID($id);
                    foreach ($this->fileList as $detailRow){
                        if(key_exists($detailRow["field_id"],$tableDetailList)){
                            $this->tableDetail[$detailRow["field_id"]] = $tableDetailList[$detailRow["field_id"]]["field_value"];
                        }
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
        $city = Yii::app()->user->city();
		$sql = "select *,docman$suffix.countdoc('expen',id) as expendoc from acc_expense where id='".$index."' and table_type={$this->table_type} and city='{$city}' and status_type in (4,6)";
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

            //$data = $this->curlPaymentJD();//发送消息给金蝶系统
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
					status_type = 9,
					payment_date = :payment_date,
					payment_type = :payment_type,
					acc_id = :acc_id,
					luu = :luu
					where id = :id and table_type = :table_type";
				break;
			case 'reject':
				$sql = "update acc_expense set 
					status_type = :status_type,
					reject_note = :reject_note,
					luu = :luu
					where id = :id and table_type = :table_type";
				break;
			case 'shift':
				$sql = "update acc_expense set 
					city = :shift_city,
					luu = :luu
					where id = :id and table_type = :table_type";
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
		if (strpos($sql,':shift_city')!==false)
			$command->bindParam(':shift_city',$this->shift_city,PDO::PARAM_STR);
		if (strpos($sql,':status_type')!==false)
			$command->bindParam(':status_type',$this->status_type,PDO::PARAM_INT);
		if (strpos($sql,':payment_date')!==false)
			$command->bindParam(':payment_date',$this->payment_date,PDO::PARAM_STR);
		if (strpos($sql,':payment_type')!==false)
			$command->bindParam(':payment_type',$this->payment_type,PDO::PARAM_STR);
		if (strpos($sql,':acc_id')!==false)
			$command->bindParam(':acc_id',$this->acc_id,PDO::PARAM_INT);

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

        $this->addTransOut($connection);//增加扣款申请(扣款需要等待金蝶系统回传消息)
		return true;
	}

    //发送消息给金蝶系统
	protected function curlPaymentJD(){
        $curlModel = new CurlForPayment();
        $arr = $curlModel->sendJDCurlForPayment($this);
        $curlModel->saveTableForArr();
        return $arr;
    }

	protected function addTransOut($connection){
        if($this->status_type==9){//已完成
            $suffix = Yii::app()->params['envSuffix'];
            $uid = Yii::app()->user->id;
            $expenseList = Yii::app()->db->createCommand()
                ->select("a.*,b.code as employee_code,b.name as employee_name")
                ->from("acc_expense a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("a.id='{$this->id}'")->queryRow();
            $remark = $expenseList["remark"];
            $remark.=empty($remark)?"":"\r\n";
            $remark.="日常费用报销自动生成，编号：".$expenseList["exp_code"];
            $connection->createCommand()->insert("acc_trans",array(
                "trans_dt"=>$expenseList["payment_date"],
                "trans_type_code"=>$expenseList["payment_type"],
                "acct_id"=>$expenseList["acc_id"],
                "amount"=>$expenseList["amt_money"],
                "city"=>$expenseList["city"],
                "lcu"=>$uid,
                "luu"=>$uid,
                "trans_desc"=>$remark,//备注
            ));
            $transId = Yii::app()->db->getLastInsertID();
            $connection->createCommand()->update("acc_expense",array(
                "payment_id"=>$transId
            ),"id=:id",array(":id"=>$this->id));
            $transInfoList=array(
                //付款人类型
                array("trans_id"=>$transId,"lcu"=>$uid,"field_id"=>"payer_type","field_value"=>"F"),
                //付款人名称
                array("trans_id"=>$transId,"lcu"=>$uid,"field_id"=>"payer_name","field_value"=>$expenseList["employee_name"]." (".$expenseList["employee_code"].")"),
                //付款人id
                array("trans_id"=>$transId,"lcu"=>$uid,"field_id"=>"payer_id","field_value"=>$expenseList["employee_id"]),
                //req_ref_no
                array("trans_id"=>$transId,"lcu"=>$uid,"field_id"=>"req_ref_no","field_value"=>$expenseList["exp_code"]),
            );
            foreach ($transInfoList as $infoList){
                $connection->createCommand()->insert("acc_trans_info",$infoList);
            }

            //复制附件
            $rows = Yii::app()->db->createCommand()
                ->select("b.phy_file_name,b.phy_path_name,b.display_name,b.file_type,b.archive,b.remove")
                ->from("docman{$suffix}.dm_file b")
                ->leftJoin("docman{$suffix}.dm_master a","a.id = b.mast_id")
                ->where("a.doc_type_code=:doctype and a.doc_id = :docid AND b.remove<>'Y'",
                    array(":doctype"=>$this->docType,":docid"=>$this->id)
                )->queryAll();
            if($rows){
                $connection->createCommand()->insert("docman{$suffix}.dm_master",array(
                    "doc_type_code"=>'TRANS',
                    "doc_id"=>$transId,
                    "lcu"=>$uid
                ));
                $masterId = Yii::app()->db->getLastInsertID();
                foreach ($rows as $row){
                    $fileList = $row;
                    $fileList["mast_id"]=$masterId;
                    $fileList["lcu"]=$uid;
                    $connection->createCommand()->insert("docman{$suffix}.dm_file",$fileList);
                }
            }
        }
    }

    protected function saveHistory($connection){
	    switch ($this->status_type){
            case 9://已完成
                $history_text=array();
                $history_text[]="<span>已完成</span>";
                $connection->createCommand()->insert("acc_expense_history", array(
                    "exp_id"=>$this->id,
                    "history_text"=>implode("<br/>",$history_text),
                    "lcu"=>Yii::app()->user->id
                ));
                break;
            case 6://等待金蝶系统扣款
                $history_text=array();
                $history_text[]="<span>等待金蝶系统扣款</span>";
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
            case 11://转移城市
                $cityName = General::getCityName($this->shift_city);
                $history_text=array();
                $history_text[]="<span>由".General::getCityName($this->city)."转移到".$cityName."</span>";
                $history_text[]="<span>扣款城市：{$cityName}</span>";
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

    public function getReadyForAcc(){
        return $this->getScenario()=="view";
    }
}