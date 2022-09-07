<?php

class ConsultAuditForm extends CFormModel
{
	/* User Fields */
	public $id;
    public $consult_code;
    public $apply_date;
    public $customer_code="";
    public $consult_money;
    public $apply_city;
    public $audit_city;
    public $audit_date;
    public $status;
    public $remark;
    public $reject_remark;
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
            array('id,consult_code,apply_date,customer_code,consult_money,apply_city,
            audit_city,audit_date,status,remark,info_list,reject_remark','safe'),
            array('apply_date,apply_city,audit_city,consult_money','required'),
            array('consult_money','numerical','allowEmpty'=>false,'integerOnly'=>false),

            array('id','validateStaff'),
            array('id','validateID'),
            array('reject_remark','validateReject'),

            array('no_of_attm,docType,docMasterId,files,removeFileId','safe')
        );
	}

    public function validateReject($attribute, $params){
	    if($this->status==3&&empty($this->reject_remark)){
            $this->addError($attribute, "拒绝原因不能为空");
            return false;
        }
    }
    public function validateStaff($attribute, $params){
	    $bool = ConsultApplyList::staffCompanyForUsername($this);
        if (!$bool){
            $this->addError($attribute, "该账号未绑定员工，请与管理员联系");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_consult")
            ->where("id=:id and status=1 and audit_city=:city",array(":id"=>$id,":city"=>$this->apply_city))->queryRow();
        if(!$row){
            $this->addError($attribute, "咨询单异常，请刷新重试");
            return false;
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select *,
				docman$suffix.countdoc('consu',id) as consucountdoc
				 from acc_consult where id='".$index."' and audit_city='{$this->apply_city}' and status=1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
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
			$this->saveHistory();
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function saveHistory(){
        $uid = Yii::app()->user->id;
        Yii::app()->db->createCommand()->insert("acc_consult_history", array(
            "consult_id" => $this->id,
            "record_username" => $uid,
            "lcu" => $uid,
            "record_date" => date("Y-m-d H:i:s"),
            "record_status" => $this->status,
            "record_remark" => $this->reject_remark,
        ));
    }

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
				$sql = "update acc_consult set 
					status = :status,
					reject_remark = :reject_remark,
					audit_date = :audit_date,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':reject_remark')!==false)
			$command->bindParam(':reject_remark',$this->reject_remark,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);

		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':audit_date')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':audit_date',$date,PDO::PARAM_STR);
        }
		$command->execute();

		return true;
	}

	public function isReady(){
	    return true;
    }
}