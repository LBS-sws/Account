<?php

class ExpenseFun
{

    public static function getColorForStatusType($status_type){
        $list = array(
            0=>" ",//草稿
            1=>" text-primary",//待确认
            2=>" text-primary",//待审核
            3=>" text-danger",//已拒绝
            4=>" text-info",//待确认银行
            6=>" text-info",//等待金蝶系统扣款
            7=>" text-danger",//金蝶系统已拒绝
            9=>" text-muted",//金蝶系统已扣款
        );
        if(key_exists("{$status_type}",$list)){
            return $list[$status_type];
        }else{
            return "";
        }
    }

    public static function getStatusStrForStatusType($status_type){
        $list = array(
            0=>Yii::t("give","draft"),//草稿
            1=>Yii::t("give","wait confirm"),//待确认
            2=>Yii::t("give","wait audit"),//待审核
            3=>Yii::t("give","rejected"),//已拒绝
            4=>Yii::t("give","wait bank"),//待确认银行
            6=>Yii::t("give","wait JD"),//等待金蝶系统扣款
            7=>Yii::t("give","rejected JD"),//金蝶系统已拒绝
            9=>Yii::t("give","finish"),//金蝶系统已扣款
        );
        if(key_exists("{$status_type}",$list)){
            return $list[$status_type];
        }else{
            return $status_type;
        }
    }

    //获取外部列表
    public static function getPaymentConditionList(){
        return array(
            "002"=>"每月15日",
            "003"=>"每月20日",
            "004"=>"每月25日",
            "005"=>"票到付款",
            "006"=>"票到30天",
            "007"=>"票到45天",
            "008"=>"票到60天",
            "009"=>"票到70天",
            "010"=>"票到80天",
            "011"=>"票到90天",
            "012"=>"票到120天",
            "013"=>"5%预付款",
            "014"=>"10%预付款",
            "015"=>"15%预付款",
            "016"=>"20%预付款",
            "017"=>"30%预付款",
            "018"=>"40%预付款",
            "019"=>"50%预付款",
            "020"=>"100%预付款",
            "021"=>"货到付款",
            "022"=>"半年预付",
            "023"=>"季度预付",
        );
    }

    //获取外部列表
    public static function getOutsideList(){
        return array(
            "0"=>Yii::t("give","personal"),
            "1"=>Yii::t("give","company"),
        );
    }
    //获取是否加急列表
    public static function getUrgentList(){
        return array(
            "0"=>Yii::t("give","No"),
            "1"=>Yii::t("give","Yes"),
        );
    }
    //获取是否列表
    public static function getNoOrYesList(){
        return array(
            "0"=>Yii::t("give","No"),
            "1"=>Yii::t("give","Yes"),
        );
    }
    //获取是否有发票列表
    public static function getInvoiceBoolList(){
        return array(
            "0"=>Yii::t("give","None"),
            "1"=>Yii::t("give","Have"),
        );
    }

    public static function getExpenseTableDetailForID($exp_id) {
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("id,field_id,field_value")
            ->from("acc_expense_detail")
            ->where("exp_id=:id",array(":id"=>$exp_id))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["field_id"]] = $row;
            }
        }
        return $list;
    }

    public static function getExpenseTableDetailForIDAndField($exp_id,$field_id) {
        $row = Yii::app()->db->createCommand()->select("id,field_id,field_value")
            ->from("acc_expense_detail")
            ->where("exp_id=:id and field_id=:field_id",array(":id"=>$exp_id,":field_id"=>$field_id))->queryRow();
        if($row){
            return $row["field_value"];
        }
        return "";
    }

    public static function getPaymentCompanyList(){
        $list=array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("hr{$suffix}.hr_company")
            ->where("id>0")->order("city asc,tacitly desc,id desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"];
            }
        }
        return $list;
    }

    public static function setModelEmployee($model,$str="employee_id"){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()->select("employee_id")->from("hr{$suffix}.hr_binding a")
            ->where("user_id=:user_id",array(":user_id"=>$uid))->queryRow();
        if($row){
            $model->$str = $row["employee_id"];
        }else{
            $model->$str = null;
        }
    }

    public static function getTripListForEmployeeID($employee_id,$not_id=0) {
        $notList =array();
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("id,trip_cause,trip_cost,trip_code")
            ->from("hr{$suffix}.hr_employee_trip")
            ->where("employee_id=:employee_id and status=4",array(
                ":employee_id"=>$employee_id
            ))->queryAll();
        $selectRows = Yii::app()->db->createCommand()->select("a.field_value")
            ->from("acc_expense_detail a")
            ->leftJoin("acc_expense b","a.exp_id=b.id")
            ->where("a.field_id='trip_id' and a.field_value is not null and b.employee_id='{$employee_id}' and a.field_value!='{$not_id}'")
            ->queryAll();
        if($selectRows){
            foreach ($selectRows as $selectRow){
                $notList[$selectRow["field_value"]] =$selectRow["field_value"];
            }
        }
        if($rows){
            foreach ($rows as $row){
                if(!in_array($row["id"],$notList)){
                    $list[$row["id"]] = "(".$row["trip_code"].") ".$row["trip_cause"]."；预估总费用:".floatval($row["trip_cost"]);
                }
            }
        }
        return $list;
    }

    public static function getTripNameForTripID($trip_id) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,trip_cause,trip_cost,trip_code")
            ->from("hr{$suffix}.hr_employee_trip")
            ->where("id=:id",array(":id"=>$trip_id))->queryRow();
        if($row){
            return "(".$row["trip_code"].") ".$row["trip_cause"]."；预估总费用:".floatval($row["trip_cost"]);
        }
        return "";
    }

    public static function getLocalSetIdToCity($city) {
        $row = Yii::app()->db->createCommand()->select("id")
            ->from("acc_set_name")
            ->where("return_value=:city and type_str='expense'",array(":city"=>$city))
            ->order("z_index asc")->queryRow();
        if($row){
            return $row["id"];
        }
        return "";
    }

    public static function getEmployeeListForID($id) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.code,a.name,b.name as department_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.department=b.id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return array(
                "code"=>$row["code"],
                "name"=>$row["name"],
                "employee"=>$row["name"]." ({$row["code"]})",
                "department"=>$row["department_name"]
            );
        }else{
            return array("code"=>"","name"=>"","employee"=>$id,"department"=>"");
        }
    }

    public static function getEmployeeAllListForID($id) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.code,a.name,a.city,b.name as department_name,f.name as position_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.department=b.id")
            ->leftJoin("hr{$suffix}.hr_dept f","a.position=f.id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            $cityName = General::getCityName($row["city"]);
            return array(
                "code"=>$row["code"],
                "name"=>$row["name"],
                "city_name"=>$cityName,
                "employee"=>$row["name"]." ({$row["code"]})",
                "department"=>$row["department_name"],
                "position"=>$row["position_name"]
            );
        }else{
            return array("code"=>"","name"=>"","city_name"=>"","employee"=>$id,"department"=>"","position"=>"");
        }
    }

    public static function getAuditListForID($id) {
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("acc_expense_audit")
            ->where("exp_id=:id",array(":id"=>$id))->queryAll();
        return $rows?$rows:array();
    }

    public static function getExpenseHistoryForID($id) {
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("acc_expense_history")
            ->where("exp_id=:id",array(":id"=>$id))->queryAll();
        return $rows;
    }

    public static function getTransTypeList() {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("trans_type_code,trans_type_desc")
            ->from("acc_trans_type")
            ->where("trans_cat='OUT'")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['trans_type_code']] = $row["trans_type_desc"];
            }
        }
        return $list;
    }

    public static function getTransStrForCode($code) {
        $row = Yii::app()->db->createCommand()->select("trans_type_code,trans_type_desc")
            ->from("acc_trans_type")
            ->where("trans_type_code=:code",array(":code"=>$code))
            ->queryRow();
        if($row){
            return $row["trans_type_desc"];
        }
        return $code;
    }

    public static function getAccountListForCity($city) {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("a.*,b.acct_type_desc")
            ->from("acc_account a")
            ->leftJoin("acc_account_type b","a.acct_type_id=b.id")
            ->where("a.city=:city and a.status='Y'",array(":city"=>$city))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row['id']] = "(".$row["acct_type_desc"].")".$row["acct_name"]." ".$row["acct_no"]."(".$row["bank_name"].")";
            }
        }
        return $list;
    }

    //根据员工id获取公司id
    public static function getCompanyIdToEmployeeID($employeeId) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.staff_id,a.company_id")
            ->from("hr{$suffix}.hr_employee a")
            ->where("a.id=:id",array(":id"=>$employeeId))->queryRow();
        if($row){
            return $row["staff_id"];//员工归属
            //return $row["company_id"];//员工合同归属
        }else{
            return "";
        }
    }

    //根据公司id获取公司名称
    public static function getCompanyNameToID($companyID) {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("name")
            ->from("hr{$suffix}.hr_company")
            ->where("id=:id",array(":id"=>$companyID))->queryRow();
        if($row){
            return $row["name"];
        }else{
            return "";
        }
    }

    public static function getAccountStrForID($id) {
        $row = Yii::app()->db->createCommand()->select("a.*,b.acct_type_desc")
            ->from("acc_account a")
            ->leftJoin("acc_account_type b","a.acct_type_id=b.id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            return "(".$row["acct_type_desc"].")".$row["acct_name"]." ".$row["acct_no"]."(".$row["bank_name"].")";
        }
        return $id;
    }

    public static function getAmtTypeOne(){
        return array(
            0=>"本地费用",
            1=>"差旅费用",
            2=>"办公费",
            3=>"快递费",
            4=>"通讯费",
            5=>"其他",
        );
    }

    public static function getRemitTypeOne(){
        return array(
            "A0"=>"物料采购",
            "A1"=>"日常付款",
            "A2"=>"工资薪金社保个税",
            "A3"=>"集团内交易",
            "A4"=>"集团内资金往来",
            "A5"=>"同名转账",
            "A6"=>"客户退款",
            "A7"=>"税金（包含增值税等，除个税）",
        );
    }

    public static function getRemitTypeTwo(){
        return array(
            "A00001"=>"物料采购",
            "A10001"=>"房租物业",
            "A10002"=>"固定资产",
            "A10003"=>"办公设备",
            "A10004"=>"劳务外包（含预缴个税）",
            "A10005"=>"其他",
            "A20001"=>"工资",
            "A20002"=>"社保",
            "A20003"=>"公积金",
            "A20004"=>"个税",
            "A20005"=>"保险费用",
            "A20006"=>"服务费",
            "A20007"=>"增值税",
            "A30001"=>"特许权经营费",
            "A30002"=>"总分包",
            "A30003"=>"咨询费",
            "A30004"=>"其他",
            "A40001"=>"借/还款",
            "A40002"=>"上划下拨",
            "A50001"=>"收款户转付款户",
            "A60001"=>"客户退款",
            "A70001"=>"税金",
        );
    }

    public static function getAmtTypeStrToKey($key){
        $list = self::getAmtTypeOne();
        return self::getKeyNameForList($list,$key);
    }

    public static function getAmtTypeOneEx(){
        return array(
            0=>array("name"=>"本地费用","colspan"=>2,"width"=>"12%"),
            1=>array("name"=>"差旅费用","colspan"=>5,"width"=>"30%"),
            2=>array("name"=>"办公费","colspan"=>1,"width"=>"6%"),
            3=>array("name"=>"快递费","colspan"=>1,"width"=>"6%"),
            4=>array("name"=>"通讯费","colspan"=>1,"width"=>"6%"),
            5=>array("name"=>"其他","colspan"=>1,"width"=>"6%")
        );
    }

    public static function getAmtTypeTwo(){
        return array(
            "00001"=>array("name"=>"市内交通费",'one_type'=>0,"width"=>"7%","more"=>true),
            "00002"=>array("name"=>"餐费",'one_type'=>0,"width"=>"5%","more"=>true),
            "10001"=>array("name"=>"机票/火车票/汽车票",'one_type'=>1,"width"=>"10%","more"=>true),
            "10002"=>array("name"=>"酒店",'one_type'=>1,"width"=>"5%","more"=>true),
            "10003"=>array("name"=>"交通费",'one_type'=>1,"width"=>"5%","more"=>true),
            "10004"=>array("name"=>"餐费",'one_type'=>1,"width"=>"5%","more"=>true),
            "10005"=>array("name"=>"其他",'one_type'=>1,"width"=>"5%","more"=>true),
            "20001"=>array("name"=>"办公费",'one_type'=>2,"width"=>"6%","more"=>false),
            "30001"=>array("name"=>"快递费",'one_type'=>3,"width"=>"6%","more"=>false),
            "40001"=>array("name"=>"通讯费",'one_type'=>4,"width"=>"6%","more"=>false),
            "50001"=>array("name"=>"其他",'one_type'=>5,"width"=>"6%","more"=>false),
        );
    }

    public static function getAmtTypeStrToKeyTwo($key){
        $list = self::getAmtTypeTwo();
        if(key_exists($key,$list)){
            return $list[$key]["name"];
        }else{
            return $key;
        }
    }

    public static function getKeyNameForList($list,$key){
        $key="".$key;
	    if(key_exists($key,$list)){
	        return $list[$key];
        }else{
	        return $key;
        }
    }

    public static function convertCurrency($money){
        $cnNums = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖"); //汉字的数字
        $cnIntRadice = array("", "拾", "佰", "仟"); //基本单位
        $cnIntUnits = array("", "万", "亿", "兆"); //对应整数部分扩展单位
        $cnDecUnits = array("角", "分", "毫", "厘"); //对应小数部分单位
        $cnInteger = "整"; //整数金额时后面跟的字符
        $cnIntLast = "元"; //整型完以后的单位
        $maxNum = 999999999999999.9999; //最大处理的数字
        $IntegerNum=""; //金额整数部分
        $DecimalNum=""; //金额小数部分
        $ChineseStr = ""; //输出的中文金额字符串
        $parts=""; //分离金额后用的数组，预定义
        if ($money == "") {
            return "";
        }
        $money = floatval($money);
        if ($money >= $maxNum) {
            return "";
        }
        if ($money == 0) {
            $ChineseStr = $cnNums[0].$cnIntLast.$cnInteger;
            return $ChineseStr;
        }
        $money = "".$money; //转换为字符串
        if (strpos($money,'.')===false){
            $IntegerNum = $money;
            $DecimalNum = '';
        } else {
            $parts = explode(".",$money);
            $IntegerNum = $parts[0];
            $DecimalNum = $parts[1].substr(0, 4);
        }
        if (intval($IntegerNum) > 0) { //获取整型部分转换
            $zeroCount = 0;
            $IntLen = str_split("{$IntegerNum}");
            for ($i = 0; $i < count($IntLen); $i++) {
                $n = $IntLen[$i];
                $p = count($IntLen) - $i - 1;
                $q = $p / 4;
                $m = $p % 4;
                if ($n == "0") {
                    $zeroCount++;
                } else {
                    if ($zeroCount > 0) {
                        $ChineseStr.= $cnNums[0];
                    }
                    $zeroCount = 0; //归零
                    $ChineseStr.= $cnNums[intval($n)]. $cnIntRadice[$m];
                }
                if ($m == 0 && $zeroCount < 4) {
                    $ChineseStr.= $cnIntUnits[$q];
                }
            }
            $ChineseStr.= $cnIntLast;
//整型部分处理完毕
        }
        if ($DecimalNum != '') { //小数部分
            $decLen = str_split("{$DecimalNum}");
            for ($i = 0; $i < count($decLen); $i++) {
                $n = $DecimalNum[$i];
                if ($n != '0') {
                    $ChineseStr .= $cnNums[intval($n)].$cnDecUnits[$i];
                }
            }
        }
        if ($ChineseStr == '') {
            $ChineseStr .= $cnNums[0] .$cnIntLast. $cnInteger;
        } else if ($DecimalNum == '') {
            $ChineseStr .= $cnInteger;
        }
        return $ChineseStr;
    }

    //查询相似的供应商
    public static function AjaxPayee($group,$city){
        $suffix = Yii::app()->params['envSuffix'];
        $html = "";
        $city = empty($city)?Yii::app()->user->city():$city;
        if($group!==""){
            $group = str_replace("'","\'",$group);
            $records = Yii::app()->db->createCommand()->select('*')
                ->from("swoper{$suffix}.swo_supplier")
                ->where("(city=:city or local_bool=0) and display=1 and (name like '%$group%' or code like '%$group%' or full_name like '%$group%')",array(
                    ":city"=>$city
                ))->queryAll();
            if($records){
                foreach ($records as $row){
                    $text="{$row["name"]} ({$row["code"]})";
                    $html.="<li><a class='clickThis' data-code='{$row["code"]}' data-name='{$row["name"]}' data-no='{$row["tax_reg_no"]}' data-bank='{$row["bank"]}' data-acct='{$row["acct_no"]}'>".$text."</a>";
                }
            }else{
                $html = "<li><a>没有结果</a></li>";
            }
        }else{
            $html = "<li><a>请输入供应商名称</a></li>";
        }
        return $html;
    }

    //查询相似的供应商
    public static function AjaxPayeeCode($group,$city){
        $suffix = Yii::app()->params['envSuffix'];
        $html = "";
        $city = empty($city)?Yii::app()->user->city():$city;
        if($group!==""){
            $group = str_replace("'","\'",$group);
            $records = Yii::app()->db->createCommand()->select('*')
                ->from("swoper{$suffix}.swo_supplier")
                ->where("(city=:city or local_bool=0) and display=1 and (name like '%$group%' or code like '%$group%' or full_name like '%$group%')",array(
                    ":city"=>$city
                ))->queryAll();
            if($records){
                foreach ($records as $row){
                    $text="{$row["name"]} ({$row["code"]})";
                    $html.="<li><a class='clickThis' data-code='{$row["code"]}' data-name='{$row["name"]}' data-no='{$row["tax_reg_no"]}' data-bank='{$row["bank"]}' data-acct='{$row["acct_no"]}'>".$text."</a>";
                }
            }else{
                $html = "<li><a>没有结果</a></li>";
            }
        }else{
            $html = "<li><a>请输入供应商编号</a></li>";
        }
        return $html;
    }

    public static function getAMPMList(){
        return array(
            "AM"=>"上午",
            "PM"=>"下午",
        );
    }

    public static function getAjaxTripDataForId($id){
        $returnData = array('status'=>0,'data'=>array());
        $suffix = Yii::app()->params['envSuffix'];
        $tripRow = Yii::app()->db->createCommand()->select("*")
            ->from("hr{$suffix}.hr_employee_trip")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($tripRow){
            $AMPMList = self::getAMPMList();
            $moneySetList=array();
            $moneySetRows = Yii::app()->db->createCommand()->select("*")
                ->from("hr{$suffix}.hr_trip_money_set")
                ->where("id>0")->queryAll();
            if($moneySetRows){
                foreach ($moneySetRows as $moneySetRow){
                    $moneySetList[$moneySetRow["id"]]=$moneySetRow["pro_name"];
                }
            }
            $dateRows = Yii::app()->db->createCommand()->select("*")
                ->from("hr{$suffix}.hr_employee_trip_info")
                ->where("trip_id=:id",array(":id"=>$id))->queryAll();
            $infoRows = Yii::app()->db->createCommand()->select("*")
                ->from("hr{$suffix}.hr_employee_trip_money")
                ->where("trip_id=:id",array(":id"=>$id))->queryAll();
            $returnData["status"]=1;
            $returnData["data"]["trip_cost"]=floatval($tripRow["trip_cost"]);
            $returnData["data"]["trip_cause"]=$tripRow["trip_cause"];
            $returnData["data"]["trip_address"]=$tripRow["trip_address"];
            $returnData["data"]["trip_company"]=$tripRow["company_name"];
            $returnData["data"]["trip_result"]=$tripRow["result_text"];
            $returnData["data"]["trip_date"]=array();
            if($dateRows){
                foreach ($dateRows as $dateRow){
                    $temp ="<tr>";
                    $temp.="<td>".$dateRow["start_time"]."(".self::getKeyNameForList($AMPMList,$dateRow["start_time_lg"]).")</td>";
                    $temp.="<td>".$dateRow["end_time"]."(".self::getKeyNameForList($AMPMList,$dateRow["end_time_lg"]).")</td>";
                    $temp.="</tr>";
                    $returnData["data"]["trip_date"][]=$temp;
                }
            }
            $returnData["data"]["trip_date"]=implode("",$returnData["data"]["trip_date"]);
            $returnData["data"]["trip_money"]=array();
            if($infoRows){
                foreach ($infoRows as $infoRow){
                    $temp ="<tr>";
                    $temp.="<td>".self::getKeyNameForList($moneySetList,$infoRow["money_set_id"])."</td>";
                    $temp.="<td>".floatval($infoRow["trip_money"])."</td>";
                    $temp.="</tr>";
                    $returnData["data"]["trip_money"][]=$temp;
                }
            }
            $returnData["data"]["trip_money"]=implode("",$returnData["data"]["trip_money"]);
        }
        return $returnData;
    }

    public static function getEmployeeNameForUsername($username){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("name")->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("user_id=:user_id",array(":user_id"=>$username))->queryRow();
        if($row){
            return $row["name"];
        }else{
            return $username;
        }
    }
}