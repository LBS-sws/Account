<?php
class AppraisalForm extends CFormModel
{
	public $id = 0;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $username;
	public $entry_time;
	public $city;
    public $year_no;
    public $month_no;
    public $new_amount;
    public $new_sum;
    public $visit_sum;
    public $num_score;
    public $appraisal_amount;
    public $new_json;
    public $appraisal_json;
    public $status_type;

    public $ready=true;

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
            'month_no'=>Yii::t('service','Appraisal date'),
			'new_amount'=>Yii::t('service','new amount'),
			'bonus_amount'=>Yii::t('service','bonus amount'),
			'appraisal_amount'=>Yii::t('service','appraisal amount'),
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
	    if(!$this->getAppraisalForEmployeeID($this->employee_id)){
            $this->addError($attribute, "员工不存在，请与管理员联系");
        }else{
	        $this->setModelReady();
	        if($this->ready){
                if(key_exists("num_score",$_POST)){
                    $this->num_score = floatval($_POST["num_score"]);
                    $this->new_json["num_score"] = $this->num_score;
                }
                $this->new_json_html();//计算总金额
            }else{
                $this->addError($attribute, "权限不足，请与管理员联系");
            }
        }
	}

	private function setModelReady(){
        $this->ready = false;
        $suffix = Yii::app()->params['envSuffix'];
        $lcu = Yii::app()->user->id;
        $bindingRow = Yii::app()->db->createCommand()->select("employee_id")
            ->from("hr{$suffix}.hr_binding")
            ->where("user_id='{$lcu}'")
            ->queryRow();
        if($bindingRow){
            $employee_id = $bindingRow["employee_id"];
            $groupRow = Yii::app()->db->createCommand()->select("a.id")
                ->from("hr{$suffix}.hr_group_staff a")
                ->leftJoin("hr{$suffix}.hr_group b","a.group_id=b.id")
                ->where("b.group_code='SALESNEW' and a.employee_id=".$employee_id)
                ->queryRow();
            if($groupRow){
                $row = Yii::app()->db->createCommand()->select("employee_id")
                    ->from("hr{$suffix}.hr_group_branch")
                    ->where("employee_id={$this->employee_id} and group_staff_id=".$groupRow["id"])
                    ->queryRow();
                if($row){
                    $this->ready = true;
                }
            }
        }
    }

	private function getAppraisalForEmployeeID($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $session = Yii::app()->session;
        if (isset($session['appraisal_xs08']) && !empty($session['appraisal_xs08'])) {
            $criteria = $session['appraisal_xs08'];
            $this->year_no = $criteria["year_no"];
            $this->month_no = $criteria["month_no"];
        }
        if(empty($this->year_no)||!is_numeric($this->year_no)){
            $this->year_no = date("Y",strtotime("-1 months"));
        }
        if(empty($this->month_no)||!is_numeric($this->month_no)){
            $this->month_no = date("n",strtotime("-1 months"));
        }
        $employee_id = !empty($employee_id)&&is_numeric($employee_id)?intval($employee_id):0;
        $sql = "select code,name,city,entry_time from hr{$suffix}.hr_employee where id=$employee_id";
        $staffRow = Yii::app()->db->createCommand($sql)->queryRow();
        if($staffRow){
            $this->employee_code = $staffRow["code"];
            $this->employee_name = $staffRow["name"];
            $this->city = $staffRow["city"];
            $this->entry_time = General::toDate($staffRow["entry_time"]);
            $bindingRow = Yii::app()->db->createCommand()->select("user_id")
                ->from("hr{$suffix}.hr_binding")
                ->where("employee_id='{$employee_id}'")
                ->queryRow();
            $this->username = $bindingRow?$bindingRow["user_id"]:'';
        }else{
            return false;
        }
        $sql = "select * from acc_appraisal where employee_id=$employee_id AND year_no={$this->year_no} AND month_no={$this->month_no}";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $this->id = $row["id"];
            $this->employee_id = $row["employee_id"];
            $this->status_type = $row["status_type"];
            $this->new_amount = floatval($row["new_amount"]);
            $this->new_sum = floatval($row["new_sum"]);
            $this->visit_sum = floatval($row["visit_sum"]);
            $this->num_score = floatval($row["num_score"]);
            $this->appraisal_amount = floatval($row["appraisal_amount"]);
            $this->new_json = empty($row["new_json"])?array():json_decode($row["new_json"],true);
            $this->appraisal_json = empty($row["appraisal_json"])?array():json_decode($row["appraisal_json"],true);
        }else{
            $this->employee_id = $employee_id;
            $this->status_type = 0;
            Yii::app()->db->createCommand()->insert("acc_appraisal",array(
                "employee_id"=>$employee_id,
                "year_no"=>$this->year_no,
                "month_no"=>$this->month_no,
                "lcu"=>Yii::app()->user->id,
                "city"=>$this->city
            ));
            $this->id = Yii::app()->db->getLastInsertID();
        }
        if($this->status_type!=1){
            $this->new_amount = 0;
            $this->setNewMoneyAmt();
            $this->visit_sum = 0;
            $this->setVisitSum();
            $this->new_sum = 0;
            $this->setNewSum();
            $this->num_score = 0;
            $this->appraisal_amount = 0;
            $this->appraisal_json = self::getAppraisalSet($this->year_no,$this->month_no);
            $this->new_json=array(
                "new_amount"=>$this->new_amount,
                "new_sum"=>$this->new_sum,
                "visit_sum"=>$this->visit_sum,
                "num_score"=>$this->num_score,
            );
        }
        return true;
    }

    private function setNewSum(){
        $suffix = Yii::app()->params['envSuffix'];
        $startDate = date("Y-m-01",strtotime("{$this->year_no}-{$this->month_no}-01"));
        $endDate = date("Y-m-t",strtotime($startDate));
        $salesman = "{$this->employee_name} ({$this->employee_code})";
        $rows = Yii::app()->db->createCommand()
            ->select("a.log_id")
            ->from("swoper$suffix.swo_logistic_dtl a")
            ->leftJoin("swoper$suffix.swo_logistic b","a.log_id=b.id")
            ->where("b.log_dt between '{$startDate}' and '{$endDate}' and b.salesman='{$salesman}' and b.city ='{$this->city}' and a.commission=1")
            ->group("a.log_id")->queryAll();//产品数量
        $this->new_sum+= $rows?count($rows):0;
        $rows = Yii::app()->db->createCommand()
            ->select("a.id")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.commission is not null and a.status='N' and a.city='{$this->city}' and a.first_dt between '{$startDate}' and '{$endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
            ->queryAll();//新增数量
        $this->new_sum+= $rows?count($rows):0;
        $rows = Yii::app()->db->createCommand()
            ->select("a.id")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.commission is not null and a.commission>0 and a.status='A' and a.city='{$this->city}' and a.status_dt between '{$startDate}' and '{$endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
            ->queryAll();//更改新增数量
        $this->new_sum+= $rows?count($rows):0;
    }

    private function setVisitSum(){
        $suffix = Yii::app()->params['envSuffix'];
        $startDate = date("Y-m-01",strtotime("{$this->year_no}-{$this->month_no}-01"));
        $endDate = date("Y-m-t",strtotime($startDate));
        $sum_total = Yii::app()->db->createCommand()->select("count(id)")
            ->from("sales{$suffix}.sal_visit")
            ->where("username='{$this->username}' and visit_dt BETWEEN '{$startDate}' and '{$endDate}'")
            ->queryScalar();
        $this->visit_sum = $sum_total;
    }

    private function setNewMoneyAmt(){
        $dtlRow = Yii::app()->db->createCommand()->select("b.year_no,b.month_no,a.new_money,a.edit_money")
            ->from("acc_service_comm_dtl a")
            ->leftJoin("acc_service_comm_hdr b","a.hdr_id=b.id")
            ->where("b.year_no={$this->year_no} and b.month_no={$this->month_no} and b.employee_code=:code",array(
                ":code"=>$this->employee_code
            ))->order("b.month_no asc")->queryRow();
        if($dtlRow){
            $dtlRow["new_money"]= empty($dtlRow["new_money"])?0:floatval($dtlRow["new_money"]);
            $dtlRow["edit_money"]= empty($dtlRow["edit_money"])?0:floatval($dtlRow["edit_money"]);
            $this->new_amount+= $dtlRow["new_money"];
            $this->new_amount+= $dtlRow["edit_money"];
        }
    }

    public static function getAppraisalSet($year_no,$month_no){
	    return array(
	        "new_amount"=>array("rate"=>0.3,"maxNum"=>60000,"maxRate"=>1.5,"text"=>"6万/月"),//新签合同金额
            "visit_sum"=>array("rate"=>0.3,"maxNum"=>400,"maxRate"=>1.5,"text"=>"400家"),//客户拜访数量
	        "new_sum"=>array("rate"=>0.3,"maxNum"=>4,"maxRate"=>1.5,"text"=>"4单/月"),//月度签单数量
	        "num_score"=>array("rate"=>0.1,"maxNum"=>10,"maxRate"=>1.2,"text"=>"0-10分上级打分"),//日常工作完成率
        );
    }
	
	public function retrieveData($employee_id)
	{
		$city = Yii::app()->user->city_allow();
		if ($this->getAppraisalForEmployeeID($employee_id)) {
		    $this->setModelReady();
			return true;
		} else {
			return false;
		}
		
	}
	
	public function saveData()
	{

		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveHeader(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'edit':
				$sql = "update acc_appraisal set  
                            new_amount = :new_amount,  
							new_sum = :new_sum,
							visit_sum = :visit_sum,
							num_score = :num_score,
							appraisal_amount = :appraisal_amount,
							new_json = :new_json,
							appraisal_json = :appraisal_json,
							status_type = :status_type,
							luu = :luu 
						where id = :id
						";
				break;
			case 'back':
				$sql = "update acc_appraisal set  
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
        if (strpos($sql,':new_amount')!==false) {
            $this->new_amount = empty($this->new_amount)?0:round($this->new_amount,2);
            $command->bindParam(':new_amount',$this->new_amount,PDO::PARAM_STR);
        }
        if (strpos($sql,':new_sum')!==false) {
            $this->new_sum = empty($this->new_sum)?0:round($this->new_sum,2);
            $command->bindParam(':new_sum',$this->new_sum,PDO::PARAM_STR);
        }
        if (strpos($sql,':visit_sum')!==false) {
            $this->visit_sum = empty($this->visit_sum)?0:round($this->visit_sum,2);
            $command->bindParam(':visit_sum',$this->visit_sum,PDO::PARAM_STR);
        }
        if (strpos($sql,':num_score')!==false) {
            $this->num_score = empty($this->num_score)?0:round($this->num_score,2);
            $command->bindParam(':num_score',$this->num_score,PDO::PARAM_STR);
        }
        if (strpos($sql,':appraisal_amount')!==false) {
            $this->appraisal_amount = empty($this->appraisal_amount)?0:round($this->appraisal_amount,2);
            $command->bindParam(':appraisal_amount',$this->appraisal_amount,PDO::PARAM_STR);
        }
        if (strpos($sql,':new_json')!==false) {
            $new_json = empty($this->new_json)?null:json_encode($this->new_json);
            $command->bindParam(':new_json',$new_json,PDO::PARAM_STR);
        }
        if (strpos($sql,':appraisal_json')!==false) {
            $appraisal_json = empty($this->appraisal_json)?null:json_encode($this->appraisal_json);
            $command->bindParam(':appraisal_json',$appraisal_json,PDO::PARAM_STR);
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

	public static function getMonthStr($year_no,$month_no){
	    $str = $year_no."年".$month_no."月";
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
	    $ready = $this->ready&&$this->status_type==0;
	    $arrTitleList=array(
	        "new_amount"=>array("htmlOptions"=>array("min"=>0,"readonly"=>true,"id"=>"new_amount"),"title"=>"新签合同金额","body"=>"根据完成率按比例计算得分，当项得分最低为0，最高为150%"),
	        "visit_sum"=>array("htmlOptions"=>array("min"=>0,"readonly"=>true,"id"=>"new_sum"),"title"=>"客户拜访数量","body"=>"根据完成率按比例计算得分，当项得分最低为0，最高为150%"),
	        "new_sum"=>array("htmlOptions"=>array("min"=>0,"readonly"=>true,"id"=>"visit_sum"),"title"=>"月度签单数量","body"=>"根据完成率按比例计算得分，当项得分最低为0，最高为150%"),
	        "num_score"=>array("htmlOptions"=>array("min"=>0,"max"=>12,"readonly"=>!$ready,"id"=>"num_score"),"title"=>"日常工作完成率<br/>（如月报、日报、培训完成等）","body"=>"具体参照销售职责及操作规范，每月由直属上级进行考评，进行加减分扣分（表现优秀额外附加最高2分）"),
        );
	    $html="<p>销售顾问绩效考核表（入职前6个月内考核）</p>";
	    $html.="<table class='table table-striped table-bordered table-hover '>";
	    $html.="<thead><tr>";
	    $html.="<th width='2%'>NO</th>";
	    $html.="<th width='14%'>关键考核指标</th>";
	    $html.="<th width='10%'>考核占比</th>";
	    $html.="<th width='10%'>目标值</th>";
	    $html.="<th width='26%'>考核及计分方式</th>";
	    $html.="<th width='18%'>月度完成情况（请填写）</th>";
	    $html.="<th width='10%'>达成率</th>";
	    $html.="<th width='10%'>当月考核得分</th>";
	    $html.="</tr></thead>";
        $html.="<tbody>";
        $num=0;
	    if(!empty($this->new_json)){
	        $sum = 0;
	        foreach ($this->appraisal_json as $keyStr=>$row){
                $num++;
                $html.="<tr class='changeTr'>";
                $title = "";
                $body="";
                $htmlOptions=array("data-num"=>$row["maxNum"],"data-rate"=>$row["rate"],"data-max_rate"=>$row["maxRate"]);
                $money = key_exists($keyStr,$this->new_json)?$this->new_json[$keyStr]:0;
                if(key_exists($keyStr,$arrTitleList)){
                    $title = $arrTitleList[$keyStr]["title"];
                    $body = $arrTitleList[$keyStr]["body"];
                    $htmlOptions = array_merge($htmlOptions,$arrTitleList[$keyStr]["htmlOptions"]);
                }
                $rate = $money/$row["maxNum"];
                $rate = $rate>$row["maxRate"]?$row["maxRate"]:round($rate,4);
                $money_ok = $rate*$row["rate"]*100;
                $money_ok = round($money_ok,2);
                $sum+=$money_ok;
                $html.="<td>".$num."</td>";
                $html.="<td>".$title."</td>";
                $html.="<td>".($row["rate"]*100)."%</td>";
                $html.="<td>".$row["text"]."</td>";
                $html.="<td>".$body."</td>";
                $html.="<td>".TbHtml::numberField($keyStr,$money,$htmlOptions)."</td>";
                $html.="<td id='{$keyStr}_rate'>".($rate*100)."%</td>";
                $html.="<td id='{$keyStr}_ok'>".sprintf("%.2f",$money_ok)."</td>";
                $html.="</tr>";
            }
            $this->appraisal_amount = $sum;
            $html.="<tr>";
            //b.year_no,b.month_no,a.new_money,a.edit_money
            $html.="<td>&nbsp;</td>";
            $html.="<td>合计</td>";
            $html.="<td>100%</td>";
            $html.="<td colspan='4' class='text-right'>总分</td>";
            $html.="<td id='appraisal_amount'>".sprintf("%.2f",$this->appraisal_amount)."</td>";
            $html.="</tr>";
        }
        $html.="</tbody>";
	    $html.="</table>";

	    return $html;
    }
}
