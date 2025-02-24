<?php

class Email {

    //强制刷新本文件
    protected $to_addr=array();//收信人郵箱
    protected $subject;//郵件主題
    protected $description;//郵件副題
    protected $message;//郵件內容（html）

	protected $to_user=array(); 	//因通知記錄需要
	protected $form_id='Email';
	protected $rec_id=0;
	protected $attrList=array();//附件

    public function __construct($subject="",$message="",$description=""){
        $this->subject = $subject;
        $this->message = $message;
        $this->description = $description;
    }

    public function insertAttr($file_name,$content){
        $this->attrList[] = array('name'=>$file_name,'content'=>$content);
    }

    public function resetAttr(){
        $this->attrList = array();
    }

    public function setMessage($message){
        $this->message = $message;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function setSubject($subject){
        $this->subject = $subject;
    }

    public function getSubject(){
        return $this->subject;
    }

    public function getToAddr(){
        return $this->to_addr;
    }

    public function getToUser(){
        return $this->to_user;
    }

    public function resetToAddr(){
        $this->to_addr = array();
		$this->to_user = array();	//Percy: 2021-04-16
    }

    //添加收信人
    public function addToAddrEmail($list){
        if(!is_array($list)){
            $this->to_addr[] = $list;
        }else{
            $this->to_addr = array_merge($this->to_addr,$list);
        }
    }

    //添加通知人
    public function addToAddrUser($list){
        if(!is_array($list)){
            $this->to_user[] = $list;
        }else{
            $this->to_user = array_merge($this->to_user,$list);
        }
    }

    //獲取繞生郵件
    public function getJoeEmail(){
        $suffix = Yii::app()->params['envSuffix'];
        $rs = Yii::app()->db->createCommand()->select("b.email")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge=b.username")
            ->where("a.code = 'CN' and b.status='A'")
            ->queryRow();
        if($rs){
            return $rs["email"];
        }else{
            return "joeyiu@lbsgroup.com.cn";
        }
    }

    //獲取繞生级别的所有邮箱
    public function getJoeEmailList(){
        return array(
            "joeyiu@lbsgroup.com.cn",//饶生
            "autumn.song@lbsgroup.com.cn",//人力资源总监
        );
    }

    //獲取Kitty郵件
    public function getKittyEmail(){
        return "kittyzhou@lbsgroup.com.cn";
    }

    //獲取重要地區總監的郵件
    public function getJoeEmailAndMore(){
        $suffix = Yii::app()->params['envSuffix'];
        $rs = Yii::app()->db->createCommand()->select("b.email")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge=b.username")
            ->where("a.code in ('CN','HD','HN','HXHB') and b.status='A'")
            ->queryAll();
        if($rs){
            return array_column($rs,'email');
        }else{
            return array("joeyiu@lbsgroup.com.cn");
        }
    }

    //添加收信人(根據權限）
    public function addEmailToPrefix($str,$city=''){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $city = empty($city)?Yii::app()->user->city():$city;
        $cityList = $this->getAllCityToMinCity($city);
        if(count($cityList)>1){
            $cityList = "'".implode("','",$cityList)."'";
            $sql = " and b.city in ($cityList) ";
        }else{
            $sql = " and b.city = '$city' ";
        }
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_user_access a")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->where("a.system_id='$systemId' and a.a_read_write like '%$str%' $sql and b.email != '' and b.status='A'")
            ->queryAll();
        if($rs){
            foreach ($rs as $row){
                if(!in_array($row["email"],$this->to_addr)){
                    $this->to_addr[] = $row["email"];
                }
                if(!in_array($row["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $row["username"];
                }
            }
        }
    }

    //添加收信人(根據權限-不包括城市）
    public function addEmailToPrefixNullCity($str){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_user_access a")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->where("a.system_id='$systemId' and a.a_read_write like '%$str%' and b.email != '' and b.status='A'")
            ->queryAll();
        if($rs){
            foreach ($rs as $row){
                if(!in_array($row["email"],$this->to_addr)){
                    $this->to_addr[] = $row["email"];
                }
                if(!in_array($row["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $row["username"];
                }
            }
        }
    }

    //添加收信人(地區老總）
    public function addEmailToCity($city){
        $suffix = Yii::app()->params['envSuffix'];
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge=b.username")
            ->where("a.code='$city' and b.email != '' and b.status='A'")
            ->queryRow();
        if($rs){
            if(!empty($rs["email"])){
                if(!in_array($rs["email"],$this->to_addr)){
                    $this->to_addr[] = $rs["email"];
                }
                if(!in_array($rs["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $rs["username"];
                }
            }
        }
    }

    //添加收信人(所有地區老總）
    public function addEmailToAllCity(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge=b.username")
            ->where("b.email != '' and b.status='A'")
            ->queryAll();
        if($rows){
            foreach ($rows as $rs){
                if(!empty($rs["email"])){
                    if(!in_array($rs["email"],$this->to_addr)){
                        $this->to_addr[] = $rs["email"];
                    }
                    if(!in_array($rs["username"],$this->to_user)){	//因通知記錄需要
                        $this->to_user[] = $rs["username"];
                    }
                }
            }
        }
    }

    //
    public function getEmailUserList($city_allow,$usernameEx="",$notUsername=""){
        if(!empty($city_allow)){
            $city_allow = implode("','",$city_allow);
            $sql = "a.city in ('CN','$city_allow')";
            if(!empty($usernameEx)){//額外的lcu
                $sql = " (a.city in ('CN','$city_allow') or a.username='{$usernameEx}')";
            }
            if(!empty($notUsername)){
                $sql.= " and a.username!='{$notUsername}'";
            }
        }else{
            return false;
        }
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        $rows = Yii::app()->db->createCommand()->select("a.username,a.city,a.email,(CASE WHEN a.username IN (SELECT incharge FROM security$suffix.sec_city) THEN 1 ELSE 0 END) AS incharge,c.a_read_write")
            ->from("security$suffix.sec_user a")
            ->leftJoin("security$suffix.sec_city b","a.city = b.code")
            ->leftJoin("security$suffix.sec_user_access c","a.username = c.username")
            ->where("a.status = 'A' AND a.email != '' AND c.system_id = '$systemId' AND $sql")
            ->order("a.city desc")
            ->queryAll();
        if($rows){
            $list = array();
            foreach ($rows as $row){
                $list[$row["username"]] = $row;
            }
            return $list;
        }
        return false;
    }

    //添加收信人(只有地區總監收到）
    public function addEmailToOnlyCityBoss($city,$notUser=array()){
        $uidList = $this->getBossUidToMinCity($city);
        if(empty($city)){
            return "";
        }else{
            foreach ($uidList as $uid){
                if(!in_array($uid,$notUser)){
                    $this->addEmailToLcu($uid);
                }
            }
        }
    }

    //添加收信人(根據權限）
    public function addEmailToPrefixAndCity($str,$city,$notEmail=array(),$readyType=2){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        //$city = Yii::app()->user->city();
        $cityList = $this->getAllCityToMinCity($city);
        switch ($readyType){
            case 1://唯读
                $readStr="a_read_only";
                break;
            case 2://读写
                $readStr="a_read_write";
                break;
            case 3://其它
                $readStr="a_control";
                break;
            default:
                $readStr="a_read_write";

        }
        if(count($cityList)>1){
            $cityList = "'".implode("','",$cityList)."'";
            $sql = " and b.city in ($cityList) ";
        }else{
            $sql = " and b.city = '$city' ";
        }
        if(!is_array($str)){
            $likeSql = " and a.{$readStr} like '%$str%'";
        }else{
            $likeSql =" and (";
            foreach ($str as $key =>$item){
                if($key != 0){
                    $likeSql.=" or ";
                }
                $likeSql .= "a.{$readStr} like '%$item%'";
            }
            $likeSql .=")";
        }
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_user_access a")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->where("a.system_id='$systemId' $likeSql $sql and b.email != '' and b.status='A'")
            ->queryAll();
        if($rs){
            foreach ($rs as $row){
                if(!in_array($row["email"],$this->to_addr)&&!in_array($row["email"],$notEmail)){
                    $this->to_addr[] = $row["email"];
                }
                if(!in_array($row["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $row["username"];
                }
            }
        }
    }

    //添加收信人(根據權限和單個城市）
    public function addEmailToPrefixAndOnlyCity($str,$city){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        //$city = Yii::app()->user->city();
        $sql = " and b.city = '$city' ";
        if(!is_array($str)){
            $likeSql = " and a.a_read_write like '%$str%'";
        }else{
            $likeSql =" and (";
            foreach ($str as $key =>$item){
                if($key != 0){
                    $likeSql.=" or ";
                }
                $likeSql .= "a.a_read_write like '%$item%'";
            }
            $likeSql .=")";
        }
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("security$suffix.sec_user_access a")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->where("a.system_id='$systemId' $likeSql $sql and b.email != '' and b.status='A'")
            ->queryAll();
        if($rs){
            foreach ($rs as $row){
                if(!in_array($row["email"],$this->to_addr)){
                    $this->to_addr[] = $row["email"];
                }
                if(!in_array($row["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $row["username"];
                }
            }
        }
    }

    //獲取用戶列表(根據權限和單個城市）
    public function getUserListToPrefix($str){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        //$city = Yii::app()->user->city();
        if(!is_array($str)){
            $likeSql = " and a.a_read_write like '%$str%'";
        }else{
            $likeSql =" and (";
            foreach ($str as $key =>$item){
                if($key != 0){
                    $likeSql.=" or ";
                }
                $likeSql .= "a.a_read_write like '%$item%'";
            }
            $likeSql .=")";
        }
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username, b.city, h.name as city_name, d.id, d.name, d.code")->from("hr$suffix.hr_binding e")
            ->leftJoin("hr$suffix.hr_employee d","d.id = e.employee_id")
            ->leftJoin("hr$suffix.hr_dept f","f.id = d.position")
            ->leftJoin("security$suffix.sec_user_access a","a.username = e.user_id")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->leftJoin("security$suffix.sec_city h","b.city=h.code")
            ->where("a.system_id='$systemId' $likeSql and b.email != '' and b.status='A'")
            ->queryAll();
        return $rs;
    }

    //添加收信人(根據權限和部門）
    public function addEmailToPrefixAndPoi($str,$department,$groupType=0){
        $suffix = Yii::app()->params['envSuffix'];
        $systemId = Yii::app()->params['systemId'];
        //$city = Yii::app()->user->city();
        $sql = " and d.department = '$department' ";
        if(!empty($groupType)){
            $sql.=" and d.group_type in (0,$groupType) ";
        }
        if(!is_array($str)){
            $likeSql = " and a.a_read_write like '%$str%'";
        }else{
            $likeSql =" and (";
            foreach ($str as $key =>$item){
                if($key != 0){
                    $likeSql.=" or ";
                }
                $likeSql .= "a.a_read_write like '%$item%'";
            }
            $likeSql .=")";
        }
        $rs = Yii::app()->db->createCommand()->select("b.email, b.username")->from("hr$suffix.hr_binding e")
            ->leftJoin("hr$suffix.hr_employee d","d.id = e.employee_id")
            ->leftJoin("hr$suffix.hr_dept f","f.id = d.position")
            ->leftJoin("security$suffix.sec_user_access a","a.username = e.user_id")
            ->leftJoin("security$suffix.sec_user b","a.username=b.username")
            ->where("a.system_id='$systemId' $likeSql $sql and b.email != '' and b.status='A'")
            ->queryAll();
        if($rs){
            foreach ($rs as $row){
                if(!in_array($row["email"],$this->to_addr)){
                    $this->to_addr[] = $row["email"];
                }
                if(!in_array($row["username"],$this->to_user)){	//因通知記錄需要
                    $this->to_user[] = $row["username"];
                }
            }
        }
    }

    //添加收信人(lcu）
    public function addEmailToLcu($lcu){
        $suffix = Yii::app()->params['envSuffix'];
        $email = Yii::app()->db->createCommand()->select("email, username")->from("security$suffix.sec_user")
            ->where("username=:username and email !='' and status='A'",array(":username"=>$lcu))
            ->queryRow();
        if($email){
            if(!in_array($email["email"],$this->to_addr)){
                $this->to_addr[] = $email["email"];
            }
            if(!in_array($email["username"],$this->to_user)){	//因通知記錄需要
                $this->to_user[] = $email["username"];
            }
        }
    }

    //添加收信人(員工id）
    public function addEmailToStaffId($staffId){
        $suffix = Yii::app()->params['envSuffix'];
        $email = Yii::app()->db->createCommand()->select("b.email, b.username")->from("hr$suffix.hr_binding a")
            ->leftJoin("security$suffix.sec_user b","b.username = a.user_id")
            ->where("a.employee_id=:employee_id and b.email !='' and b.status='A'",array(":employee_id"=>$staffId))
            ->queryRow();
        if($email){
            if(!in_array($email["email"],$this->to_addr)){
                $this->to_addr[] = $email["email"];
            }
            if(!in_array($email["username"],$this->to_user)){	//因通知記錄需要
               $this->to_user[] = $email["username"];
            }
        }
    }

    public function deleteEmail($list){
        if(is_array($list)){
            $userList = array_column($list,"username");
            $emailList = array_column($list,"email");
            $this->to_user = array_diff($this->to_user,$userList);
            $this->to_addr = array_diff($this->to_addr,$emailList);
        }
    }

    //發送郵件
    public function sent($uid="",$systemId="",$request_dt=""){
        $request_dt = empty($request_dt)?date('Y-m-d H:i:s'):$request_dt;
        if(empty($this->to_addr)){ //後期修改，如果沒有收件人不發送郵件
            return false;
        }
        $to_addr = empty($this->to_addr)?json_encode(array("it@lbsgroup.com.hk")):json_encode($this->to_addr);
        if(empty($uid)){
            $uid = Yii::app()->user->id;
        }
        if(empty($systemId)){
            $systemId = Yii::app()->user->system();
        }
        $from_addr = Yii::app()->params['adminEmail'];
        $suffix = Yii::app()->params['envSuffix'];
        $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
            'request_dt'=>$request_dt,
            'from_addr'=>$from_addr,
            'to_addr'=>$to_addr,
            'subject'=>$this->subject,//郵件主題
            'description'=>$this->description,//郵件副題
            'message'=>$this->message,//郵件內容（html）
            'status'=>"P",
            'lcu'=>$uid,
            'lcd'=>date('Y-m-d H:i:s'),
        ));

        if(!empty($this->attrList)){
            $id = Yii::app()->db->getLastInsertID();
            if(is_numeric($id)&&!empty($id)){
                foreach ($this->attrList as $attr){
                    $attr["queue_id"]=$id;
                    Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue_attm", $attr);
                }
            }
        }

		//新增通知記錄
        if(!empty($this->to_user)){
            $connection = Yii::app()->db;
            SystemNotice::addNotice($connection, array(
                    'note_type'=>'notice',
                    'subject'=>$this->subject,//郵件主題
                    'description'=>$this->description,//郵件副題
                    'message'=>$this->message,
                    'username'=>json_encode($this->to_user),
                    'system_id'=>$systemId,
                    'form_id'=>$this->form_id,
                    'rec_id'=>$this->rec_id,
                )
            );
        }
   }

    //查找管轄某城市的所有城市（根據小城市查找大城市）
    public function getAllCityToMinCity($minCity){
        if(empty($minCity)){
            return array();
        }
        $cityList = array($minCity);
        $suffix = Yii::app()->params['envSuffix'];
        $command = Yii::app()->db->createCommand();
        $rows = $command->select("region")->from("security$suffix.sec_city")
            ->where("code=:code",array(":code"=>$minCity))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $foreachList = Email::getAllCityToMinCity($row["region"]);
                $cityList = array_merge($foreachList,$cityList);
            }
        }

        return $cityList;
    }

    //查找管轄某城市的boos城市的負責人（根據小城市查找大城市）
    public function getBossUidToMinCity($minCity){
        if(empty($minCity)){
            return array();
        }
        $userList = array();
        if(is_array($minCity)){
            $userList = array($minCity["incharge"]);
            $minCity = $minCity["region"];
        }
        //$arrList=array("华南","华西","华北","华东");
        $suffix = Yii::app()->params['envSuffix'];
        $command = Yii::app()->db->createCommand();
        $rows = $command->select("*")->from("security$suffix.sec_city")
            ->where("code=:code",array(":code"=>$minCity))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $foreachList = Email::getBossUidToMinCity($row);
                $userList = array_merge($foreachList,$userList);
            }
        }

        return $userList;
    }

    //查找某城市管轄下的所有城市（根據大城市查找小城市）
    public function getAllCityToMaxCity($maxCity){
        if(empty($maxCity)){
            return array();
        }
        $cityList = array($maxCity);
        $suffix = Yii::app()->params['envSuffix'];
        $command = Yii::app()->db->createCommand();
        $rows = $command->select("code")->from("security$suffix.sec_city")
            ->where("region=:region",array(":region"=>$maxCity))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $foreachList = Email::getAllCityToMaxCity($row["code"]);
                $cityList = array_merge($foreachList,$cityList);
            }
        }

        return $cityList;
    }

    //查找華南、華北、華西、華東老總、繞生、林生
    public function getOnlyLRTMUser(){
        $userList = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.code,b.username,b.email")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge = b.username")
            ->where("b.status = 'A' and a.code in('HD','HD1','HN','HN1','HN2','HXHB')")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $userList[] = $row['username'];
            }
        }
        $userList[] = "Franco";
        $userList[] = "joe";

        return $userList;
    }

    //查找華南、華北、華西、華東老總、繞生、林生
    public function getOnlyLRTMUserList(){
        $userList = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.code,b.username,b.email")->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge = b.username")
            ->where("b.status = 'A' and a.code in('HD','HD1','HN','HN1','HN2','HXHB')")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $cityList = $this->getAllCityToMaxCity($row["code"]);
                $userList[] = array('username'=>$row['username'],'email'=>$row['email'],'cityList'=>$cityList);
            }
        }
        $systemName = Yii::app()->name;
        if (strpos($systemName,'UAT')===false){ //測試版不需要該郵件
            $userList[] = array('username'=>"Franco",'email'=>"flam@lbsgroup.com.hk",'cityList'=>array());
            $userList[] = array('username'=>"joe",'email'=>"joeyiu@lbsgroup.com.cn",'cityList'=>array());
        }
        return $userList;
    }

    //需要添加額外郵箱地址(支點郵件通知)
    public function addSupportPreEmailToEmployeeId($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("support_city")->from("hr$suffix.hr_apply_support_email")
            ->where("employee_id=:id",array(":id"=>$employee_id))->queryRow();
        if($row){
            $this->addEmailToPrefixAndOnlyCity("AY01",$row["support_city"]);
        }
    }

    //中央支援郵箱，需要添加額外郵箱地址
    public function addSupportPreEmail(){
        $email_address = array(
            array('email'=>"candy@lbsgroup.com.cn",'username'=>"candy"),
            array('email'=>"tak@lbsgroup.com.cn",'username'=>"tak"),
            array('email'=>"shunman@lbsgroup.com.cn",'username'=>"shunman"),
            array('email'=>"kittyzhou@lbsgroup.com.cn",'username'=>"kittyzhou")
        );
        foreach ($email_address as $email){
            if(!in_array($email["email"],$this->to_addr)){
                $this->to_addr[] = $email["email"];
            }
            if(!in_array($email["username"],$this->to_user)){	//因通知記錄需要
                $this->to_user[] = $email["username"];
            }
        }
    }

    //台灣地區的考核，只發送以下給郵件
    /*Ernest: ernest
Pamela: pamela
Riki: TPA0016
玉琴: TPA0004*/
    public function addEmailOnlyRetire(){
        $email_address = array(
            array('email'=>"eyu@lbsgroup.com.tw",'username'=>"ernest"),
            array('email'=>"pamela.tp@lbsgroup.com.tw",'username'=>"pamela"),
            array('email'=>"ting.tp@lbsgroup.com.tw",'username'=>"TPA0036"),
            //array('email'=>"riki.tp@lbsgroup.com.tw",'username'=>"TPA0016"),
            //array('email'=>"ginny.tp@lbsgroup.com.tw",'username'=>"TPA0004")
        );
        foreach ($email_address as $email){
            if(!in_array($email["email"],$this->to_addr)){
                $this->to_addr[] = $email["email"];
            }
            if(!in_array($email["username"],$this->to_user)){	//因通知記錄需要
                $this->to_user[] = $email["username"];
            }
        }
    }
}
?>