<?php

class SalesGroupSetForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $employee_type;
	public $group_staff_name;
	public $start_date;
	public $end_date;
    public $info_list = array(
        array('id'=>0,
            'setID'=>0,
            'employeeID'=>'',
            'employeeName'=>'',
            'employeeType'=>'',
            'uflag'=>'Y',
        ),
    );

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels(){
		return array(
            'start_date'=>Yii::t('group','start date'),
            'end_date'=>Yii::t('group','end date'),
            'employee_id'=>Yii::t('group','staff'),
            'employee_code'=>Yii::t('group','employee code'),
            'employee_name'=>Yii::t('group','employee name'),
            'employee_type'=>Yii::t('group','common type'),
            'group_staff_name'=>Yii::t('group','manage staff'),
            'employeeID'=>Yii::t('group','employee name'),
            'employeeName'=>Yii::t('group','employee name'),
            'employeeType'=>"员工类型",
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id,employee_id,start_date,end_date,employee_type,info_list','safe'),
			array('employee_id,start_date,end_date,employee_type','required'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            //array('id','validateID','on'=>array("delete")),
            array('end_date','validateDate','on'=>array("edit","new")),
            array('info_list','validateList','on'=>array("edit","new")),
		);
	}

    public function validateDate($attribute, $params){
        if(strtotime($this->start_date)>strtotime($this->end_date)){
            $this->addError($attribute, "开始时间不能大于结束时间");
        }
        $startDate = date("Y-m",strtotime($this->start_date));
        $endDate = date("Y-m",strtotime($this->end_date));
        $id = empty($this->id)?0:$this->id;
        $employee_id = empty($this->employee_id)?0:$this->employee_id;
        //DATE_FORMAT(a.start_date,'%Y-%m-01') BETWEEN '{$startDate}' and '{$endDate}'
        $sql = "select * from acc_group_set a where a.employee_id={$employee_id} and id!={$id} AND !(DATE_FORMAT(a.start_date,'%Y-%m')>'{$endDate}' or DATE_FORMAT(a.end_date,'%Y-%m')<'{$startDate}')";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $this->addError($attribute, "该时间段内已有配置：".$row["id"]);
        }
    }

    public function validateList($attribute, $params){
        $this->group_staff_name=null;
        $branchText = array();
        $updateArr = array();
        $delArr = array();
        if(!empty($this->info_list)){
            foreach ($this->info_list as $row){
                if($row["uflag"]=="D"){
                    $delArr[] = $row;
                }elseif (!empty($row["employeeID"])){
                    $branchText[]=$row["employeeName"];
                    $updateArr[]=$row;
                }
            }
        }
        if(!empty($branchText)){
            $this->group_staff_name = implode(";",$branchText);
        }
        if(empty($this->getErrors())){
            $this->info_list = array_merge($updateArr,$delArr);
        }
    }

    public static function getGroupTypeList($key='',$bool=false){
        $list = array(
            1=>"老销售×0.5% + 1年内新销售×1%",
            2=>"固定1%",
        );
        if($bool){
            $key="".$key;
            if(key_exists($key,$list)){
                return $list[$key];
            }else{
                return $key;
            }
        }
        return $list;
    }

    public static function getEmployeeNameByID($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $record = Yii::app()->db->createCommand()->select("id,code,name")
            ->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$employee_id))->queryRow();
        if($record){
            return $record['name']." ({$record['code']})";
        }
        return $employee_id;
    }

    public static function getManageIDList($year,$month){
        $date = date("Y-m",strtotime("{$year}/{$month}/01"));
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.employee_id")
            ->from("acc_group_set b")
            ->where("DATE_FORMAT(b.start_date,'%Y-%m')<='{$date}' and DATE_FORMAT(b.end_date,'%Y-%m')>='{$date}' ")->queryAll();
        $list=array();
        if($rows){
            foreach ($rows as $row){
                $list[]=$row["employee_id"];
            }
        }
        return empty($list)?array(0):$list;
    }

	public function retrieveData($index){
        $city = Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from acc_group_set a where a.id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->employee_type = $row['employee_type'];
			$this->start_date = General::toDate($row['start_date']);
			$this->end_date = General::toDate($row['end_date']);
            $rows = Yii::app()->db->createCommand()->select("a.*,b.code,b.name")
                ->from("acc_group_set_info a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("a.set_id=:id",array(":id"=>$index))->order("id asc")->queryAll();
            if($rows){
                $this->info_list = array();
                foreach ($rows as $arr){
                    $temp = array();
                    $temp['id'] = $arr['id'];
                    $temp['setID'] = $index;
                    $temp['employeeID'] = $arr['employee_id'];
                    $temp['employeeName'] = $arr['name']." ({$arr['code']})";
                    $temp['employeeType'] = $arr['employee_type'];
                    $temp['uflag'] = "Y";
                    $this->info_list[] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}
	
	public function saveData(){
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
			$this->saveInfo($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveInfo(&$connection){

        $uid = Yii::app()->user->id;

        foreach ($this->info_list as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from acc_group_set_info where set_id = :set_id";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_group_set_info(
									set_id, employee_id, employee_type
								) values (
									:set_id, :employee_id, :employee_type
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_group_set_info where id = :id";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_group_set_info(
										set_id, employee_id, employee_type
									) values (
										:set_id, :employee_id, :employee_type
									)"
                                :
                                "update acc_group_set_info set
										employee_id = :employee_id,
										employee_type = :employee_type
									where id = :id and set_id=:set_id
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
                if (strpos($sql,':employee_id')!==false) {
                    $command->bindParam(':employee_id',$row['employeeID'],PDO::PARAM_STR);
                }
                if (strpos($sql,':employee_type')!==false) {
                    $command->bindParam(':employee_type',$row['employeeType'],PDO::PARAM_STR);
                }
                $command->execute();
            }
        }
    }

	protected function saveDataForSql(&$connection){
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_group_set where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_group_set(
						start_date,end_date, employee_id, employee_type, group_staff_name, lcu, lcd) values (
						:start_date, :end_date, :employee_id,:employee_type,:group_staff_name, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_group_set set 
					start_date = :start_date,
					end_date = :end_date,
					employee_id = :employee_id,
					employee_type = :employee_type,
					group_staff_name = :group_staff_name,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_date')!==false)
			$command->bindParam(':start_date',$this->start_date,PDO::PARAM_STR);
		if (strpos($sql,':end_date')!==false)
			$command->bindParam(':end_date',$this->end_date,PDO::PARAM_STR);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_STR);
		if (strpos($sql,':employee_type')!==false)
			$command->bindParam(':employee_type',$this->employee_type,PDO::PARAM_STR);
		if (strpos($sql,':group_staff_name')!==false)
			$command->bindParam(':group_staff_name',$this->group_staff_name,PDO::PARAM_STR);

		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        if ($this->scenario=='new')
            $this->id = Yii::app()->db->getLastInsertID();

		return true;
	}

	public function isReadOnly(){
	    return $this->getScenario()=='view';
    }
}