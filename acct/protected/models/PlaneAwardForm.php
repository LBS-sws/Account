<?php

class PlaneAwardForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $entry_time;
	public $plane_year;
	public $plane_month;
	public $plane_date;
	public $job_id;
	public $job_num;
	public $money_id;
	public $money_value;
	public $money_num;
	public $year_id;
	public $year_month;
	public $year_num;
	public $other_sum;
	public $other_str;
	public $plane_sum;
	public $city;
	public $city_name;
	public $show_date;

	public $updateBool=true;

    public $info_list = array(
        array('id'=>0,
            'plane_id'=>0,
            'other_id'=>'',
            'other_num'=>'',
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
            'plane_date'=>Yii::t('plane','plane date'),
            'show_date'=>Yii::t('plane','plane date'),
            'employee_code'=>Yii::t('plane','employee code'),
            'employee_name'=>Yii::t('plane','employee name'),
            'entry_time'=>Yii::t('plane','entry time'),//入职日期
            'city'=>Yii::t('plane','city'),
            'city_name'=>Yii::t('plane','city'),
            'job_id'=>Yii::t('plane','job value'),
            'money_value'=>Yii::t('plane','money value'),
            'year_month'=>Yii::t('plane','year month'),
            'job_num'=>Yii::t('plane','job num'),
            'money_num'=>Yii::t('plane','money num'),
            'year_num'=>Yii::t('plane','year num'),
            'other_sum'=>Yii::t('plane','other sum'),
            'plane_sum'=>Yii::t('plane','plane sum'),
            'info_list'=>Yii::t('plane','other list'),
            'value_name'=>Yii::t('plane','Other Name'),
            'value_money'=>Yii::t('plane','other num'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id,city,employee_id,employee_code,employee_name,entry_time,plane_date,plane_year,plane_month,info_list','safe'),
			array('employee_id,id','required'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID'),
            array('info_list','validateList','on'=>array("edit")),
		);
	}

    public function validateList($attribute, $params){
        $list = array();
        $this->other_str=array();
        $this->other_sum=0;
        if(!empty($this->info_list)){
            foreach ($this->info_list as $arr){
                $row = Yii::app()->db->createCommand()->select("id,set_name")->from("acc_plane_set_other")
                ->where("id=:id",array(":id"=>$arr["other_id"]))->queryRow();
                if($row&&!empty($arr["other_num"])){
                    $arr["other_num"] = round($arr["other_num"],2);
                    $this->other_str[]=$row["set_name"]." ({$arr["other_num"]})";
                    $this->other_sum+=$arr["other_num"];
                    $list[]=$arr;
                }
            }
        }
        $this->other_str = implode(",",$this->other_str);
        if(empty($list)){
            $this->addError($attribute, "配置详情不能为空");
            return false;
        }else{
            $this->info_list = $list;
        }
    }

    public function validateID($attribute, $params){
        $cityList = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()->select("id,plane_date")->from("acc_plane")
            ->where("id=:id and city in ({$cityList})",array(":id"=>$this->id))->queryRow();
        if($row){
            $this->plane_date = $row["plane_date"];
            $this->setUpdateBool();
            if(!$this->updateBool){
                $this->addError($attribute, "已超过两个月，无法修改");
                return false;
            }
            if($this->getScenario()=="delete"){
                $row = Yii::app()->db->createCommand()->select("id")->from("acc_plane_info")
                    ->where("plane_id=:id",array(":id"=>$this->id))->queryRow();
                if($row){
                    $this->addError($attribute, "该员工已设置直升机杂项，无法删除");
                }
            }
        }else{
            $this->addError($attribute, "该员工信息异常，请刷新重试");
        }
    }

	public function retrieveData($index,$bool=true){
        if($bool){
            $cityList = Yii::app()->user->city_allow();
            $sqlEpr = " and a.city in ({$cityList})";
        }else{
            $sqlEpr = "";
        }
		$suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.code,b.name,b.entry_time")
            ->from("acc_plane a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.id=a.employee_id")
            ->where("a.id=:id {$sqlEpr}",array(":id"=>$index))->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->employee_code = $row['code'];
			$this->employee_name = $row['name'];
			$this->entry_time = $row['entry_time'];
			$this->plane_year = $row['plane_year'];
			$this->plane_month = $row['plane_month'];
			$this->plane_date = $row['plane_date'];
			$this->show_date = $this->plane_year."/".$this->plane_month;
			$this->city = $row['city'];
			$this->city_name = General::getCityName($row['city']);
			$this->job_id = PlaneSetJobForm::getPlaneName($row['job_id']);
			$this->job_num = $row['job_num'];
			$this->other_sum = floatval($row['other_sum']);
			$this->other_str = $row['other_str'];
            $this->setUpdateBool();
            if($this->updateBool||!$bool){ //只能修改上个月及以后的数据(允许强制刷新)
                $this->getPlaneMoney();
                $this->getPlaneYear();
                $this->savePlaneForm();
            }else{
                $this->money_num=$row["money_num"];
                $this->money_id=$row["money_id"];
                $this->money_value=$row["money_value"];
                $this->year_num=$row["year_num"];
                $this->year_id=$row["year_id"];
                $this->year_month=$row["year_month"];
                $this->plane_sum=floatval($row["plane_sum"]);
            }

			if($bool){
                $this->getInfoList();
            }
            return true;
		}else{
		    return false;
        }
	}

	private function setUpdateBool(){
        $planeTime = strtotime($this->plane_date);
        $ageTime = date("Y-m-01");
        $ageTime = strtotime("$ageTime - 1 months");
        if($ageTime<=$planeTime) { //只能修改上个月及以后的数据(允许强制刷新)
            $this->updateBool=true;
        }else{
            $this->updateBool=false;
        }
    }

	//保存表单内容(方便列表显示及查询)
	private function savePlaneForm(){
        $this->plane_sum = floatval($this->job_num)+floatval($this->year_num)+floatval($this->money_num)+floatval($this->other_sum);
        Yii::app()->db->createCommand()->update("acc_plane",array(
            "money_id"=>$this->money_id,
            "money_value"=>$this->money_value,
            "money_num"=>$this->money_num,
            "year_num"=>$this->year_num,
            "year_id"=>$this->year_id,
            "year_month"=>$this->year_month,
            "plane_sum"=>$this->plane_sum,
        ),"id=:id",array(":id"=>$this->id));
    }

	//获取做单金额的奖金
	private function getPlaneMoney(){
        $suffix = Yii::app()->params['envSuffix'];
        $service_money = Yii::app()->db->createCommand()->select("service_money")
            ->from("operation{$suffix}.opr_service_money")
            ->where("employee_id=:id and service_year=:year and service_month=:month",array(
                ":id"=>$this->employee_id,
                ":year"=>$this->plane_year,
                ":month"=>$this->plane_month
            ))->queryScalar();
        $service_money=$service_money?floatval($service_money):0;
        $row = PlaneSetMoneyForm::getPlaneForDateAndMoney($this->plane_date,$service_money);
        $this->money_num=$row["value"];
        $this->money_id=$row["id"];
        $this->money_value=$service_money;
    }

	//获取做单金额的奖金
	private function getPlaneYear(){
	    $planeYear = date("Y",strtotime($this->plane_date));
	    $planeMonth = date("n",strtotime($this->plane_date));
        $planeMonth = intval($planeMonth)+intval($planeYear)*12;
	    $entryYear = date("Y",strtotime($this->entry_time));
	    $entryMonth = date("n",strtotime($this->entry_time));
        $entryMonth = intval($entryMonth)+intval($entryYear)*12;
        $longMonth = floor(($planeMonth-$entryMonth)/12);
        $row = PlaneSetYearForm::getPlaneForDateAndMoney($this->plane_date,$longMonth);
        $this->year_num=$row["value"];
        $this->year_id=$row["id"];
        $this->year_month=$longMonth;
    }

	//获取杂项奖金列表
	private function getInfoList(){
        $rows = Yii::app()->db->createCommand()->select("id,plane_id,other_id,other_num")->from("acc_plane_info")
            ->where("plane_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();
        if($rows){
            $this->info_list=array();
            foreach ($rows as $arr){
                $temp = array();
                $temp['id'] = $arr['id'];
                $temp['plane_id'] = $this->id;
                $temp['other_id'] = $arr['other_id'];
                $temp['other_num'] = $arr['other_num'];
                $temp['uflag'] = "Y";
                $this->info_list[] = $temp;
            }
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
                    $sql = "delete from acc_plane_info where plane_id = :plane_id";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_plane_info(
									plane_id, other_id, other_num
								) values (
									:plane_id, :other_id, :other_num
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_plane_info where id = :id";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_plane_info(
										plane_id, other_id, other_num
									) values (
										:plane_id, :other_id, :other_num
									)"
                                :
                                "update acc_plane_info set
										other_id = :other_id,
										other_num = :other_num
									where id = :id and plane_id=:plane_id
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
                if (strpos($sql,':plane_id')!==false)
                    $command->bindParam(':plane_id',$this->id,PDO::PARAM_INT);
                if (strpos($sql,':other_id')!==false)
                    $command->bindParam(':other_id',$row['other_id'],PDO::PARAM_INT);

                if (strpos($sql,':other_num')!==false) {
                    $other_num = $row['other_num'];
                    $command->bindParam(':other_num',$other_num,PDO::PARAM_STR);
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
				$sql = "delete from acc_plane where id = :id";
				break;
			case 'edit':
				$sql = "update acc_plane set
                      other_str = :other_str,
					  other_sum = :other_sum,
					  luu=:luu
                      where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':other_str')!==false)
			$command->bindParam(':other_str',$this->other_str,PDO::PARAM_STR);
		if (strpos($sql,':other_sum')!==false)
			$command->bindParam(':other_sum',$this->other_sum,PDO::PARAM_STR);

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
	    return $this->getScenario()=='view'||!$this->updateBool;
    }
}