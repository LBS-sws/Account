<?php
class SalesGroupBelowForm extends CFormModel
{
	public $id = 0;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $city;
    public $year_no;
    public $month_no;
    public $bonus_amount=0;//提成金额
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
            'month_no'=>Yii::t('app','Time'),
			'new_amount'=>Yii::t('service','new amount'),
			'bonus_amount'=>"提成金额",
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

	protected function getYearMonthForSession(){
        $session = Yii::app()->session;
        if (isset($session['salesGroupBelow_xs08']) && !empty($session['salesGroupBelow_xs08'])) {
            $criteria = $session['salesGroupBelow_xs08'];
            $this->year_no = $criteria["year_no"];
            $this->month_no = $criteria["month_no"];
        }
        if(empty($this->year_no)||!is_numeric($this->year_no)){
            $this->year_no = date("Y");
        }
        if(empty($this->month_no)||!is_numeric($this->month_no)){
            $this->month_no = date("n");
        }
    }

	private function getPerBonusForEmployeeID($employee_id,$resetBool=false){
        $suffix = Yii::app()->params['envSuffix'];
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
        $sql = "select * from acc_group_below where employee_id=$employee_id AND year_no={$this->year_no} AND month_no={$this->month_no}";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->employee_id = $row["employee_id"];
            $this->status_type = $row["status_type"];
            $this->bonus_amount = floatval($row["bonus_amount"]);
            $this->new_json = empty($row["new_json"])?array():json_decode($row["new_json"],true);
            $this->bonus_json = empty($row["bonus_json"])?array():json_decode($row["bonus_json"],true);
        }else{
            $this->employee_id = $employee_id;
            $this->status_type = 0;
            $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
            Yii::app()->db->createCommand()->insert("acc_group_below",array(
                "employee_id"=>$employee_id,
                "year_no"=>$this->year_no,
                "month_no"=>$this->month_no,
                "status_type"=>$this->status_type,
                "lcu"=>$uid,
                "city"=>$this->city
            ));
            $this->id = Yii::app()->db->getLastInsertID();
        }
        if($this->status_type!=1||$resetBool){//如果没有固定或者当月，则读配置
            $codeStr = $this->setBonusJson();
            $this->bonus_amount = 0;
            $new_json = array();
            $rows = Yii::app()->db->createCommand()->select("b.employee_code,b.employee_name,a.new_money,a.edit_money")
                ->from("acc_service_comm_dtl a")
                ->leftJoin("acc_service_comm_hdr b","a.hdr_id=b.id")
                ->where("b.year_no={$this->year_no} and b.month_no in ({$this->month_no}) and 
                b.employee_code in ({$codeStr})")->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $rowAmt = $row["new_money"]+$row["edit_money"];
                    if(isset($this->bonus_json[$row['employee_code']])){
                        $amtRadio = $this->bonus_json[$row['employee_code']]['amt_radio'];
                        $row["entry_time"]=$this->bonus_json[$row['employee_code']]['entry_time'];
                    }else{
                        $amtRadio=0;
                        $row["entry_time"]="";
                    }

                    $rowAmt = round($amtRadio*$rowAmt,2);
                    $row["rowAmt"]=$rowAmt;
                    $row["amtRadio"]=$amtRadio;
                    $this->bonus_amount+= $rowAmt;
                    $new_json[]=$row;
                }
            }
            $this->new_json = $new_json;

            //保存结果
            $this->status_type = 1;
            Yii::app()->db->createCommand()->update("acc_group_below",array(
                "status_type"=>$this->status_type,
                "new_json"=>json_encode($this->new_json),
                "bonus_json"=>json_encode($this->bonus_json),
                "bonus_amount"=>$this->bonus_amount,
            ),"id=:id",array(":id"=>$this->id));
        }
        return true;
    }

    private function setBonusJson(){
        $suffix = Yii::app()->params['envSuffix'];
        $date = date("Y-m",strtotime("{$this->year_no}/{$this->month_no}/01"));
	    $codeList=array();
        $bonus_json = array();
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.set_id,a.employee_id,a.employee_type,b.employee_type as staff_type,f.code,f.name,f.entry_time")
            ->from("acc_group_set_info a")
            ->leftJoin("acc_group_set b","a.set_id=b.id")
            ->leftJoin("hr{$suffix}.hr_employee f","a.employee_id=f.id")
            ->where("DATE_FORMAT(b.start_date,'%Y-%m')<='{$date}' and DATE_FORMAT(b.end_date,'%Y-%m')>='{$date}' and b.employee_id='{$this->employee_id}'")->queryAll();
        if($rows){
            foreach ($rows as $row){
                if($row["staff_type"]==2){//固定1%
                    $row["amt_radio"]=0.01;
                }else{//老销售×0.5% + 1年内新销售×1%
                    switch ($row["employee_type"]){
                        case 2://新入职
                            $row["amt_radio"]=0.01;
                            break;
                        case 3://老员工
                            $row["amt_radio"]=0.005;
                            break;
                        default://自动获取
                            $entry_time = date("Y-m-01",strtotime($row["entry_time"]));
                            $maxDate = date("Y-m",strtotime("$entry_time + 1 years"));
                            if($maxDate<$date){//老销售
                                $row["amt_radio"]=0.005;
                            }else{
                                $row["amt_radio"]=0.01;
                            }
                    }
                }
                $bonus_json[$row["code"]]=$row;
                $codeList[]=$row["code"];
            }
        }
        $this->bonus_json = $bonus_json;
        if(empty($codeList)){
            return "''";
        }else{
            return "'".implode("','",$codeList)."'";
        }
    }
    public function sendBsByOne($index){
        $row = Yii::app()->db->createCommand()->select("*")->from("acc_group_below")
            ->where("id=:id",array(":id"=>$index))->queryRow();
        if($row){
            $employee_id =$row["employee_id"];
            $this->year_no =$row["year_no"];
            $this->month_no =$row["month_no"];
        }else{
            return false;
        }
        $curlData=array(
            "presetSalarySubsetCode"=>"PresetSalarySubset1",
            "models"=>array()
        );
        $this->getPerBonusForEmployeeID($employee_id,true);
        $temp = $this->getCurlDataModels();
        $curlData["models"] = array_merge($curlData["models"],$temp);

        $bsCurlModel = new BsCurlModel();
        $bsCurlModel->sendData = $curlData;
        $curlData = $bsCurlModel->sendBsCurl();
        if($curlData["code"]!=200){//curl异常，不继续执行
            $bsCurlModel->logError($curlData);
            return false;
        }else{
            $bsCurlModel->logError($curlData);
            return true;
        }
    }
	
	public function resetAllList($year,$month,$sendBsBool=false)
	{
	    $this->year_no=$year;
	    $this->month_no=$month;
        $idList = SalesGroupSetForm::getManageIDList($this->year_no,$this->month_no);
	    if($idList){
            $curlData=array(
                "presetSalarySubsetCode"=>"PresetSalarySubset1",
                "models"=>array()
            );
	        foreach ($idList as $employee_id){
                $this->getPerBonusForEmployeeID($employee_id,true);
                $temp = $this->getCurlDataModels();
                $curlData["models"] = array_merge($curlData["models"],$temp);
            }
            if($sendBsBool){
                $bsCurlModel = new BsCurlModel();
                $bsCurlModel->sendData = $curlData;
                $curlData = $bsCurlModel->sendBsCurl();
                if($curlData["code"]!=200){//curl异常，不继续执行
                    echo "-error \n";
                    $bsCurlModel->logError($curlData);
                }else{
                    echo "-success \n";
                    $bsCurlModel->logError($curlData);
                }
            }
        }
	}

    protected function getCurlDataModels(){
        $suffix = Yii::app()->params['envSuffix'];
        $models = array();
        $bsStaffID = 0;
        $startDate = date("Y/m/01",strtotime("{$this->year_no}-{$this->month_no}-01"));
        $stopDate = date("Y/m/t",strtotime($startDate));
        $staffRow = Yii::app()->db->createCommand()->select("bs_staff_id")->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$this->employee_id))->queryRow();
        if($staffRow){
            $bsStaffID = $staffRow["bs_staff_id"];
        }
        $models[]=array(
            "staffId"=>$bsStaffID,
            "itemName"=>10,//地推销售管理提成
            "startDate"=>$startDate,
            "stopDate"=>$stopDate,
            "numericVal"=>$this->bonus_amount,
        );
        return $models;
    }

	public function retrieveData($employee_id)
	{
		$city = Yii::app()->user->city_allow();
        $this->getYearMonthForSession();
        $bool = $this->year_no==date("Y")&&$this->month_no==date("n");
		if ($this->getPerBonusForEmployeeID($employee_id,$bool)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function isReadOnly() {
		return true;
	}

    public function new_json_html(){
	    $html="<p>提成金额详情</p>";
	    $html.="<table class='table table-striped table-bordered table-hover '>";
	    $html.="<thead><tr><th>员工</th><th>入职日期</th><th>新增业绩</th><th>更改新增业绩</th><th>累计业绩</th><th>提成比例</th><th>提成金额</th></tr></thead>";
        if(!empty($this->new_json)){
            $html.="<tbody>";
            $sum = 0;
	        foreach ($this->new_json as $row){
	            //$rowSum = $row["new_money"]+$row["edit_money"];
                $rowAmt= $row["new_money"]+$row["edit_money"];
                $amt_radio =($row["amtRadio"]*100)."%";
	            $rowBonus = round($rowAmt*$row["amtRadio"],2);
	            $sum+=$rowBonus;
	            $className = "";
                $html.="<tr class='{$className}'>";
                //b.year_no,b.month_no,a.new_money,a.edit_money
                $html.="<td>".$row["employee_name"]." ({$row["employee_code"]})"."</td>";
                $html.="<td>".(date("Y-m-d",strtotime($row["entry_time"])))."</td>";
                $html.="<td>".$row["new_money"]."</td>";
                $html.="<td>".$row["edit_money"]."</td>";
                $html.="<td>".$rowAmt."</td>";
                $html.="<td>".$amt_radio."</td>";
                $html.="<td>".$rowBonus."</td>";
                $html.="</tr>";
            }
            $html.="<tr>";
            $html.="</tbody>";
            $html.="</tfoot>";
            //b.year_no,b.month_no,a.new_money,a.edit_money
            $html.="<th colspan='6' class='text-right'><b>汇总</b></th>";
            $html.="<th>".$sum."</th>";
            $html.="</tr>";
            $html.="</tfoot>";
        }
	    $html.="</table>";

	    return $html;
    }

    public function downFixed(){
        $suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        $session = Yii::app()->session;
        if (isset($session['salesGroupBelow_xs08']) && !empty($session['salesGroupBelow_xs08'])) {
            $criteria = $session['salesGroupBelow_xs08'];
            $this->year_no = $criteria["year_no"];
            $this->month_no = $criteria["month_no"];
        }
        $bool = $this->year_no==date("Y")&&$this->month_no==date("n");
        $idList = SalesGroupSetForm::getManageIDList($this->year_no,$this->month_no);
        $idStr = implode(",",$idList);
        if($idList){
            foreach ($idList as $employee_id){
                $this->getPerBonusForEmployeeID($employee_id,$bool);
            }
        }
        $excelData = array();
        $detailData=array();
        $rows = Yii::app()->db->createCommand()
            ->select("b.id,b.code,b.name,c.name as dept_name,e.name as city_name,f.bonus_amount,f.new_json,f.status_type")
            ->from("hr$suffix.hr_employee b")
            ->leftJoin("hr$suffix.hr_dept c","b.position=c.id")
            ->leftJoin("security$suffix.sec_city e","b.city=e.code")
            ->leftJoin("account$suffix.acc_group_below f","f.employee_id=b.id AND f.year_no={$this->year_no} AND f.month_no={$this->month_no}")
            ->where(" b.id in ({$idStr}) AND b.city in ({$citylist})")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $excelData[]=$this->getDownTempForRow($row);
                $detailData[]=$this->getDownDetailTempForRow($row);
            }
        }
        $excel = new DownPay();
        $headList = $this->getTopArr();
        $detailHeadList = $this->getDetailTopArr();
        $excel->colTwo=6;
        $str="销售团队下线提成\n";
        $str.="年月：{$this->year_no}年{$this->month_no}月";
        $excel->SetHeaderString($str);
        $excel->init();
        $excel->setSheetName("汇总");
        $excel->setSummaryHeader($headList);
        $excel->setListData($excelData);
        $sheetId=0;
        if(!empty($detailData)){
            $excel->colTwo=7;
            foreach ($detailData as $detailRow){
                $sheetId++;
                $headerStr = $str."\n";
                $headerStr.= $detailRow["name"];
                $excel->addSheet($detailRow["name"]);
                $excel->SetHeaderString($headerStr);
                $excel->outHeader($sheetId);
                $excel->setSummaryHeader($detailHeadList);
                $excel->setListData($detailRow["list"]);
            }
        }
        $excel->outExcel("销售团队下线提成");
    }

    protected function getDownDetailTempForRow($row){
        $list=array("name"=>$row["name"]." ({$row["code"]})","list"=>array());
        $jsonRows = json_decode($row["new_json"],true);
        if($jsonRows){
            foreach ($jsonRows as $jsonRow){
                $rowNum = $jsonRow["new_money"]+$jsonRow["edit_money"];
                $rowAmt = $rowNum*$jsonRow["amtRadio"];
                $amtRadio = ($jsonRow["amtRadio"]*100)."%";
                $temp = array(
                    "employeeName"=>$jsonRow["employee_name"]." ({$jsonRow["employee_code"]})",
                    "entryTime"=>$jsonRow["entry_time"],
                    "newMoney"=>$jsonRow["new_money"],
                    "editMoney"=>$jsonRow["edit_money"],
                    "rowNum"=>$rowNum,
                    "amtRadio"=>$amtRadio,
                    "rowAmt"=>$rowAmt,
                );
                $list["list"][]=$temp;
            }
        }
        return $list;
    }

    protected function getDownTempForRow($row){
        $list = array(
            "employeeCode"=>$row["code"],
            "employeeName"=>$row["name"],
            "cityName"=>$row["city_name"],
            "deptName"=>$row["dept_name"],
            "yearMonth"=>$this->year_no."年".$this->month_no."月",
            "bonus_amount"=>$row['status_type']!=1?"-":floatval($row['bonus_amount']),
        );
        return $list;
    }

    private function getDetailTopArr(){
        $topList=array(
            array("name"=>"员工","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//员工
            array("name"=>"入职日期","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//入职日期
            array("name"=>"新增业绩","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//新增业绩
            array("name"=>"更改新增业绩","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//更改新增业绩
            array("name"=>"累计业绩","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//累计业绩
            array("name"=>"提成比例","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//提成比例
            array("name"=>"提成金额","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//提成金额
        );
        return $topList;
    }

    private function getTopArr(){
        $topList=array(
            array("name"=>"工号","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//工号
            array("name"=>"姓名","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//姓名
            array("name"=>"城市","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//城市
            array("name"=>"岗位","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//岗位
            array("name"=>"年月","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//年月
            array("name"=>"提成金额","rowspan"=>2,"background"=>"#D6DCE4","color"=>"#000000"),//提成金额
        );
        return $topList;
    }
}
