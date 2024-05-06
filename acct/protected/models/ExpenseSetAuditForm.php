<?php

class ExpenseSetAuditForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $employee_name;
	public $audit_user_str;
    public $detail = array(
        array('id'=>0,
            'set_id'=>0,
            'audit_user'=>'',
            'audit_tag'=>'',
            'amt_bool'=>0,
            'amt_min'=>0,
            'amt_max'=>0,
            'z_index'=>0,
            'uflag'=>'N',
        ),
    );

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'employee_id'=>Yii::t('give','employee name'),
            'city_name'=>Yii::t('give','City'),
            'audit_user_str'=>Yii::t('give','appoint audit'),
            'audit_user'=>Yii::t('give','appoint audit'),
            'audit_tag'=>Yii::t('give','appoint tag'),
            'amt_bool'=>Yii::t('give','amt bool'),
            'amt_min'=>Yii::t('give','amt min'),
            'amt_max'=>Yii::t('give','amt max'),
            'z_index'=>Yii::t('give','z_index'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,employee_id,employee_name,audit_user_str,detail','safe'),
            array('employee_id','required'),
            array('employee_id','validateName'),
            array('detail','validateDetail'),
		);
	}

    public function validateDetail($attribute, $params){
	    $list = array();
	    $auditUser = self::getAppointAuditUserList();
	    $userStr = array();
	    foreach ($this->detail as $row){
            if($row["uflag"]=="D"){
                $list[]=$row;
            }else{
                if(key_exists($row["audit_user"],$auditUser)){
                    $userStr[] = $auditUser[$row["audit_user"]];
                    $list[]=$row;
                }
            }
        }
        if(empty($userStr)){
            $message = "审核人不能为空";
            $this->addError($attribute,$message);
        }
        $this->audit_user_str = implode(",",$userStr);
        $this->detail=$list;
    }

    public function validateName($attribute, $params){
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_set_audit")
            ->where('employee_id=:employee_id and id!=:id',
                array(':employee_id'=>$this->employee_id,':id'=>$id))->queryRow();
        if($row){
            $message = "该员工已被指定，指定id：".$row["id"];
            $this->addError($attribute,$message);
        }
    }

    public static function getAppointAuditTagList(){
        return array(
            "部门负责人"=>"部门负责人",
            "财务部"=>"财务部",
            "总经理"=>"总经理",
            "额外审核人"=>"额外审核人",
        );
    }

    public static function getAmtBoolList(){
        return array(
            0=>"不限制金额",
            1=>"限制金额"
        );
    }

    public static function getAppointAuditUserList($username=""){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $city_allow = Yii::app()->user->city_allow();
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("b.username,b.disp_name")
            ->from("security{$suffix}.sec_user_access a")
            ->leftJoin("security{$suffix}.sec_user b","a.username=b.username")
            ->where("a.system_id='{$systemId}' and 
            (a.username=:username or (
                b.city in ({$city_allow}) and (a.a_read_write like '%DE03%')
            ))
            ",array(
                ":username"=>$username
            ))
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["username"]] = $row["disp_name"];
            }
        }
        return $list;
    }

    public static function getEmployeeName($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("code,name")
            ->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$employee_id))
            ->queryRow();
        return $row?$row["name"]." ({$row["code"]})":$employee_id;
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from acc_set_audit where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->employee_name = self::getEmployeeName($row['employee_id']);
			$this->audit_user_str = $row['audit_user_str'];
            $sql = "select * from acc_set_audit_info where set_id=".$index." ";
            $classRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($classRows){
                $this->detail=array();
                foreach ($classRows as $classRow){
                    $temp = array();
                    $temp["id"] = $classRow["id"];
                    $temp["set_id"] = $classRow["set_id"];
                    $temp["audit_user"] = $classRow["audit_user"];
                    $temp["audit_tag"] = $classRow["audit_tag"];
                    $temp["amt_bool"] = $classRow["amt_bool"];
                    $temp["amt_min"] = empty($classRow["amt_min"])?"":floatval($classRow["amt_min"]);
                    $temp["amt_max"] = empty($classRow["amt_max"])?"":floatval($classRow["amt_max"]);
                    $temp["z_index"] = $classRow["z_index"];
                    $temp['uflag'] = 'N';
                    $this->detail[] = $temp;
                }
            }
		}
		return true;
	}

	public function copyData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from acc_set_audit where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
            $sql = "select * from acc_set_audit_info where set_id=".$index." ";
            $classRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($classRows){
                $this->detail=array();
                foreach ($classRows as $classRow){
                    $temp = array();
                    $temp["id"] = "";
                    $temp["set_id"] = "";
                    $temp["audit_user"] = $classRow["audit_user"];
                    $temp["audit_tag"] = $classRow["audit_tag"];
                    $temp["amt_bool"] = $classRow["amt_bool"];
                    $temp["amt_min"] = empty($classRow["amt_min"])?"":floatval($classRow["amt_min"]);
                    $temp["amt_max"] = empty($classRow["amt_max"])?"":floatval($classRow["amt_max"]);
                    $temp["z_index"] = $classRow["z_index"];
                    $temp['uflag'] = 'Y';
                    $this->detail[] = $temp;
                }
            }
		}
        return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
            $this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			var_dump($e);
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function saveDetail(&$connection)
    {
        $uid = Yii::app()->user->id;

        foreach ($this->detail as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from acc_set_audit_info where set_id = :set_id";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_set_audit_info(
									set_id, audit_user, audit_tag, amt_bool, amt_min, amt_max, z_index,lcu
								) values (
									:set_id,:audit_user,:audit_tag,:amt_bool,:amt_min,:amt_max,:z_index,:lcu
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_set_audit_info where id = :id";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_set_audit_info(
									    set_id, audit_user, audit_tag, amt_bool, amt_min, amt_max, z_index,lcu
									) values (
									    :set_id,:audit_user,:audit_tag,:amt_bool,:amt_min,:amt_max,:z_index,:lcu
									)"
                                :
                                "update acc_set_audit_info set
										audit_user = :audit_user, 
										audit_tag = :audit_tag, 
										amt_bool = :amt_bool, 
										amt_min = :amt_min, 
										amt_max = :amt_max, 
										z_index = :z_index,
										luu = :luu 
									where id = :id
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
                if (strpos($sql,':set_id')!==false)
                    $command->bindParam(':set_id',$this->id,PDO::PARAM_INT);
                if (strpos($sql,':audit_user')!==false)
                    $command->bindParam(':audit_user',$row['audit_user'],PDO::PARAM_STR);
                if (strpos($sql,':audit_tag')!==false)
                    $command->bindParam(':audit_tag',$row['audit_tag'],PDO::PARAM_STR);
                if (strpos($sql,':amt_bool')!==false)
                    $command->bindParam(':amt_bool',$row['amt_bool'],PDO::PARAM_INT);
                if (strpos($sql,':amt_min')!==false){
                    $row['amt_min'] = empty($row['amt_min'])?0:$row['amt_min'];
                    $command->bindParam(':amt_min',$row['amt_min'],PDO::PARAM_INT);
                }
                if (strpos($sql,':amt_max')!==false){
                    $row['amt_max'] = empty($row['amt_max'])?0:$row['amt_max'];
                    $command->bindParam(':amt_max',$row['amt_max'],PDO::PARAM_INT);
                }
                if (strpos($sql,':z_index')!==false){
                    $row['z_index'] = empty($row['z_index'])?0:$row['z_index'];
                    $command->bindParam(':z_index',$row['z_index'],PDO::PARAM_INT);
                }
                if (strpos($sql,':z_index')!==false){
                    $row['z_index'] = empty($row['z_index'])?0:$row['z_index'];
                    $command->bindParam(':z_index',$row['z_index'],PDO::PARAM_INT);
                }
                if (strpos($sql,':luu')!==false)
                    $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                if (strpos($sql,':lcu')!==false)
                    $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                $command->execute();
            }
        }
        return true;
    }

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_set_audit where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_set_audit(
						employee_id, audit_user_str, lcu) values (
						:employee_id, :audit_user_str, :lcu)";
				break;
			case 'edit':
				$sql = "update acc_set_audit set 
					audit_user_str = :audit_user_str,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_STR);
		if (strpos($sql,':audit_user_str')!==false)
			$command->bindParam(':audit_user_str',$this->audit_user_str,PDO::PARAM_INT);

		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
        }
		return true;
	}

	public function isOccupied($index) {
        $employee_id = $this->employee_id;
		$sql = "select a.id from acc_expense a where a.status_type NOT in (0,0) and a.employee_id='{$employee_id}'";
		$workRow = Yii::app()->db->createCommand($sql)->queryRow();
		if($workRow){
		    return true;//不允许删除
        }else{
            return false;
        }
	}
}
