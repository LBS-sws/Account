<?php
class PerformanceBonusForm extends CFormModel
{
	public $id = 0;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $city;
    public $year_no;
    public $quarter_no;
    public $new_amount;
    public $bonus_amount;
    public $new_json;
    public $bonus_json;
    public $status_type;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'city'=>Yii::t('misc','City'),
			'employee_code'=>Yii::t('service','employee code'),
			'employee_name'=>Yii::t('service','employee name'),
			'status_type'=>Yii::t('service','status type'),
			'quarter_no'=>Yii::t('service','quarter no'),
			'new_amount'=>Yii::t('service','new amount'),
			'bonus_amount'=>Yii::t('service','bonus amount'),
		);
	}

	public function rules()
	{
		return array(
			array('id,employee_id','safe'),
			array('employee_id','required'),
			array('employee_id','validateEmployee'),
		);
	}

	public function validateEmployee($attribute, $params) {
	    if(!$this->getPerBonusForEmployeeID($this->employee_id)){
            $this->addError($attribute, "员工不存在，请与管理员联系");
        }
	}

	private function getPerBonusForEmployeeID($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $session = Yii::app()->session;
        if (isset($session['performanceBonus_xs08']) && !empty($session['performanceBonus_xs08'])) {
            $criteria = $session['performanceBonus_xs08'];
            $this->year_no = $criteria["year_no"];
            $this->quarter_no = $criteria["quarter_no"];
        }
        if(empty($this->year_no)||!is_numeric($this->year_no)){
            $this->year_no = date("Y",strtotime("-3 months"));
        }
        if(empty($this->quarter_no)||!is_numeric($this->quarter_no)){
            $month = date("n",strtotime("-3 months"));
            $this->quarter_no = ceil($month/3);
        }
        $sql = "select code,name,city from hr{$suffix}.hr_employee where id=$employee_id";
        $staffRow = Yii::app()->db->createCommand($sql)->queryRow();
        if($staffRow){
            $this->employee_code = $staffRow["code"];
            $this->employee_name = $staffRow["name"];
            $this->city = $staffRow["city"];
        }else{
            return false;
        }
        $employee_id = !empty($employee_id)&&is_numeric($employee_id)?intval($employee_id):0;
        $sql = "select * from acc_performance_bonus where employee_id=$employee_id AND year_no={$this->year_no} AND quarter_no={$this->quarter_no}";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->employee_id = $row["employee_id"];
            $this->status_type = $row["status_type"];
            $this->new_amount = floatval($row["new_amount"]);
            $this->bonus_amount = floatval($row["bonus_amount"]);
            $this->new_json = empty($row["new_json"])?array():json_decode($row["new_json"],true);
            $this->bonus_json = empty($row["bonus_json"])?array():json_decode($row["bonus_json"],true);
        }else{
            $this->employee_id = $employee_id;
            $this->status_type = 0;
            Yii::app()->db->createCommand()->insert("acc_performance_bonus",array(
                "employee_id"=>$employee_id,
                "year_no"=>$this->year_no,
                "quarter_no"=>$this->quarter_no,
                "lcu"=>Yii::app()->user->id,
                "city"=>$this->city
            ));
            $this->id = Yii::app()->db->getLastInsertID();
        }
        if($this->status_type!=1){
            $this->new_amount = 0;
            $this->bonus_amount = 0;
            $this->new_json = array();
            $minMonth = ($this->quarter_no-1)*3 + 1;
            $monthList = "".$minMonth;
            for ($i=$minMonth+1;$i<$minMonth+3;$i++){
                $monthList.=",".$i;
            }
            $this->bonus_json = PerformanceSetForm::getBonusArrForYearMonth($this->year_no,$minMonth);
            $rows = Yii::app()->db->createCommand()->select("b.year_no,b.month_no,a.new_money,a.edit_money")
                ->from("acc_service_comm_dtl a")
            ->leftJoin("acc_service_comm_hdr b","a.hdr_id=b.id")
            ->where("b.year_no={$this->year_no} and b.month_no in ({$monthList}) and b.employee_code=:code",array(
                ":code"=>$this->employee_code
            ))->order("b.month_no asc")->queryAll();
            if($rows){
                foreach ($rows as $row){
                    if($row["year_no"]==2025&&$row["month_no"]==1){
                        $row["new_money"]=0;
                        $row["edit_money"]=0;
                    }
                    $row["new_money"]= empty($row["new_money"])?0:floatval($row["new_money"]);
                    $row["edit_money"]= empty($row["edit_money"])?0:floatval($row["edit_money"]);
                    $this->new_amount+= $row["new_money"];
                    $this->new_amount+= $row["edit_money"];
                    $this->new_json[]=$row;
                }
            }
            $this->computeBonusAmount();
        }
        return true;
    }

    private function computeBonusAmount(){
	    if(!empty($this->bonus_json)){
	        foreach ($this->bonus_json["LE"] as $item){
	            if($this->new_amount<$item["new_amount"]){
	                $this->bonus_amount = $item["bonus_amount"];
	                return $item["bonus_amount"];
                }
            }
            for ($i = count($this->bonus_json["GT"])-1;$i>=0;$i--){
                if($this->new_amount>=$this->bonus_json["GT"][$i]["new_amount"]){
                    $this->bonus_amount = $this->bonus_json["GT"][$i]["bonus_amount"];
                    return $this->bonus_json["GT"][$i]["bonus_amount"];
                }
            }
        }
        $this->bonus_amount = 0;
        return 0;
    }
	
	public function retrieveData($employee_id)
	{
		$city = Yii::app()->user->city_allow();
		if ($this->getPerBonusForEmployeeID($employee_id)) {
			return true;
		} else {
			return false;
		}
		
	}

    public function batchSave($list){
        $saveArr= array("bool"=>true,"message"=>"");
        if(!empty($list)){
            $connection = Yii::app()->db;
            $transaction=$connection->beginTransaction();
            $curlData=array(
                "presetSalarySubsetCode"=>"PresetSalarySubset1",
                "models"=>array()
            );
            foreach ($list as $employee_id){
                $this->retrieveData($employee_id);
                if($this->status_type!=1){
                    $this->status_type=1;
                    $temp = $this->getCurlDataModels();
                    $this->saveHeader($connection);
                    $curlData["models"] = array_merge($curlData["models"],$temp);
                }
            }
            $bsCurlModel = new BsCurlModel();
            $bsCurlModel->sendData = $curlData;
            $curlData = $bsCurlModel->sendBsCurl();
            if($curlData["code"]!=200){//curl异常，不继续执行
                $bsCurlModel->logError($curlData);
                $saveArr["bool"]=false;
                $saveArr["message"]=$curlData["message"];
                $transaction->rollback();
            }else{
                $transaction->commit();
            }
        }
        return $saveArr;
    }
	
	public function saveData()
	{

		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
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
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

    protected function sendBsData(){
        $saveArr= array("bool"=>true,"message"=>"");
        if($this->getScenario()=="edit"){
            $bsCurlModel = new BsCurlModel();
            $bsCurlModel->sendData = array(
                "presetSalarySubsetCode"=>"PresetSalarySubset1",
                "models"=>$this->getCurlDataModels()
            );
            $curlData = $bsCurlModel->sendBsCurl();
            if($curlData["code"]!=200){//curl异常，不继续执行
                $bsCurlModel->logError($curlData);
                $saveArr["bool"]=false;
                $saveArr["message"]=$curlData["message"];
            }
        }
        return $saveArr;
    }

    protected function getCurlDataModels(){
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $bsStaffID = 0;
        $maxMonth = ($this->quarter_no-1)*3 + 3;
        $startDate = date("Y/m/01",strtotime("{$this->year_no}-{$maxMonth}-01"));
        $startDate = date("Y/m/01",strtotime("{$startDate} + 1 months"));//季度奖金推送到北森需要延迟一个月
        $stopDate = date("Y/m/t",strtotime($startDate));
        $staffRow = Yii::app()->db->createCommand()->select("bs_staff_id")->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$this->employee_id))->queryRow();
        if($staffRow){
            $bsStaffID = $staffRow["bs_staff_id"];
        }
        $models[]=array(
            "staffId"=>$bsStaffID,
            "itemName"=>8,//季度绩效奖金
            "startDate"=>$startDate,
            "stopDate"=>$stopDate,
            "numericVal"=>empty($this->bonus_amount)?0:round($this->bonus_amount,2),
        );
        return $models;
    }

	protected function saveHeader(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
				$sql = "update acc_performance_bonus set  
                            new_amount = :new_amount,  
							bonus_amount = :bonus_amount,
							new_json = :new_json,
							bonus_json = :bonus_json,
							status_type = :status_type,
							luu = :luu 
						where id = :id
						";
				break;
			case 'back':
				$sql = "update acc_performance_bonus set  
							status_type = :status_type,
							luu = :luu 
						where id = :id
						";
				break;
		}

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false) {
			$sdate = General::toMyDate($this->start_dt);
			$command->bindParam(':start_dt',$sdate,PDO::PARAM_STR);
		}
        if (strpos($sql,':new_amount')!==false) {
            $this->new_amount = empty($this->new_amount)?0:round($this->new_amount,2);
            $command->bindParam(':new_amount',$this->new_amount,PDO::PARAM_STR);
        }
        if (strpos($sql,':bonus_amount')!==false) {
            $this->bonus_amount = empty($this->bonus_amount)?0:round($this->bonus_amount,2);
            $command->bindParam(':bonus_amount',$this->bonus_amount,PDO::PARAM_STR);
        }
        if (strpos($sql,':new_json')!==false) {
            $new_json = empty($this->new_json)?null:json_encode($this->new_json);
            $command->bindParam(':new_json',$new_json,PDO::PARAM_STR);
        }
        if (strpos($sql,':bonus_json')!==false) {
            $bonus_json = empty($this->bonus_json)?null:json_encode($this->bonus_json);
            $command->bindParam(':bonus_json',$bonus_json,PDO::PARAM_STR);
        }
		if (strpos($sql,':status_type')!==false)
			$command->bindParam(':status_type',$this->status_type,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}
	
	public function isReadOnly() {
		return ($this->status_type==1);
	}

	public static function getQuarterStr($year_no,$quarter_no){
	    $quaList = PerformanceBonusList::getQuarterList();
	    $str = $year_no."年";
	    if(isset($quaList[$quarter_no])){
            $str.=$quaList[$quarter_no];
        }
	    return $str;
    }

	public static function getStatusStr($status_type){
	    if($status_type==1){
	        return "已固定";
        }else{
            return "未固定";
        }
    }

    public function new_json_html(){
	    $html="<p>季度金额详情</p>";
	    $html.="<table class='table table-striped table-bordered table-hover '>";
	    $html.="<thead><tr><th>时间</th><th>新增业绩</th><th>更改新增业绩</th><th>合计</th></tr></thead>";
        $html.="<tbody>";
	    if(!empty($this->new_json)){
	        $sum = 0;
	        foreach ($this->new_json as $row){
                $sum+= $row["new_money"]+$row["edit_money"];
                $html.="<tr>";
                //b.year_no,b.month_no,a.new_money,a.edit_money
                $html.="<td>".$row["year_no"]."年".$row["month_no"]."月</td>";
                $html.="<td>".$row["new_money"]."</td>";
                $html.="<td>".$row["edit_money"]."</td>";
                $html.="<td>".($row["new_money"]+$row["edit_money"])."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            //b.year_no,b.month_no,a.new_money,a.edit_money
            $html.="<td colspan='3'>&nbsp;</td>";
            $html.="<td>".$sum."</td>";
            $html.="</tr>";
        }
        $html.="</tbody>";
	    $html.="</table>";

	    return $html;
    }

    public function bonus_json_html(){
        $html="<p>季度奖金详情</p>";
        $html.="<table class='table table-striped table-bordered table-hover '>";
        $html.="<thead><tr><th>条件</th><th>奖金</th><th>&nbsp;</th></tr></thead>";
        $html.="<tbody>";
        if(!empty($this->bonus_json)){
            foreach ($this->bonus_json["LE"] as $row){
                if(floatval($this->bonus_amount)==floatval($row["bonus_amount"])){
                    $html.="<tr class='success'>";
                    $str = "<span class='fa fa-check'></span>";
                }else{
                    $html.="<tr>";
                    $str = "<span class='fa fa-close'></span>";
                }
                //b.year_no,b.month_no,a.new_money,a.edit_money
                $html.="<td>季度金额<".$row["new_amount"]."</td>";
                $html.="<td>".$row["bonus_amount"]."</td>";
                $html.="<td>".$str."</td>";
                $html.="</tr>";
            }
            foreach ($this->bonus_json["GT"] as $row){
                if(floatval($this->bonus_amount)==floatval($row["bonus_amount"])){
                    $html.="<tr class='success'>";
                    $str = "<span class='fa fa-check'></span>";
                }else{
                    $html.="<tr>";
                    $str = "<span class='fa fa-close'></span>";
                }
                //b.year_no,b.month_no,a.new_money,a.edit_money
                $html.="<td>季度金额>=".$row["new_amount"]."</td>";
                $html.="<td>".$row["bonus_amount"]."</td>";
                $html.="<td>".$str."</td>";
                $html.="</tr>";
            }
        }
        $html.="</tbody>";
        $html.="</table>";

        return $html;
    }

    public function downFixed(){
        $suffix = Yii::app()->params['envSuffix'];
        $session = Yii::app()->session;
        if (isset($session['performanceBonus_xs08']) && !empty($session['performanceBonus_xs08'])) {
            $criteria = $session['performanceBonus_xs08'];
            $this->year_no = $criteria["year_no"];
            $this->quarter_no = $criteria["quarter_no"];
        }
        if(empty($this->year_no)||!is_numeric($this->year_no)){
            $this->year_no = date("Y",strtotime("-3 months"));
        }
        if(empty($this->quarter_no)||!is_numeric($this->quarter_no)){
            $month = date("n",strtotime("-3 months"));
            $this->quarter_no = ceil($month/3);
        }
        $excelData = array();
        $timerStr = $this->getQuarterStr($this->year_no,$this->quarter_no);
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.city as s_city,b.code,b.name,e.name as city_name,f.name as dept_name")
            ->from("acc_performance_bonus a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("hr{$suffix}.hr_dept f","b.position=f.id")
            ->leftJoin("security$suffix.sec_city e","b.city=e.code")
            ->where("a.status_type=1 and a.year_no={$this->year_no} AND a.quarter_no={$this->quarter_no}")
            ->queryAll();
        if($rows){
            $cityAreaList = AppraisalForm::getAllCityToArea();
            foreach ($rows as $row){
                $temp = $this->getDownTempForRow($row);
                $temp["yearMonth"]=$timerStr;
                if(key_exists($row["s_city"],$cityAreaList)){
                    $temp["areaName"] = $cityAreaList[$row["s_city"]];
                }
                $excelData[]=$temp;
            }
        }
        $excel = new DownPay();
        $headList = $this->getTopArr();
        $excel->colTwo=6;
        $str="季度绩效奖金\n";
        $str.="查询时间：".$timerStr;
        $excel->SetHeaderString($str);
        $excel->init();
        $excel->setSummaryHeader($headList);
        $excel->setListData($excelData);
        $excel->outExcel("季度绩效奖金");
    }

    protected function getDownTempForRow($row){
        $list = array(
            "yearMonth"=>"",
            "areaName"=>"",
            "cityName"=>$row["city_name"],
            "employeeCode"=>$row["code"],
            "employeeName"=>$row["name"],
            "deptName"=>$row["dept_name"],
        );
        $setJson = empty($row["new_json"])?array():json_decode($row["new_json"],true);
        $dataList = array();
        foreach ($setJson as $setRow){
            $keyStr = $setRow["year_no"]."_".$setRow["month_no"];
            $dataList[$keyStr] = $setRow;
        }
        $minMonth = ($this->quarter_no-1)*3 + 1;
        for ($i=$minMonth;$i<$minMonth+3;$i++){
            $keyStr = $this->year_no."_".$i;
            if(key_exists($keyStr,$dataList)){
                $list[$keyStr."new_money"]=floatval($dataList[$keyStr]["new_money"]);
                $list[$keyStr."edit_money"]=floatval($dataList[$keyStr]["edit_money"]);
            }else{
                $list[$keyStr."new_money"]="";
                $list[$keyStr."edit_money"]="";
            }
        }
        $list["new_amount"]=floatval($row["new_amount"]);
        $list["bonus_amount"]=floatval($row["bonus_amount"]);
        return $list;
    }

    private function getTopArr(){
        $topList=array(
            array("name"=>"年月","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//年月
            array("name"=>"区域","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//区域
            array("name"=>"城市","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//城市
            array("name"=>"工号","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//工号
            array("name"=>"姓名","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//姓名
            array("name"=>"岗位","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//岗位
        );
        $minMonth = ($this->quarter_no-1)*3 + 1;
        for ($i=$minMonth;$i<$minMonth+3;$i++){
            $topList[]=array("name"=>"{$i}月份","background"=>"#D6DCE4","color"=>"#000000",
                "colspan"=>array(
                    array("name"=>"新增业绩"),//新增业绩
                    array("name"=>"更改新增业绩"),//更改新增业绩
                )
            );
        }
        $topList[]=array("name"=>" ","background"=>"#D6DCE4","color"=>"#000000","colspan"=>array(
            array("name"=>"累计业绩"),//累计业绩
        ));
        $topList[]=array("name"=>" ","background"=>"#D6DCE4","color"=>"#000000","colspan"=>array(
            array("name"=>"季度奖金"),//季度奖金
        ));
        return $topList;
    }
}
