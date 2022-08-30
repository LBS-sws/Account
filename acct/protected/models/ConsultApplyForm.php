<?php

class ConsultApplyForm extends CFormModel
{
	/* User Fields */
	public $id;
    public $consult_code;
    public $apply_date;
    public $customer_code="";
    public $consult_money;
    public $apply_city;
    public $audit_city="ZY";
    public $audit_date;
    public $status;
    public $remark;
    public $reject_remark;
    public $staff_city;
    public $info_list = array(
        array('id'=>0,
            'consult_id'=>0,
            'set_id'=>'',
            'good_money'=>'',
            'uflag'=>'Y',
        ),
    );

    public $no_of_attm = array(
        'consu'=>0
    );
    public $docType = 'CONSU';
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
            'consult_code'=>Yii::t('consult','consult code'),
            'apply_date'=>Yii::t('consult','apply date'),
            'customer_code'=>Yii::t('consult','customer code'),
            'consult_money'=>Yii::t('consult','consult money'),
            'apply_city'=>Yii::t('consult','apply city'),
            'audit_city'=>Yii::t('consult','audit city'),
            'audit_date'=>Yii::t('consult','audit date'),
            'status'=>Yii::t('consult','status'),
            'remark'=>Yii::t('consult','remark'),
            'reject_remark'=>Yii::t('consult','reject remark'),

            'set_id'=>Yii::t('consult','good name'),
            'good_money'=>Yii::t('consult','good money'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,consult_code,apply_date,customer_code,consult_money,apply_city,audit_city,audit_date,status,remark,info_list','safe'),
            array('apply_date,apply_city,audit_city,consult_money','required'),
            array('consult_money','numerical','allowEmpty'=>false,'integerOnly'=>false),

            array('id','validateStaff'),
            array('id','validateID','on'=>array("delete")),
            array('info_list','validateList','on'=>array("edit","new")),

            array('no_of_attm,docType,docMasterId,files,removeFileId','safe')
        );
	}

    public function validateStaff($attribute, $params){
	    $bool = ConsultApplyList::staffCompanyForUsername($this);
        if (!$bool){
            $this->addError($attribute, "该账号未绑定员工，请与管理员联系");
            return false;
        }else{
            $this->staff_city=$this->apply_city;
        }
    }

    public function validateList($attribute, $params){
        $list = array();
        //$this->consult_money=0;
        if(!empty($this->info_list)){
            foreach ($this->info_list as $row){
                if(!empty($row["set_id"])){
                    $row["good_money"] = empty($row["good_money"])?0:round($row["good_money"],2);
                    //$this->consult_money+=$row["good_money"];
                    $list[]=$row;
                }
            }
        }
        if(empty($list)){
            $this->addError($attribute, "请至少添加一个商品");
            return false;
        }else{
            $this->info_list = $list;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_consult")
            ->where("id=:id and status not in (0,3)",array(":id"=>$id))->queryRow();
        if($row){
            $this->addError($attribute, "只能删除草稿或被拒绝的咨询单");
            return false;
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select *,
				docman$suffix.countdoc('consu',id) as consucountdoc
				 from acc_consult where id='".$index."' and (apply_city='{$this->apply_city}' or (audit_city='{$this->apply_city}' and status in (2,3)))";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
            $this->staff_city=$this->apply_city;
			$this->id = $row['id'];
			$this->consult_code = $row['consult_code'];
            $this->apply_date = General::toDate($row['apply_date']);
            $this->audit_date = General::toDate($row['audit_date']);
            $this->customer_code = $row['customer_code'];
            $this->consult_money = floatval($row['consult_money']);
            $this->apply_city = $row['apply_city'];
            $this->audit_city = $row['audit_city'];
            $this->status = $row['status'];
            $this->remark = $row['remark'];
            $this->reject_remark = $row['reject_remark'];
            $this->no_of_attm['consu'] = $row['consucountdoc'];
            $this->info_list = array();
            $rows = Yii::app()->db->createCommand()->select("id,set_id,good_money")->from("acc_consult_info")
                ->where("consult_id=:id",array(":id"=>$index))->order("id asc")->queryAll();
            if($rows){
                foreach ($rows as $arr){
                    $temp = array();
                    $temp['id'] = $arr['id'];
                    $temp['consult_id'] = $index;
                    $temp['set_id'] = $arr['set_id'];
                    $temp['good_money'] = floatval($arr['good_money']);
                    $temp['uflag'] = "Y";
                    $this->info_list[] = $temp;
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
            $this->saveInfo($connection);
            $this->updateDocman($connection,'CONSU');
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

    protected function saveInfo(&$connection){
        $uid = Yii::app()->user->id;
        foreach ($this->info_list as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from acc_consult_info where consult_id = :consult_id";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_consult_info(
									consult_id, set_id, good_money
								) values (
									:consult_id, :set_id, :good_money
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_consult_info where id = :id";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_consult_info(
										consult_id, set_id, good_money
									) values (
										:consult_id, :set_id, :good_money
									)"
                                :
                                "update acc_consult_info set
										set_id = :set_id,
										good_money = :good_money
									where id = :id and consult_id=:consult_id
									";
                            break;
                    }
                    break;
            }

            if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
                $command=$connection->createCommand($sql);
                if (strpos($sql,':id')!==false)
                    $command->bindParam(':id',$row['id'],PDO::PARAM_INT);
                if (strpos($sql,':consult_id')!==false)
                    $command->bindParam(':consult_id',$this->id,PDO::PARAM_INT);

                if (strpos($sql,':set_id')!==false) {
                    $command->bindParam(':set_id',$row['set_id'],PDO::PARAM_STR);
                }
                if (strpos($sql,':good_money')!==false) {
                    $command->bindParam(':good_money',$row['good_money'],PDO::PARAM_STR);
                }
                $command->execute();
            }
        }
    }

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_consult where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_consult(
						apply_date, customer_code, consult_money, apply_city, audit_city, status, remark, city, lcu, lcd) values (
						:apply_date, :customer_code, :consult_money, :apply_city, :audit_city, :status, :remark, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_consult set 
					apply_date = :apply_date, 
					consult_money = :consult_money,
					audit_city = :audit_city,
					remark = :remark,
					status = :status,
					reject_remark = '',
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		//apply_date, customer_code, consult_money, apply_city, audit_city, remark
		if (strpos($sql,':apply_date')!==false)
			$command->bindParam(':apply_date',$this->apply_date,PDO::PARAM_STR);
		if (strpos($sql,':customer_code')!==false)
			$command->bindParam(':customer_code',$this->customer_code,PDO::PARAM_STR);
		if (strpos($sql,':consult_money')!==false)
			$command->bindParam(':consult_money',$this->consult_money,PDO::PARAM_INT);
		if (strpos($sql,':apply_city')!==false)
			$command->bindParam(':apply_city',$this->apply_city,PDO::PARAM_STR);
		if (strpos($sql,':audit_city')!==false)
			$command->bindParam(':audit_city',$this->audit_city,PDO::PARAM_STR);
		if (strpos($sql,':remark')!==false)
			$command->bindParam(':remark',$this->remark,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);

		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
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
            $this->consult_code=$this->apply_city.(100000+$this->id);
            Yii::app()->db->createCommand()->update("acc_consult",array(
                "consult_code"=>$this->consult_code
            ),"id=:id",array(":id"=>$this->id));
        }

		return true;
	}

	public function isReady(){
	    return $this->getScenario()=="view"||in_array($this->status,array(1,2))||$this->staff_city!=$this->apply_city;
    }
}