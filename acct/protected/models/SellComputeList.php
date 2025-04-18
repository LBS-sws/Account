<?php

class SellComputeList extends CListPageModel
{
    public $year;
    public $month;

    public $city;

    public $onlySql="";//只允許查看自己的提成
    public $down_id=array();

    public static $sellComputeAttr=array(
        'service_reward'=>array('value'=>'service_reward','name'=>''),
        //'performance'=>array('value'=>'performance','name'=>''),
        'point'=>array('value'=>'point','name'=>''),
        'new_calc'=>array('value'=>'new_calc','name'=>''),
        'new_amount'=>array('value'=>'new_amount','name'=>'','amount'=>true),
        'edit_amount'=>array('value'=>'edit_amount','name'=>'','amount'=>true),
        'install_amount'=>array('value'=>'install_amount','name'=>'','amount'=>true),
        'end_amount'=>array('value'=>'end_amount','name'=>'','amount'=>true),
        'performance_amount'=>array('value'=>'performance_amount','name'=>'','amount'=>true),
        'new_money'=>array('value'=>'new_money','name'=>''),
        'edit_money'=>array('value'=>'edit_money','name'=>''),
        'install_money'=>array('value'=>'install_money','name'=>''),
        'out_money'=>array('value'=>'out_money','name'=>''),
        'performanceedit_amount'=>array('value'=>'performanceedit_amount','name'=>'','amount'=>true),
        'performanceedit_money'=>array('value'=>'performanceedit_money','name'=>''),
        'performanceend_amount'=>array('value'=>'performanceend_amount','name'=>'','amount'=>true),
        'renewal_amount'=>array('value'=>'renewal_amount','name'=>'','amount'=>true),
        'renewal_money'=>array('value'=>'renewal_money','name'=>''),
        'renewalend_amount'=>array('value'=>'renewalend_amount','name'=>'','amount'=>true),
        'product_amount'=>array('value'=>'product_amount','name'=>'','amount'=>true),
        'supplement_money'=>array('value'=>'supplement_money','name'=>''),
    );

    public function init(){
        if(empty($this->year)||!is_numeric($this->year)){
            $this->year = date("Y",strtotime("-1 months"));
        }
        if(empty($this->month)||!is_numeric($this->month)){
            $this->month = date("n",strtotime("-1 months"));
        }
        if(empty($this->city)){
            $this->city=Yii::app()->user->city();
        }
    }

    public function rules()
    {
        return array(
            array('year,month,city,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'code'=>Yii::t('app','employee_code'),
            'name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'city_name'=>Yii::t('app','city'),
            'time'=>Yii::t('app','Time'),
            'dept_name'=>Yii::t('app','user_name'),
            'moneys'=>Yii::t('commission','final_money'),
            'examine'=>Yii::t('misc','Status'),
		);
	}

    public function searchColumns() {
        $search = array(
            'code'=>"b.code",
            'name'=>"b.name",
            'city_name'=>'e.name',
            'dept_name'=>'c.name',
            //'examine'=>'g.examine',
        );
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1,$bool=true)
	{
        $this->down_id=array();
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $cityList = Yii::app()->user->city_allow();
        if($bool){
            $citySql = " a.city in ({$cityList})";
        }else{
            $citySql = " a.city='{$city}'";
        }
        $moneysSql = $this->getCountMoneySql();
        $leaveTime = date("Y/m/01",strtotime("{$this->year}/{$this->month}/01"));
		$sql1 = "select b.name,b.code,c.name as dept_name,a.id,g.examine,e.name as city_name {$moneysSql}
				from acc_service_comm_hdr a 
				LEFT JOIN hr$suffix.hr_employee b  on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 
                LEFT JOIN acc_service_comm_dtl f on f.hdr_id=a.id 
                LEFT JOIN acc_product g on a.id=g.service_hdr_id 
				where {$citySql} {$this->onlySql} and a.year_no= {$this->year} and a.month_no={$this->month} and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
			";
		$sql2 = "select count(a.id)
				from acc_service_comm_hdr a 
				LEFT JOIN hr$suffix.hr_employee b  on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 
				where {$citySql} {$this->onlySql} and a.year_no= {$this->year} and a.month_no={$this->month} and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
			";
		$clause = "";
        if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $columns = $this->searchColumns();
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.city desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $this->down_id[]=$record["id"];
			    $this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'time'=>"{$this->year}/{$this->month}",
                    'city_name'=>$record['city_name'],
                    'dept_name'=>$record['dept_name'],
                    'style'=>SellTableList::examineStyle($record['examine']),
                    'examine'=>SellTableList::examine($record['examine']),
                    'moneys'=>key_exists("moneys",$record)?floatval($record['moneys']):0,
                );
			}
		}
		$session = Yii::app()->session;
		$sessionNum = $bool?"":"01";
		$session[$this->criteriaName().$sessionNum] = $this->getCriteria();
        $this->down_id = implode(",",$this->down_id);
		return true;
	}

    public function getCriteria() {
        return array(
            'year'=>$this->year,
            'month'=>$this->month,
            'city'=>$this->city,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }

    private function getCountMoneySql(){
	    $sql = "";
	    $addList = array("IFNULL(f.supplement_money,0)");
        foreach (self::$sellComputeAttr as $item){
            if(key_exists("amount",$item)&&$item["amount"]){
                $addList[]="IFNULL(f.{$item['value']},0)";
            }
        }
        if(!empty($addList)){
            $sql= ",(".implode("+",$addList).") as moneys";
        }
        return $sql;
    }


    public static function getYearList(){
        $arr = array();
        $year = date("Y");
        for($i=$year-4;$i<$year+2;$i++){
            if($i>2021){
                $arr[$i] = $i.Yii::t("plane"," year unit");
            }
        }
        return $arr;
    }

    public static function getMonthList($bool=false){
        $arr = array();
        if ($bool){
            $arr[]=Yii::t("plane","all");
        }
        for($i=1;$i<=12;$i++){
            $arr[$i] = $i.Yii::t("plane"," month unit");
        }
        return $arr;
    }

    public static function showText($num,$showBool,$type=""){
        if ($showBool){
            return "";
        }else{
            switch ($type){
                case "rate":
                    return ($num*100)."%";
                default:
                    return $num;
            }
        }
    }

    //查找变更前的服务 $service：当前服务 $type：变更前的服务类型(N:新增,C:续约)
    public static function getBeforeServiceList($service,$type,$sales_str="salesman_id",$group_type=0){
        $service['salesman_id']=empty($service['salesman_id'])?0:$service['salesman_id'];
        $service['company_id']=empty($service['company_id'])?0:$service['company_id'];
        $service['cust_type']=empty($service['cust_type'])?0:$service['cust_type'];
        $service['cust_type_name']=empty($service['cust_type_name'])?0:$service['cust_type_name'];
        $status_dt = date("Y-m",strtotime($service['status_dt'])); //服务日期（服务表单的第一个日期:新增、更改、续约、终止）
        //$sign_dt = date("Y-m",strtotime($service['sign_dt'])); //签约日期
        $suffix = Yii::app()->params['envSuffix'];
        if($type=="N"){
            $dateSql="status='N' and date_format(first_dt,'%Y-%m')<'$status_dt'";
        }elseif($type=="C"){//續約終止：必須終止前一個客戶服務時續約
            $dateSql="date_format(status_dt,'%Y-%m')<'$status_dt'";
        }else{
            $dateSql="status='{$type}' and date_format(status_dt,'%Y-%m')<'$status_dt'";
        }
        $row = Yii::app()->db->createCommand()
            ->select("id,city,status,status_dt,first_dt,salesman_id,othersalesman_id,commission,target,royalty,royaltys")->from("swoper{$suffix}.swo_service")
            ->where("{$dateSql} and id!={$service['id']} and 
             salesman_id={$service['salesman_id']} and company_id={$service['company_id']} and 
             cust_type={$service['cust_type']} and cust_type_name={$service['cust_type_name']} and
             commission is not null and commission !=''")
            ->order("status_dt desc")->queryRow();
        //由於舊數據沒有保存提成點，所以需要重新查詢
        if($row){
            $oldSearch=true;
            if($type=="C"&&$row["status"]!="C"){ //如果終止服務前一個服務不是續約，則終止服務不是續約終止
                return array();
            }
            if($sales_str=="othersalesman_id"){ //跨區
                if($row["target"]==1){//放入奖金库的单不计算提成
                    $oldSearch=false;
                    $row["royalty"]=0;//放入奖金库的单不计算提成
                }else{
                    $row["royalty"]=$row["royaltys"];//放入奖金库的单不计算提成
                }
                $row["salesman_id"]=$row["othersalesman_id"];
            }
            $royalty=floatval($row["royalty"]);
            if($oldSearch&&empty($royalty)){
                self::getServiceRoyalty($row);
            }
            self::setSpanRateForService($row,$group_type);
        }
        return $row?$row:array();
    }

    //设置跨区客户服务的跨区提成比例
    public static function setSpanRateForService(&$serviceRow,$group_type){
        if(!in_array($group_type,array(0,1,2))||empty($serviceRow["othersalesman_id"])){
            return false;//无组别或者没有跨区的客户服务不需要查跨区提成比例
        }
        if($serviceRow["status"]=="N"){
            $timer = strtotime($serviceRow["first_dt"]);
        }else{
            $timer = strtotime($serviceRow["status_dt"]);
        }
        $year = date("Y",$timer);
        $month = date("n",$timer);
        $city = $serviceRow["city"];
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("*")->from("sales{$suffix}.sal_performance")
            ->where("city=:city and ((year={$year} and month<={$month}) or year<{$year})",array(":city"=>$city))
            ->order("year desc,month desc")->queryRow();
        if($row){
            $span_rate=0.5;
            $span_other_rate=0.5;
            switch ($group_type) {
                case 0://0:无
                    $span_rate = floatval($row["spanning"]);
                    $span_other_rate = floatval($row["otherspanning"]);
                    break;
                case 1:
                    $span_rate = floatval($row["business_spanning"]);
                    $span_other_rate = floatval($row["business_otherspanning"]);
                    break;
                case 2:
                    $span_rate = floatval($row["restaurant_spanning"]);
                    $span_other_rate = floatval($row["restaurant_otherspanning"]);
                    break;
            }
            $serviceRow["span_rate"]=$span_rate;
            $serviceRow["span_other_rate"]=$span_other_rate;
        }
    }

    //由於舊數據沒有保存提成點，需要查詢提成計算
    public static function getServiceRoyalty(&$service){
        $suffix = Yii::app()->params['envSuffix'];
        $time = $service["status"]=="N"?strtotime($service["first_dt"]):strtotime($service["status_dt"]);
        $year = date("Y",$time);
        $month = date("n",$time);
        $row = Yii::app()->db->createCommand()
            ->select("f.hdr_id,f.service_reward,f.point,f.new_calc,b.code,b.name")
            ->from("acc_service_comm_dtl f")
            ->leftJoin("acc_service_comm_hdr a","f.hdr_id=a.id")
            ->leftJoin("hr{$suffix}.hr_employee b","b.code=a.employee_code")
            ->where("b.id=:id and a.year_no=$year and a.month_no=$month",
                array(":id"=>$service["salesman_id"]))->queryRow();
        if($row){
            $point = Yii::app()->db->createCommand()->select("id,point")
                ->from("sales$suffix.sal_integral")
                ->where("hdr_id=:id",array(":id"=>$row['hdr_id']))
                ->queryRow();
            $point = $point?floatval($point["point"]):0;
            $service_reward = ReportXS01Form::serviceReward($row["code"],$row["name"],"{$year}/{$month}");
            //$point = self::getOldPoint($service,$year,$month);
            $service["royalty"]=$service_reward+$point+$row["new_calc"];
            $service["oldSell"]=array(
                "hdr_id"=>"hdr_id:".$row["hdr_id"],
                "service_reward"=>"service_reward:".$row["service_reward"],
                "point"=>"point:".$point,
                "new_calc"=>"new_calc:".$row["new_calc"]
            );//調試異常，把內容顯示出來
        }else{
            $service=array();
        }
    }

    //獲取舊版銷售積分提成點
    public static function getOldPoint($service,$year,$month){
        $point = 0;
        $suffix = Yii::app()->params['envSuffix'];
        $staffRow = Yii::app()->db->createCommand()
            ->select("a.user_id,f.manager_type,d.entry_time")
            ->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee d","d.id=a.employee_id")
            ->leftJoin("hr$suffix.hr_dept f","f.id=d.position")
            ->where("a.employee_id={$service["salesman_id"]} and f.manager_type in (1,2)")
            ->queryRow();//員工及副經理才有销售提成激励点：f.manager_type in (1,2)
        if($staffRow){
            $salesBool = true;//是否需要銷售系統的銷售提成點數
            $entry_time = date("Y-m-01",strtotime("{$staffRow['entry_time']} + 1 months"));
            $startDate = date("Y-m-d",strtotime("{$year}-{$month}-01"));
            if($entry_time>=$startDate){
                //新入職員工需要判斷該員工是否在本月1號有銷售拜訪
                $visitRow = Yii::app()->db->createCommand()->select("id")
                    ->from("sales$suffix.sal_visit")
                    ->where("city='{$service['city']}' and username=:id and visit_dt='{$startDate}'",array(":id"=>$staffRow["user_id"]))
                    ->queryRow();
                if(!$visitRow){
                    $salesBool = false;//當月一號沒有銷售拜訪
                }
            }
            if($salesBool){
                $integralRow = Yii::app()->db->createCommand()->select("id,point")
                    ->from("sales$suffix.sal_integral")
                    ->where("city='{$service['city']}' and year={$year} and month={$month} and username=:id",array(":id"=>$staffRow["user_id"]))
                    ->queryRow();
                if($integralRow){
                    $point = empty($integralRow['point'])?0:floatval($integralRow['point']);
                }
            }
        }
        return $point;
    }

    public static function getNewCalc($sum_money,$date,$city,$type='fw'){
        $rate = 0.05;
        $row = Yii::app()->db->createCommand()
            ->select("id")->from("acc_service_rate_hdr")
            ->where("start_dt<='{$date}' and city='{$city}'")
            ->order("start_dt desc")->queryRow();
        if($row){
            $dtRow = Yii::app()->db->createCommand()
                ->select("id,rate")->from("acc_service_rate_dtl")
                ->where("hdr_id='{$row['id']}' and name='{$type}' and ((sales_amount>={$sum_money} and operator='LE')
							or (sales_amount<{$sum_money} and operator='GT'))")
                ->order("sales_amount")->queryRow();
            if($dtRow){
                $rate = floatval($dtRow['rate']);
            }
        }
        return $rate;
    }

    public static function getProductRate($sum_money,$date,$city,$type){
        $rate = 0;
        $row = Yii::app()->db->createCommand()
            ->select("id")->from("acc_product_rate_hdr")
            ->where("start_dt<='{$date}' and city='{$city}'")
            ->order("start_dt desc")->queryRow();
        if($row){
            $dtRow = Yii::app()->db->createCommand()
                ->select("id,rate")->from("acc_product_rate_dtl")
                ->where("hdr_id='{$row['id']}' and name='{$type}' and ((sales_amount>={$sum_money} and operator='LE')
							or (sales_amount<{$sum_money} and operator='GT'))")
                ->order("sales_amount")->queryRow();
            if($dtRow){
                $rate = floatval($dtRow['rate']);
            }
        }
        return $rate;
    }

    public static function onlySearch(&$model){
        if(Yii::app()->user->validFunction('CN09')){
            $model->onlySql="";
        }else{
            $employeeList = self::getEmployeeListForUser();
            $id = empty($employeeList)?0:$employeeList["id"];
            $model->onlySql=" and b.id='{$id}'";//只能查詢自己的銷售提成計算
        }
    }

    public static function getEmployeeListForUser($username=""){
        $suffix = Yii::app()->params['envSuffix'];
        if(empty($username)){
            $username=Yii::app()->user->id;
        }
        $row = Yii::app()->db->createCommand()
            ->select("b.id,b.code,b.name,b.city")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.user_id=:username",array(":username"=>$username))
            ->queryRow();
        return $row?$row:array();
    }
}
