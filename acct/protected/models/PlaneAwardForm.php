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
	public $old_pay_wage;

	public $plane_status=0;
	public $take_amt;
	public $old_take_amt;
	public $old_money_value;
	public $reject_txt;

	public $updateBool=true;

    public $info_list = array(
        array('id'=>0,
            'plane_id'=>0,
            'other_id'=>'',
            'other_num'=>'',
            'uflag'=>'Y',
        ),
    );

    public $infoDetail = array(
        array(
            'id'=>0,
            'planeId'=>0,
            'takeTxt'=>'',//提成说明
            'takeAmt'=>'',//提成金额
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
            'old_pay_wage'=>Yii::t('plane','old shall pay wages'),//原机制应发工资
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

            'reject_txt'=>Yii::t('plane','reject remark'),
            'plane_status'=>Yii::t('plane','plane status'),
            'take_amt'=>Yii::t('plane','take amt'),
            'old_take_amt'=>Yii::t('plane','old take amt'),
            'old_money_value'=>Yii::t('plane','old money value'),
            'take_txt'=>Yii::t('plane','take txt'),
            'take_money'=>Yii::t('plane','take money'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id,city,old_pay_wage,employee_id,employee_code,employee_name,entry_time,plane_date,plane_year,plane_month,info_list,infoDetail,
            reject_txt,plane_status,take_amt,old_take_amt,old_money_value,money_value','safe'),
			array('employee_id,id','required'),
            array('old_pay_wage','numerical','allowEmpty'=>false,'integerOnly'=>false),
            array('id','validateID'),
            array('info_list','validateList','on'=>array("edit")),
            array('infoDetail','validateDetail','on'=>array("edit")),
            array('reject_txt','required','on'=>array("reject")),
		);
	}

    public function validateDetail($attribute, $params){
        $list = array();
        $take_amt=$this->old_take_amt;
        if(!empty($this->infoDetail)){
            foreach ($this->infoDetail as $arr){
                if($arr["takeTxt"]!==""&&$arr["takeAmt"]!==""){
                    $arr["takeAmt"] = round($arr["takeAmt"],2);
                    $list[]=$arr;//$row['uflag']
                    if($arr["uflag"]!="D"){//不是刪除的時候
                        $take_amt+=$arr["takeAmt"];
                    }
                }
            }
        }
        $this->take_amt = $take_amt;
        $this->infoDetail = $list;
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
                    $list[]=$arr;//$row['uflag']
                    if($arr["uflag"]!="D"){//不是刪除的時候
                        $this->other_str[]=$row["set_name"]." ({$arr["other_num"]})";
                        $this->other_sum+=$arr["other_num"];
                    }
                }
            }
        }
        $this->other_str = empty($this->other_str)?"":implode(",",$this->other_str);
        $this->info_list = $list;
    }

    public function validateID($attribute, $params){
        $suffix = Yii::app()->params['envSuffix'];
        $cityList = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.entry_time")
            ->from("acc_plane a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.id=a.employee_id")
            ->where("a.id=:id and a.city in ({$cityList})",array(":id"=>$this->id))->queryRow();
        if($row){
            $this->plane_date = $row["plane_date"];
            $this->employee_id = $row["employee_id"];
            $this->plane_year = $row["plane_year"];
            $this->plane_month = $row["plane_month"];
            $this->entry_time = $row['entry_time'];
            $this->job_num = $row['job_num'];
            //$this->reject_txt = $row['reject_txt'];
            $this->plane_status = $row['plane_status'];
            if($this->getScenario()=="edit"){
                $this->setUpdateBool();
                if(!$this->updateBool){
                    $this->addError($attribute, "已超过两个月，无法修改");
                    return false;
                }else{
                    $this->getPlaneMoney();
                    $this->getPlaneYear();
                    $this->getOldTakeAmt();
                }
            }else{
                $this->job_num = $row['job_num'];
                $this->year_num = $row['year_num'];
                $this->money_num = $row['money_num'];
                $this->other_sum = $row['other_sum'];

                $this->money_value = $row['money_value'];
                $this->take_amt = $row['take_amt'];
                $this->plane_sum = $row['plane_sum'];
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
			$this->old_pay_wage = floatval($row['old_pay_wage']);
			$this->show_date = $this->plane_year."/".$this->plane_month;
			$this->city = $row['city'];
			$this->city_name = General::getCityName($row['city']);
			$this->job_id = PlaneSetJobForm::getPlaneName($row['job_id']);
			$this->job_num = $row['job_num'];
			$this->other_sum = floatval($row['other_sum']);
			$this->other_str = $row['other_str'];;
            $this->reject_txt = $row['reject_txt'];
            $this->plane_status = $row['plane_status'];

            $this->money_value=$row["money_value"]===null?null:floatval($row["money_value"]);
            $this->old_take_amt = floatval($row["old_take_amt"]);
            $this->take_amt = floatval($row["take_amt"]);
            $this->setUpdateBool();
            if($this->updateBool||!$bool){ //只能修改上个月及以后的数据(允许强制刷新)
                $this->getPlaneMoney();
                $this->getPlaneYear();
                $this->getOldTakeAmt();
                $this->savePlaneForm();
            }else{
                $this->money_num=$row["money_num"];
                $this->money_id=$row["money_id"];
                $this->old_money_value = $row['old_money_value'];
                $this->money_value=$this->money_value===null?$this->old_money_value:$row["money_value"];
                $this->year_num=$row["year_num"];
                $this->year_id=$row["year_id"];
                $this->year_month=$row["year_month"];
                $this->plane_sum=floatval($row["plane_sum"]);
            }

			if($bool){
                $this->getInfoList();
                $this->getDetailList();
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
            if(in_array($this->plane_status,array(0,3))){//草稿、拒绝状态
                $this->updateBool=true;
            }else{
                $this->updateBool=false;
            }
        }else{
            $this->updateBool=false;
        }
    }

	//保存表单内容(方便列表显示及查询)
	private function savePlaneForm(){
        $this->plane_sum = floatval($this->job_num)+floatval($this->year_num)+floatval($this->money_num)+floatval($this->other_sum);
        Yii::app()->db->createCommand()->update("acc_plane",array(
            "money_id"=>$this->money_id,
            "old_money_value"=>empty($this->old_money_value)?0:$this->old_money_value,
            "old_take_amt"=>empty($this->old_take_amt)?0:$this->old_take_amt,
            "money_num"=>$this->money_num,
            "year_num"=>$this->year_num,
            "year_id"=>$this->year_id,
            "year_month"=>$this->year_month,
            "plane_sum"=>$this->plane_sum,
        ),"id=:id",array(":id"=>$this->id));
    }

	//获取派单系统的做单提成
	private function getOldTakeAmt(){
        $this->old_take_amt = 10;
        $start = date("Y-m-d",strtotime("{$this->plane_year}-{$this->plane_month}-01"));
        $end = date("Y-m-t",strtotime($start));
        $staffList=array($this->employee_code);
        $model=new SystemU();
        $arr=$model->getSalaryMoney($start,$end,$staffList);
        if(!empty($arr["data"])){
            foreach ($arr["data"] as $row){
                $this->old_take_amt = isset($row["amt"])?round($row["amt"],2):0;
            }
        }
        $take_amt = Yii::app()->db->createCommand()->select("sum(take_amt)")
            ->from("acc_plane_detail")
            ->where("plane_id=:id",array(":id"=>$this->id))->queryScalar();
        $this->take_amt = empty($take_amt)?0:$take_amt;
        $this->take_amt+= $this->old_take_amt;
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
        $old_money=$service_money?floatval($service_money):0;
        $this->old_money_value=$old_money;
        $this->money_value = $this->money_value===null||$this->money_value===''?$old_money:floatval($this->money_value);
        $row = PlaneSetMoneyForm::getPlaneForDateAndMoney($this->plane_date,$this->money_value,$this->city);
        $this->money_num=$row["value"];
        $this->money_id=$row["id"];
    }

	//获取年资的奖金
	private function getPlaneYear(){
	    $planeYear = date("Y",strtotime($this->plane_date));
	    $planeMonth = date("n",strtotime($this->plane_date));
        $planeMonth = intval($planeMonth)+intval($planeYear)*12;
	    $entryYear = date("Y",strtotime($this->entry_time));
	    $entryMonth = date("n",strtotime($this->entry_time));
        $entryMonth = intval($entryMonth)+intval($entryYear)*12;
        $longMonth = floor(($planeMonth-$entryMonth)/12);
        $row = PlaneSetYearForm::getPlaneForDateAndMoney($this->plane_date,$longMonth,$this->city);
        $this->year_num=$row["value"];
        $this->year_id=$row["id"];
        $this->year_month=$longMonth;
    }

	//获取年资的奖金
	public function diffYearForDate($entryDate,$planeDate){
	    $planeYear = date("Y",strtotime($planeDate));
	    $planeMonth = date("n",strtotime($planeDate));
        $planeMonth = intval($planeMonth)+intval($planeYear)*12;
	    $entryYear = date("Y",strtotime($entryDate));
	    $entryMonth = date("n",strtotime($entryDate));
        $entryMonth = intval($entryMonth)+intval($entryYear)*12;
        $longMonth = floor(($planeMonth-$entryMonth)/12);
        return $longMonth;
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

	//获取提成调整补充说明列表
	private function getDetailList(){
        $rows = Yii::app()->db->createCommand()->select("id,plane_id,take_txt,take_amt")->from("acc_plane_detail")
            ->where("plane_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();
        if($rows){
            $this->infoDetail=array();
            foreach ($rows as $arr){
                $temp = array();
                $temp['id'] = $arr['id'];
                $temp['planeId'] = $this->id;
                $temp['takeTxt'] = $arr['take_txt'];
                $temp['takeAmt'] = $arr['take_amt'];
                $temp['uflag'] = "Y";
                $this->infoDetail[] = $temp;
            }
        }
    }
	
	public function saveData(){
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $this->saveDataForSql($connection);
            $this->saveInfo($connection);
            $this->saveDetail($connection);
            //$arr = array("bool"=>true,"message"=>"");
            $arr = $this->sendBsData();
            if($arr["bool"]){
                $transaction->commit();
            }else{
                $transaction->rollback();
            }
			return $arr;
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function sendBsData(){
        $saveArr= array("bool"=>true,"message"=>"");
        if($this->plane_status==12){
            $bsCurlModel = new BsCurlModel();
            $bsCurlModel->sendData = $this->getCurlData();
            $curlData = $bsCurlModel->sendBsCurl();
            if($curlData["code"]!=200){//curl异常，不继续执行
                $bsCurlModel->logError($curlData);
                $saveArr["bool"]=false;
                $saveArr["message"]=$curlData["message"];
            }
        }
        return $saveArr;
    }

	protected function getCurlData(){
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $bsStaffID = 0;
        $plane_sum = 0;
        $plane_sum+= empty($this->job_num)?0:floatval($this->job_num);
        $plane_sum+= empty($this->year_num)?0:floatval($this->year_num);
        $plane_sum+= empty($this->money_num)?0:floatval($this->money_num);
        $plane_sum+= empty($this->other_sum)?0:floatval($this->other_sum);
        $startDate = date("Y/m/01",strtotime("{$this->plane_year}-{$this->plane_month}-01"));
        $stopDate = date("Y/m/t",strtotime($startDate));
        $staffRow = Yii::app()->db->createCommand()->select("bs_staff_id")->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$this->employee_id))->queryRow();
        if($staffRow){
            $bsStaffID = $staffRow["bs_staff_id"];
        }
        $models[]=array(
            "staffId"=>$bsStaffID,
            "itemName"=>3,//直升机金额
            "startDate"=>$startDate,
            "stopDate"=>$stopDate,
            "numericVal"=>$plane_sum,
        );
        $models[]=array(
            "staffId"=>$bsStaffID,
            "itemName"=>4,//直升机做单金额
            "startDate"=>$startDate,
            "stopDate"=>$stopDate,
            "numericVal"=>empty($this->money_value)?0:floatval($this->money_value),
        );
        $models[]=array(
            "staffId"=>$bsStaffID,
            "itemName"=>5,//技术人员提成
            "startDate"=>$startDate,
            "stopDate"=>$stopDate,
            "numericVal"=>empty($this->take_amt)?0:floatval($this->take_amt),
        );
        return array(
            "presetSalarySubsetCode"=>"PresetSalarySubset1",
            "models"=>$models
        );
    }

    protected function saveInfo(&$connection){

        $uid = Yii::app()->user->id;

        if(!empty($this->info_list)){
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
    }

    protected function saveDetail(&$connection){

        $uid = Yii::app()->user->id;

        if(!empty($this->infoDetail)){
            foreach ($this->infoDetail as $row) {
                $sql = '';
                switch ($this->scenario) {
                    case 'delete':
                        $sql = "delete from acc_plane_detail where plane_id = :plane_id";
                        break;
                    case 'new':
                        if ($row['uflag']=='Y') {
                            $sql = "insert into acc_plane_detail(
									plane_id, take_txt, take_amt
								) values (
									:plane_id, :take_txt, :take_amt
								)";
                        }
                        break;
                    case 'edit':
                        switch ($row['uflag']) {
                            case 'D':
                                $sql = "delete from acc_plane_detail where id = :id and plane_id = :plane_id";
                                break;
                            case 'Y':
                                $sql = ($row['id']==0)
                                    ?
                                    "insert into acc_plane_detail(
										plane_id, take_txt, take_amt
									) values (
										:plane_id, :take_txt, :take_amt
									)"
                                    :
                                    "update acc_plane_detail set
										take_txt = :take_txt,
										take_amt = :take_amt
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
                    if (strpos($sql,':take_txt')!==false)
                        $command->bindParam(':take_txt',$row['takeTxt'],PDO::PARAM_INT);

                    if (strpos($sql,':take_amt')!==false) {
                        $take_amt = $row['takeAmt'];
                        $command->bindParam(':take_amt',$take_amt,PDO::PARAM_STR);
                    }
                    $command->execute();
                }
            }
        }
    }

	protected function saveDataForSql(&$connection){
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_plane where id = :id AND plane_status in (0,3)";
				break;
			case 'edit':
				$sql = "update acc_plane set
                      other_str = :other_str,
					  other_sum = :other_sum,
					  old_pay_wage = :old_pay_wage,
					  take_amt = :take_amt,
					  money_value = :money_value,
					  plane_status = :plane_status,
					  luu=:luu
                      where id = :id";
				break;
			case 'finish':
				$sql = "update acc_plane set
					  plane_status = 2,
					  luu=:luu
                      where id = :id AND plane_status=1";
				break;
			case 'reject':
				$sql = "update acc_plane set
					  plane_status = 3,
					  reject_txt = :reject_txt,
					  luu=:luu
                      where id = :id AND plane_status=1";
				break;
			case 'revoke':
				$sql = "update acc_plane set
					  plane_status = 0,
					  reject_txt = :reject_txt,
					  luu=:luu
                      where id = :id AND plane_status=2";
				break;
		}

        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':other_str')!==false)
			$command->bindParam(':other_str',$this->other_str,PDO::PARAM_STR);
		if (strpos($sql,':other_sum')!==false)
			$command->bindParam(':other_sum',$this->other_sum,PDO::PARAM_STR);
		if (strpos($sql,':old_pay_wage')!==false)
			$command->bindParam(':old_pay_wage',$this->old_pay_wage,PDO::PARAM_STR);
        if (strpos($sql,':take_amt')!==false){
            $this->take_amt = $this->take_amt===""?null:$this->take_amt;
            $command->bindParam(':take_amt',$this->take_amt,PDO::PARAM_STR);
        }
        if (strpos($sql,':money_value')!==false){
            $this->money_value = $this->money_value===""?null:$this->money_value;
            $command->bindParam(':money_value',$this->money_value,PDO::PARAM_STR);
        }
        if (strpos($sql,':plane_status')!==false){
            $this->plane_status = empty($this->plane_status)?0:$this->plane_status;
            $command->bindParam(':plane_status',$this->plane_status,PDO::PARAM_STR);
        }
        if (strpos($sql,':reject_txt')!==false)
            $command->bindParam(':reject_txt',$this->reject_txt,PDO::PARAM_STR);

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

    public static function validateAjaxPayer($data,$year,$month){
	    $html = "";
        if(!empty($data)){
            $staffList = self::getStaffList("code",$year,$month);
            foreach ($data as $row){
                if(key_exists($row["code"],$staffList)){
                    $num =$staffList[$row["code"]]["id"];
                    $row["money"]=is_numeric($row["money"])?round($row["money"],2):0;
                    $html.= "<tr>";
                    $html.= "<td>".TbHtml::checkBox("test[check][{$num}]",false,array('class'=>'checkOne'))."</td>";
                    $html.= "<td>";
                    $html.=TbHtml::textField("test[{$num}][code]",$row["code"],array("readonly"=>true));
                    $html.="</td>";
                    $html.= "<td>".TbHtml::textField("test[{$num}][name]",$staffList[$row["code"]]["name"],array("readonly"=>true))."</td>";
                    $html.= "<td>".TbHtml::textField("test[{$num}][money]",$row["money"],array("readonly"=>true))."</td>";
                    $html.= "</tr>";
                }else{
                    $html.= "<tr class='danger'>";
                    $html.= "<td>&nbsp;</td>";
                    $html.= "<td>".$row["code"]."</td>";
                    $html.= "<td>该员工未参加直升机</td>";
                    $html.= "<td>".$row["money"]."</td>";
                    $html.= "</tr>";
                }
            }
        }
        return array("html"=>$html);
    }

    public function pasteSave($list,$paste){
        $year = key_exists("plane_year",$paste)?$paste["plane_year"]:2022;
        $month = key_exists("plane_month",$paste)?$paste["plane_month"]:12;
        $staffList = self::getStaffList("id",$year,$month);
        $checkList = key_exists("check",$list)?$list["check"]:array();
        $uid = Yii::app()->user->id;
        if(!empty($checkList)){
            foreach ($checkList as $checkId=>$value){
                if($value==1&&key_exists($checkId,$staffList)&&key_exists($checkId,$list)){
                    $money = $list[$checkId]["money"];
                    $money = is_numeric($money)?round($money,2):0;
                    Yii::app()->db->createCommand()->update("acc_plane",array(
                        "old_pay_wage"=>$money,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$checkId));
                }
            }
        }
    }

    private static function getStaffList($keyStr,$year,$month){
        $cityList = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $staffRows = Yii::app()->db->createCommand()
            ->select("a.id,b.code,b.name,b.entry_time")
            ->from("acc_plane a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.id=a.employee_id")
            ->where("a.plane_year=:year and plane_month=:month and a.city in ({$cityList})",
                array(":year"=>$year,":month"=>$month))->queryAll();
        if($staffRows){
            foreach ($staffRows as $row){
                $list[$row[$keyStr]]=$row;
            }
        }
        return $list;
    }
}