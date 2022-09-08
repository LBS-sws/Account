<?php
class SysBlock {
    protected $checkItems;

    protected $systemIsCN=0;//0：大陸。 1：台灣。2：新加坡。 3：吉隆坡

    public function __construct() {
        $this->checkItems = require(Yii::app()->basePath.'/config/sysblock.php');
        $this->systemIsCN = General::SystemIsCN();
    }

    public function blockNRoute($controllerId, $functionId) {
        $session = Yii::app()->session;
        $sysblock =isset($session['sysblock']) ? $session['sysblock'] : array();
        $sysId = Yii::app()->params['systemId'];

        foreach ($this->checkItems as $key=>$value) {
            if (!isset($sysblock[$key]) || $sysblock[$key]["bool"]==false) {
                $function = $value['validation'];
                $result = $this->$function($key,$value);
                //$result = call_user_func_array('self::'.$value['validation'],array($key,&$value));
                $sysblock[$key] = array(
                    "bool"=>$result,
                    'fun'=>$value
                );
                $session['sysblock'] = $sysblock;
                //function設置為空時，只提示一次，不限制行為(start)
                if($value['function']===""){
                    continue;
                }
                //function設置為空時，只提示一次，不限制行為(end)

                if (!$result) {
                    $url = '';
                    $systems = General::systemMapping();
                    if ($sysId==$value['system']) {
                        if(is_array($value['function'])){
                            $bool = !in_array($functionId,$value['function']);
                        }else{
                            $bool = $functionId!=$value['function'];
                        }
                        if ($controllerId!='site' && $bool) $url = $systems[$value['system']]['webroot'];
                    } else {
                        $url = $systems[$value['system']]['webroot'];
                    }
                    return ($url=='' ? false : $url);
                }
            }
        }

        return false;
    }

    public function getBlockMessage($systemId) {
        $session = Yii::app()->session;
        if (isset($session['sysblock'])) {
            foreach ($session['sysblock'] as $key=>$value) {//後續把value改成了數組類型
                if (is_array($value)&&!$value["bool"]) {
                    return $value["fun"]['message'];
                }elseif(!$value && isset($this->checkItems[$key])) {
                    return $this->checkItems[$key]['message'];
                }
            }
        }
        return false;
    }

    /**
     * 驗證管理員是否有未考核的員工.
     * @param string $uid 需要被驗證的管理員..
     * @return bool true(無未考核員工)  false(有未考核員工).
     */
    public function validateReviewLongTime(){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
            ->where("a.user_id=:user_id and e.system_id='hr' and e.a_read_write like'%RE02%'",array(":user_id"=>$uid))->queryRow();
        if($row){ //賬號有綁定的員工且有考核權限
            $year = date("Y");
            $day = date("m-d");
            $dateSql = " and b.id<0";
            if($year>2020){
                if ($day>="08-01"){
                    $dateSql = " and (b.year <= ".($year-1)." or (b.year = $year and b.year_type = 1))";
                }elseif ($day>="02-01"){
                    $dateSql = " and (b.year <= ".($year-1).")";
                }else{
                    $dateSql = " and (b.year <= ".($year-2)." or (b.year = ".($year-1)." and b.year_type = 1))";
                }
            }
            $count = Yii::app()->db->createCommand()->select("a.id")->from("hr$suffix.hr_review_h a")
                ->leftJoin("hr$suffix.hr_review b","a.review_id=b.id")
                ->leftJoin("hr$suffix.hr_employee d","b.employee_id=d.id")
                ->where("d.staff_status=0 and a.status_type!=3 and a.handle_id=:handle_id $dateSql",
                    array(":handle_id"=>$row['id'])
                )->queryRow();
            if($count){ //存在未考核的員工
                return false;
            }
        }
        return true;
    }

    /**
    每月10日, 驗證 用户还没有提交上月营业报告, false: 还没有提交
     **/
    public function isSalesSummarySubmitted() {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $lastdate = date('d')<10 ? date('Y-m-d',strtotime('-2 months')) : date('Y-m-d',strtotime('last day of previous month'));
        $year = date("Y", strtotime($lastdate));
        $month = date("m", strtotime($lastdate));

        $sql = "select username from security$suffix.sec_user_access 
				where username='$uid' and system_id='ops' and a_read_write like '%YA01%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $citylist = General::getCityListWithNoDescendant();
        if (!array_key_exists($city, $citylist)) return true;

        $sql = "select workflow$suffix.RequestStatus('OPRPT',a.id,a.lcd) as wfstatus
				from operation$suffix.opr_monthly_hdr a 
				where a.city='$city' and a.year_no=$year and a.month_no=$month and a.status='Y'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false || ($row['wfstatus']!='' && $row['wfstatus']!='PS'));
    }

    /**
    每月10日, 驗證 地区主管未审核营业报告, false: 未审核
     **/
    public function isSalesSummaryApproved() {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $lastdate = date('d')<10 ? date('Y-m-d',strtotime('-2 months')) : date('Y-m-d',strtotime('last day of previous month'));
        $year = date("Y", strtotime($lastdate));
        $month = date("m", strtotime($lastdate));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='ops' and a_read_write like '%YA03%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) {
            return true;
        } else {
            if (strpos($row['a_control'],'YN01')!==false) return true;
        }

        $wf = new WorkflowOprpt;
        $wf->connection = Yii::app()->db;
        $list = $wf->getPendingRequestIdList('OPRPT', 'PH', $uid);
        if (empty($list)) return true;

        $sql = "select a.id
				from operation$suffix.opr_monthly_hdr a 
				where a.id in ($list) and a.year_no=$year and a.month_no=$month and a.status='Y' 
				limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }

    /**
    每年12月30日, 驗證 用户有学分确认权限的未及时处理完, false: 未处理
     * 2022/09/07 限制修改成：每个月倒数第二天限制专员和审核人必须审核完当前地区所有的申请记录
     **/
    public function isCreditConfirmed() {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $monthFastDay = date("Y-m-01");//当月第一天
        $monthLastDay = date("Y-m-d",strtotime($monthFastDay."+1 months -3 day"));//每个月倒数第二天
        $lastdate = date("Y-m-d")>=$monthLastDay?$monthLastDay:date("Y-m-d",strtotime("{$monthFastDay} -1 day"));

        //$lastdate = date('m-d')=='12-31' ? date('Y-m-d') : date('Y-m-d',strtotime('last year December 31st'));
        $year = date("Y", strtotime($lastdate));
        $month = date("m", strtotime($lastdate));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='sp' and a_read_write like '%GA04%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $sql = "select a.id from spoint$suffix.gr_credit_request a
                LEFT JOIN spoint$suffix.gr_credit_type b ON a.credit_type = b.id
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where d.city='$city' AND a.state = 1 and a.apply_date <= '$lastdate'
				limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }

    /**
    每年12月30日, 驗證 用户有学分审核权限的未及时处理完, false: 未处理
     * 2022/09/07 限制修改成：每个月倒数第二天限制专员和审核人必须审核完当前地区所有的申请记录
     **/
    public function isCreditApproved() {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $monthFastDay = date("Y-m-01");//当月第一天
        $monthLastDay = date("Y-m-d",strtotime($monthFastDay."+1 months -3 day"));//每个月倒数第二天
        $lastdate = date("Y-m-d")>=$monthLastDay?$monthLastDay:date("Y-m-d",strtotime("{$monthFastDay} -1 day"));

        //$lastdate = date('m-d')=='12-31' ? date('Y-m-d') : date('Y-m-d',strtotime('last year December 31st'));
        $year = date("Y", strtotime($lastdate));
        $month = date("m", strtotime($lastdate));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='sp' and a_read_write like '%GA01%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $sql = "select a.id from spoint$suffix.gr_credit_request a
                LEFT JOIN spoint$suffix.gr_credit_type b ON a.credit_type = b.id
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where d.city='$city' AND a.state = 4 and a.apply_date <= '$lastdate'
				limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }

    /**
    每个月倒数第二天限制专员和审核人必须审核完当前地区所有的锦旗记录, false: 未处理
     **/
    public function isPrizeApproved () {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $monthFastDay = date("Y-m-01");//当月第一天
        $monthLastDay = date("Y-m-d",strtotime($monthFastDay."+1 months -3 day"));//每个月倒数第二天
        $lastdate = date("Y-m-d")>=$monthLastDay?$monthLastDay:date("Y-m-d",strtotime("{$monthFastDay} -1 day"));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='hr' and a_read_write like '%ZG07%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $sql = "select a.id from hr$suffix.hr_prize a 
                LEFT JOIN hr$suffix.hr_employee b ON a.employee_id = b.id
                where a.status =1 AND b.city IN ($city_allow) and a.prize_date <= '$lastdate'
				limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }

    /**
    每个月倒数第二天限制专员和审核人必须审核完当前地区所有的慈善分记录, false: 未处理
     **/
    public function isCharityApproved () {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $monthFastDay = date("Y-m-01");//当月第一天
        $monthLastDay = date("Y-m-d",strtotime($monthFastDay."+1 months -3 day"));//每个月倒数第二天
        $lastdate = date("Y-m-d")>=$monthLastDay?$monthLastDay:date("Y-m-d",strtotime("{$monthFastDay} -1 day"));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='ch' and a_read_write like '%GA01%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $sql = "select a.id from charity$suffix.cy_credit_request a
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where (d.city IN ($city_allow) AND a.state = 1) and a.type_state='2' 
			 and a.apply_date <= '$lastdate' limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }

    /**
    每个月倒数第二天限制专员和审核人必须审核完当前地区所有的慈善分记录, false: 未处理
     **/
    public function isCharityConfirmed () {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $monthFastDay = date("Y-m-01");//当月第一天
        $monthLastDay = date("Y-m-d",strtotime($monthFastDay."+1 months -3 day"));//每个月倒数第二天
        $lastdate = date("Y-m-d")>=$monthLastDay?$monthLastDay:date("Y-m-d",strtotime("{$monthFastDay} -1 day"));

        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='ch' and a_read_write like '%GA03%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;

        $sql = "select a.id from charity$suffix.cy_credit_request a
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where (d.city IN ($city_allow) AND a.state = 1) and a.type_state='1' 
			 and a.apply_date <= '$lastdate' limit 1
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        return ($row===false);
    }


    /**
    每月3日, 驗證 用户有月报表分析权限为读写的权限的未及时发送邮件, false: 未处理
     **/
    public function isMonthDispatch () {
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $email=Yii::app()->user->email();
        $year = date('Y');
        $day = date('d');
        $month = date('n');
        if($year==2022&&$month==2){//2022年春节处理(2022年2月14日)
            if($day<14){
                $lastdate = date('Y-m-d',strtotime(date('Y-m-14').' -3 months'));
            }else{
                $lastdate = date('Y-m-d',strtotime(date('Y-m-15').' -2 months'));
            }
        }elseif(in_array($month,array(10,5))){//（国庆、五一）特殊处理
            if($day<10){
                $lastdate = date('Y-m-d',strtotime(date('Y-m-10').' -3 months'));
            }else{
                $lastdate = date('Y-m-d',strtotime(date('Y-m-11').' -2 months'));
            }
        }elseif($day<3){ //每月三号以后限制两个月以前的报表汇总
            $lastdate = date('Y-m-d',strtotime(date('Y-m-3').' -3 months'));
        }else{
            $lastdate = date('Y-m-d',strtotime(date('Y-m-4').' -2 months'));
        }
        $year = date("Y", strtotime($lastdate));
        $month = date("n", strtotime($lastdate));
        $sql = "select a_control from security$suffix.sec_user_access 
				where username='$uid' and system_id='drs' and a_read_write like '%H01%'
			";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row===false) return true;
        $subject="月报表总汇-" .$year.'/'.$month;
        $star = date("Y-m-01", strtotime($lastdate));
        $end = date("Y-m-t", strtotime($lastdate));
        $sql = "select * from swoper$suffix.swo_month_email               
                where city='$city' and  request_dt>= '$star' and  request_dt<= '$end' and subject='$subject' 	
			";
        $row = Yii::app()->db->createCommand($sql)->queryAll();
        if(count($row)>=1){
            return true;
        }else{
            return false;
        }
    }

    /**
    檢查一月內的質檢平均分是否低於75分，如果低於75分，需要提示用戶去培訓系統進行測試
     **/
    public function validateExaminationHint() {
        if($this->systemIsCN!=1){
            return true;
        }
        $dateSql = " and replace(b.entry_time,'/', '-')>='2021-01-01' ";//起始日期設置為2021-01-01
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
            ->leftJoin("hr$suffix.hr_dept f","b.position=f.id")
            ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
            ->where("a.user_id=:user_id $dateSql and e.system_id='quiz' and e.a_read_write like '%EM02%' and f.technician=1",array(":user_id"=>$uid))->queryRow();
        if($row){ //技術員需要驗證質檢分數
            $date = date("Y/m/01");
            $date = date("Y-m",strtotime("$date -1 month"));
            $username="(".$row["code"].")";
            $result = Yii::app()->db->createCommand()->select("avg(qc_result) as result")->from("swoper$suffix.swo_qc")
                ->where("date_format(qc_dt,'%Y-%m')=:date and job_staff like '%$username' and date_format(qc_dt,'%Y-%m')>='2021-01'",array(":date"=>$date))->queryScalar();
            if($result!==null){ //該員工有錄入的質檢分數
                $result=floatval($result);
                if($result<75){ //上月的質檢平均分低於75分
                    $nowMonth = date("Y-m");
                    $title = Yii::app()->db->createCommand()->select("MAX(title_num/title_sum)")->from("quiz$suffix.exa_join")
                        ->where("employee_id=:employee_id and date_format(lcd,'%Y-%m')=:date",array(":employee_id"=>$row['id'],":date"=>$nowMonth))->queryScalar();
                    $title = $title===null?0:floatval($title);
                    if($title<0.85){//測驗後的正確率小於85%
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
    檢查上月的質檢平均分是否低於75分，如果低於75分，用戶必须去培訓系統進行測試
     **/
    public function validateExamination() {
        if($this->systemIsCN!=1){
            return true;
        }
        $uid = Yii::app()->user->id;
        $dateSql = " and replace(b.entry_time,'/', '-')>='2021-01-01' ";//起始日期設置為2021-01-01
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
            ->leftJoin("hr$suffix.hr_dept f","b.position=f.id")
            ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
            ->where("a.user_id=:user_id $dateSql and e.system_id='quiz' and e.a_read_write like '%EM02%' and f.technician=1",array(":user_id"=>$uid))->queryRow();

        if($row){//技術員需要驗證質檢分數
            $date = date("Y/m/01");
            $date = date("Y-m",strtotime("$date -2 month"));
            $username="(".$row["code"].")";
            $result = Yii::app()->db->createCommand()->select("date_format(qc_dt,'%Y-%m') as qc_date,avg(qc_result) as result")->from("swoper$suffix.swo_qc")
                ->where("date_format(qc_dt,'%Y-%m')<='$date' and job_staff like '%$username' and date_format(qc_dt,'%Y-%m')>='2021-01'")
                ->group("qc_date")->getText();

            $result = Yii::app()->db->createCommand()->select("a.qc_date")
                ->from("($result) a")
                ->where("a.result<75")//檢查分數是否低於75分
                ->order("a.qc_date desc")
                ->queryScalar();
            if($result){
                $nowMonth = date("Y/m/01");
                $nowMonth = date("Y-m",strtotime("$nowMonth -1 month"));
                $title = Yii::app()->db->createCommand()->select("MAX(title_num/title_sum)")->from("quiz$suffix.exa_join")
                    ->where("employee_id=:employee_id and date_format(lcd,'%Y-%m')>=:date",array(":employee_id"=>$row['id'],":date"=>$nowMonth))->queryScalar();
                $title = $title===null?0:floatval($title);
                if($title<0.85){//測驗後的正確率小於85%
                    return false;
                }
            }
        }
        return true;
    }

    /**
    新同事每次登陆系统限制（三個月以內提示測驗，三個月以後限制使用）
     **/
    public function validateNewStaff($key,&$value) {
        if($this->systemIsCN==0||$this->systemIsCN==1){//大陸、台灣執行
            $dateSql = "replace(b.entry_time,'/', '-')>='2021-01-01' ";//起始日期設置為2021-01-01
            $uid = Yii::app()->user->id;
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name,b.entry_time")->from("hr$suffix.hr_binding a")
                ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
                ->leftJoin("hr$suffix.hr_dept f","b.position=f.id")
                ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
                ->where("$dateSql and a.user_id=:user_id and e.system_id='quiz' and e.a_read_write like '%EM02%' and b.staff_status=0 and f.technician=1",
                    array(":user_id"=>$uid))->queryRow();
            if($row){
                $entry_time = strtotime($row["entry_time"]);
                if(strtotime("- 3 month")<=$entry_time&&time()>=$entry_time){
                    //员工入职三个月以内只提示不限制行为
                    $this->checkItems[$key]["function"]="";
                    $value["function"]="";
                }
                $quizId =General::getQuizIdForMust();
                $title = Yii::app()->db->createCommand()->select("MAX(title_num/title_sum)")->from("quiz$suffix.exa_join")
                    ->where("employee_id=:employee_id and quiz_id='{$quizId}'",array(":employee_id"=>$row['id']))->queryScalar();
                $title = $title===null?0:floatval($title);
                if($title<0.85){//測驗後的正確率小於85%
                    return false;
                }
            }
        }
        return true;
    }

    /**
    新入職員工一個月後提示錄入社会保障卡号
     **/
    public function validateSocialCode($key,&$value) {
        $thisDay = intval(date("d"));
        $maxDay = intval(date("t"));
        $oldDate = date("Y-m-d",strtotime("-1 months"));
        if($thisDay==16||$thisDay==$maxDay){//每月16號及最後一天彈窗提示
            $uid = Yii::app()->user->id;
            $city = Yii::app()->user->city();
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("b.id")->from("hr$suffix.hr_binding a")
                ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
                ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
                ->where("a.user_id=:user_id and e.system_id='hr' and (e.a_read_write like'%ZE01%' or e.a_read_write like'%ZG01%')",array(":user_id"=>$uid))->queryRow();
            if($row){//有員工錄入及入職審核權限
                $dateSql = "replace(entry_time,'/', '-')<='{$oldDate}' ";//一個月以前
                $staffRows = Yii::app()->db->createCommand()->select("name")->from("hr$suffix.hr_employee")
                    ->where("city=:city and staff_status in (0,4) and (social_code='' or social_code is null) and $dateSql",array(":city"=>$city))->queryAll();
                if($staffRows){
                    //有員工沒有填社保號
                    $staffList = array();
                    foreach ($staffRows as $staff){
                        $staffList[] = $staff["name"];
                    }
                    $staffList = implode("、",$staffList);
                    $value["message"].="<br>".Yii::t("block","username")."：".$staffList;
                    $this->checkItems[$key]["function"]="";
                    $value["function"]="";
                    return false;
                }

            }
        }
        return true;
    }

    /**
     * 技術員每年必須測驗一次
     **/
    public function EveryYearForExamination($key,&$value){
        if($this->systemIsCN!=0){//進大陸執行
            return true;
        }
        $dateSql = "replace(b.entry_time,'/', '-')<'2021-01-01' ";//入職日期小於2021-01-01
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name,b.entry_time")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
            ->leftJoin("hr$suffix.hr_dept f","b.position=f.id")
            ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
            ->where("$dateSql and a.user_id=:user_id and e.system_id='quiz' and e.a_read_write like '%EM02%' and b.staff_status=0 and f.technician=1",
                array(":user_id"=>$uid))->queryRow();
        if($row){
            $dateTime = time();
            //$dateTime = strtotime("2022-01-21");
            $entryM = date("m",strtotime($row["entry_time"]));
            $entryMD = date("m-d",strtotime($row["entry_time"]));
            $dateYear = date("Y",$dateTime);
            $dateMonth = date("m-d",$dateTime);
            //如果員工的入職月份在12月，則計算上一年是否測試
            $dateYear = (intval($entryM)>11&&$dateMonth<$entryMD)?$dateYear-1:$dateYear;
            //$entryDate = date("Y-m-d",strtotime($row["entry_time"]));
            $entryM = "{$dateYear}-{$entryM}-01";
            $entryMD = $dateYear."-".$entryMD;
            $hindStartDate = strtotime($entryMD);
            $hindEndDate = strtotime("$entryM + 2 month -1 day");
            $sqlStartDate = " and date_format(lcd,'%Y-%m-%d')>='{$entryMD}'";
            $quizId =General::getQuizIdForMust();
            if($dateTime<$hindStartDate){
                //需要查以前有沒有測驗（不限時間）
                $sqlStartDate = "";
            }elseif($hindEndDate>=$dateTime&&$hindStartDate<=$dateTime){
                //只提示不限制行为
                $this->checkItems[$key]["function"]="";
                $value["function"]="";
            }
            $title = Yii::app()->db->createCommand()->select("MAX(title_num/title_sum)")->from("quiz$suffix.exa_join")
                ->where("employee_id=:employee_id and quiz_id='{$quizId}' $sqlStartDate",array(":employee_id"=>$row['id']))->queryScalar();
            $title = $title===null?0:floatval($title);
            if($title<0.85){//測驗後的正確率小於85%
                return false;
            }
        }
        return true;
    }

    public function test() {
        return false;
    }
}
?>