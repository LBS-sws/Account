<?php

class ExpenseApplyForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $exp_code;
	public $employee_id;
	public $apply_date;
	public $audit_user;
	public $audit_json;
	public $current_num;
	public $current_username;
	public $city;
	public $status_type=0;
	public $amt_money;
	public $payment_id;
	public $payment_type;
	public $payment_date;
	public $acc_id;
	public $remark;
	public $reject_note;
	public $lcu;
	public $luu;
	public $lcd;
	public $lud;

	public $infoDetail=array(
	    array(
            "id"=>"",
            "expId"=>"",
            "setId"=>"",
            "infoDate"=>"",
            "amtType"=>"",
            "infoRemark"=>"",
            "infoAmt"=>"",
            "infoJson"=>"[]",
            "uflag"=>"N",
        )
    );


    public $no_of_attm = array(
        'expen'=>0
    );
    public $docType = 'EXPEN';
    public $docMasterId = 0;
    public $files;
    public $removeFileId = 0;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'city'=>Yii::t('give','City'),
            'exp_code'=>Yii::t('give','expense code'),
            'apply_date'=>Yii::t('give','apply date'),
            'employee_id'=>Yii::t('give','apply user'),
            'employee'=>Yii::t('give','apply user'),
            'department'=>Yii::t('give','department'),
            'amt_money'=>Yii::t('give','sum money'),
            'status_type'=>Yii::t('give','status type'),
            'remark'=>Yii::t('give','remark'),
            'reject_note'=>Yii::t('give','reject note'),
            'payment_id'=>Yii::t('give','Payment Account'),
            'acc_id'=>Yii::t('give','Payment Account'),
            'payment_type'=>Yii::t('give','Payment Type'),
            'payment_date'=>Yii::t('give','Payment Date'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,exp_code,employee_id,apply_date,city,status_type,amt_money,remark,reject_note','safe'),
			array('employee_id,apply_date','required'),
            array('employee_id','validateEmployee'),
            array('id','validateID'),
            array('infoDetail','validateInfo'),
            array('status_type','validateStatus'),
            array('no_of_attm, docType, files, removeFileId, docMasterId','safe'),
        );
	}

    public function validateStatus($attribute, $params) {//验证是否有审核人
	    if($this->status_type!=2){
	        return true;
        }
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

    public function validateInfo($attribute, $params) {
        $updateList = array();
        $deleteList = array();
        $this->amt_money = 0;
        $typeTwoList = self::getAmtTypeTwo();
        foreach ($this->infoDetail as $list){
            $temp = array();
            if($list["uflag"]=="D"){
                $deleteList[] = $list;
            }else{
                if(!empty($list["infoAmt"])){
                    $list["infoAmt"] = is_numeric($list["infoAmt"])?round($list["infoAmt"],2):0;
                    $this->amt_money+=floatval($list["infoAmt"]);
                    foreach ($typeTwoList as $key=>$item){
                        $key = "".$key;
                        if(key_exists($key,$list)){
                            $temp[$key] = $list[$key];
                        }
                    }
                    $list["infoJson"] = json_encode($temp);
                    $updateList[]=$list;
                    if(empty($list["setId"])){
                        $this->addError($attribute, "费用归属不能为空");
                        break;
                    }
                    if(empty($list["infoDate"])){
                        $this->addError($attribute, "日期不能为空");
                        break;
                    }
                    if($list["amtType"]===""){
                        $this->addError($attribute, "费用类别不能为空");
                        break;
                    }
                }
            }
        }

        if(empty($updateList)){
            $this->addError($attribute, "报销明细不能为空");
            return false;
        }
        $this->infoDetail = array_merge($updateList,$deleteList);
    }

    public function validateEmployee($attribute, $params) {
        $id = $this->$attribute;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,city")->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row){
            $this->employee_id = $id;
            $this->city = $row["city"];
        }else{
            $this->addError($attribute, "员工不存在，请刷新重试");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $uid = Yii::app()->user->id;
        if($this->getScenario()!="new"){
            $row = Yii::app()->db->createCommand()->select("id,city")->from("acc_expense")
                ->where("id=:id and lcu='{$uid}'",array(":id"=>$id))->queryRow();
            if($row){
                $this->city = $row["city"];
            }else{
                $this->addError($attribute, "报销单不存在，请刷新重试");
                return false;
            }
        }
    }

    public static function setModelEmployee($model,$str="employee_id"){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()->select("employee_id")->from("hr{$suffix}.hr_binding a")
            ->where("user_id=:user_id",array(":user_id"=>$uid))->queryRow();
        if($row){
            $model->$str = $row["employee_id"];
        }else{
            $model->$str = null;
        }
    }

    public static function getEmployeeListForID($id) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.code,a.name,b.name as department_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.department=b.id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return array(
                "code"=>$row["code"],
                "name"=>$row["name"],
                "employee"=>$row["name"]." ({$row["code"]})",
                "department"=>$row["department_name"]
            );
        }else{
            return array("code"=>"","name"=>"","employee"=>$id,"department"=>"");
        }
    }

    public static function getAuditListForID($id) {
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("acc_expense_audit")
            ->where("exp_id=:id",array(":id"=>$id))->queryAll();
        return $rows?$rows:array();
    }

    public static function getExpenseHistoryForID($id) {
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("acc_expense_history")
            ->where("exp_id=:id",array(":id"=>$id))->queryAll();
        return $rows;
    }

    public static function getTransTypeList() {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("trans_type_code,trans_type_desc")
            ->from("acc_trans_type")
            ->where("trans_cat='OUT'")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['trans_type_code']] = $row["trans_type_desc"];
            }
        }
        return $list;
    }

    public static function getTransStrForCode($code) {
        $row = Yii::app()->db->createCommand()->select("trans_type_code,trans_type_desc")
            ->from("acc_trans_type")
            ->where("trans_type_code=:code",array(":code"=>$code))
            ->queryRow();
        if($row){
            return $row["trans_type_desc"];
        }
        return $code;
    }

    public static function getAccountListForCity($city) {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("a.*,b.acct_type_desc")
            ->from("acc_account a")
            ->leftJoin("acc_account_type b","a.acct_type_id=b.id")
            ->where("a.city=:city and a.status='Y'",array(":city"=>$city))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['id']] = "(".$row["acct_type_desc"].")".$row["acct_name"]." ".$row["acct_no"]."(".$row["bank_name"].")";
            }
        }
        return $list;
    }

    public static function getAccountStrForID($id) {
        $row = Yii::app()->db->createCommand()->select("a.*,b.acct_type_desc")
            ->from("acc_account a")
            ->leftJoin("acc_account_type b","a.acct_type_id=b.id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return "(".$row["acct_type_desc"].")".$row["acct_name"]." ".$row["acct_no"]."(".$row["bank_name"].")";
        }
        return $id;
    }

    public static function getAmtTypeOne(){
        return array(
            0=>"本地费用",
            1=>"差旅费用",
            2=>"办公费",
            3=>"快递费",
            4=>"通讯费",
            5=>"其他",
        );
    }

    public static function getAmtTypeTwo(){
        return array(
            "00001"=>array("name"=>"市内交通费",'one_type'=>0),
            "00002"=>array("name"=>"餐费",'one_type'=>0),
            "10001"=>array("name"=>"机票/火车票/汽车票",'one_type'=>1),
            "10002"=>array("name"=>"酒店",'one_type'=>1),
            "10003"=>array("name"=>"交通费",'one_type'=>1),
            "10004"=>array("name"=>"餐费",'one_type'=>1),
            "10005"=>array("name"=>"其他",'one_type'=>1),
            "20001"=>array("name"=>"办公费",'one_type'=>2),
            "30001"=>array("name"=>"快递费",'one_type'=>3),
            "40001"=>array("name"=>"通讯费",'one_type'=>4),
            "50001"=>array("name"=>"其他",'one_type'=>5),
        );
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
		$sql = "select *,docman$suffix.countdoc('expen',id) as expendoc from acc_expense where id='".$index."' and lcu='{$uid}'";
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
			$this->saveDataForInfo($connection);
            $this->updateDocman($connection,'EXPEN');
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function updateDocman(&$connection, $doctype) {
        if ($this->scenario=='new') {
            $docidx = strtolower($doctype);
            if ($this->docMasterId[$docidx] > 0) {
                $docman = new DocMan($doctype,$this->id,get_class($this));
                $docman->masterId = $this->docMasterId[$docidx];
                $docman->updateDocId($connection, $this->docMasterId[$docidx]);
            }
        }
    }

	protected function saveDataForInfo(&$connection)
	{
        $uid = Yii::app()->user->id;
		switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete('acc_expense_history', 'exp_id=:id',array(":id"=>$this->id));
                $connection->createCommand()->delete('acc_expense_audit', 'exp_id=:id',array(":id"=>$this->id));
                $connection->createCommand()->delete('acc_expense_info', 'exp_id=:id',array(":id"=>$this->id));
                break;
            case 'new':
                foreach ($this->infoDetail as $list){
                    if(in_array($list["uflag"],array("N","Y"))){
                        $connection->createCommand()->insert("acc_expense_info", array(
                            "exp_id"=>$this->id,
                            "set_id"=>$list["setId"],
                            "info_date"=>$list["infoDate"],
                            "amt_type"=>$list["amtType"],
                            "info_remark"=>$list["infoRemark"],
                            "info_amt"=>$list["infoAmt"],
                            "info_json"=>$list["infoJson"],
                        ));
                    }
                }
                break;
            case 'edit':
                foreach ($this->infoDetail as $list){
                    switch ($list["uflag"]){
                        case "D"://删除
                            $connection->createCommand()->delete('acc_expense_info', 'id=:id',array(":id"=>$list["id"]));
                            break;
                        case "Y"://修改
                            if(empty($list["id"])){
                                $connection->createCommand()->insert("acc_expense_info", array(
                                    "exp_id"=>$this->id,
                                    "set_id"=>$list["setId"],
                                    "info_date"=>$list["infoDate"],
                                    "amt_type"=>$list["amtType"],
                                    "info_remark"=>$list["infoRemark"],
                                    "info_amt"=>$list["infoAmt"],
                                    "info_json"=>$list["infoJson"],
                                ));
                            }else{
                                $connection->createCommand()->update("acc_expense_info", array(
                                    "set_id"=>$list["setId"],
                                    "info_date"=>$list["infoDate"],
                                    "amt_type"=>$list["amtType"],
                                    "info_remark"=>$list["infoRemark"],
                                    "info_amt"=>$list["infoAmt"],
                                    "info_json"=>$list["infoJson"],
                                ), "id=:id and exp_id={$this->id}", array(":id" =>$list["id"]));
                            }
                            break;
                    }
                }
                break;
        }

    }

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_expense where id = :id AND lcu=:lcu";
				break;
			case 'new':
				$sql = "insert into acc_expense(
						employee_id,apply_date, city, status_type, amt_money, remark, reject_note, lcu, lcd) values (
						:employee_id,:apply_date, :city, :status_type, :amt_money, :remark, null, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_expense set 
					apply_date = :apply_date, 
					status_type = :status_type,
					amt_money = :amt_money,
					remark = :remark,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_INT);
		if (strpos($sql,':apply_date')!==false)
			$command->bindParam(':apply_date',$this->apply_date,PDO::PARAM_STR);
		if (strpos($sql,':remark')!==false)
			$command->bindParam(':remark',$this->remark,PDO::PARAM_STR);
		if (strpos($sql,':status_type')!==false)
			$command->bindParam(':status_type',$this->status_type,PDO::PARAM_INT);
		if (strpos($sql,':amt_money')!==false){
            $command->bindParam(':amt_money',$this->amt_money,PDO::PARAM_INT);
        }

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

        if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
            $this->exp_code = "OUT".(100000+$this->id);
            $connection->createCommand()->update("acc_expense", array(
                "exp_code"=>$this->exp_code,
            ), "id=:id", array(":id" =>$this->id));
        }

        $this->saveHistory($connection);
		return true;
	}

	protected function saveHistory($connection){
        if($this->status_type==2){
            $connection->createCommand()->update("acc_expense", array(
                "audit_user"=>$this->audit_user,
                "audit_json"=>$this->audit_json,
                "current_username"=>$this->current_username,
                "current_num"=>0,
            ), "id=:id", array(":id" =>$this->id));

            $connection->createCommand()->delete('acc_expense_audit', 'exp_id=:id',array(":id"=>$this->id));

            $history_text=array();
            $history_text[]="<span>报销申请，等待审核</span>";
            $history_text[]="<span>审核人：{$this->audit_user}</span>";
            $connection->createCommand()->insert("acc_expense_history", array(
                "exp_id"=>$this->id,
                "history_text"=>implode("<br/>",$history_text),
                "lcu"=>Yii::app()->user->id
            ));
        }
    }

	public function readonly(){
        return $this->getScenario()=='view'||!in_array($this->status_type,array(0,7));
    }

	public function getReadyForAcc(){
        return true;
    }

    //由於列表需要顯示附件數量，導致列表打開太慢，所以保存附件數量
    public function resetFileSum($id=0){
        $id = empty($id)||!is_numeric($id)?0:$id;
        if(!empty($id)){
            $suffix = Yii::app()->params['envSuffix'];
            $sql = "update acc_expense set
              exp_one_num=docman{$suffix}.countdoc('expen',{$id}),
              lud=lud
              WHERE id={$id}
            ";
            Yii::app()->db->createCommand($sql)->execute();
        }
    }
}