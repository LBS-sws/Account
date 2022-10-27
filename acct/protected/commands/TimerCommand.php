<?php
class TimerCommand extends CConsoleCommand
{

    public function run()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $firstDay = date("Y/m/d");
        $sql = "SELECT g.name as city_name,a.*,f.field_value,
workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt) as wfstatusdesc
FROM acc_request a left outer join acc_request_info f on f.req_id = a.id and f.field_id='ref_no'
LEFT JOIN security$suffix.sec_city g ON a.city = g.code
WHERE workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>'ED' and workflow$suffix.RequestStatus('PAYMENT',a.id,a.req_dt)<>''";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $firstDay = date("Y/m/d", strtotime("$firstDay - 30 day"));
        $firstDay1 = date("Y/m/d", strtotime("$firstDay - 60 day"));
        $firstDay2 = date("Y/m/d", strtotime("$firstDay - 90 day"));
        foreach ($records as $k => $record) {
            $from_addr = "it@lbsgroup.com.hk";
            $allEmail = array();
            $record['req_dt'] = date_format(date_create($record['req_dt']), "Y/m/d");

                if ($record['req_dt'] == $firstDay) { //30天提醒
                    $this->addAccountAllEmail($record,$allEmail);
                    //地区老总
                    $sql = "select b.email from acc_approver a
                    LEFT JOIN security$suffix.sec_user b ON a.username=b.username
                    where a.city='" . $record['city'] . "' and a.approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryRow();
                    if (!empty($rows['email'])&&!in_array($rows['email'],$allEmail)){
                        $allEmail[] = $rows['email'];
                    }
					$to_addr = json_encode($allEmail);
                    $subject = "付款申请报销提醒-" . $record['field_value'];
                    $description = "付款申请报销提醒-" . $record['field_value'];
                    $message = "单号：" . $record['field_value'] ."，城市：" . $record['city_name'] . ",申请日期为：" . $record['req_dt'] . "金额为：" . $record['amount'] . "的申请已过一个月，报销仍未完成";
                    $lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                } elseif ($record['req_dt'] == $firstDay1) {
                    $this->addAccountAllEmail($record,$allEmail);
//                    $sql = "select approver_type, username from acc_approver where city='" . $record['city'] . "' and approver_type='regionDirector'";
// 包括副總監
                    $sql = "select distinct username from acc_approver where city='" . $record['city'] . "' and approver_type in ('regionDirector', 'regionDirectorA')";

                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zj = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zj'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();
                    $from_addr = "it@lbsgroup.com.hk";
                    if(!empty($rs[0]['email'])&&!in_array($rs[0]['email'],$allEmail)){
                        $allEmail[]=$rs[0]['email'];
                    }
                    $to_addr = json_encode($allEmail);
                    $subject = "付款申请报销提醒-" . $record['field_value'];
                    $description = "付款申请报销提醒-" . $record['field_value'];
                    $message = "单号：" . $record['field_value'] ."，城市：" . $record['city_name'] . ",申请日期为：" . $record['req_dt'] . "金额为：" . $record['amount'] . "的申请已过两个月，报销仍未完成";
                    $lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                } elseif ($record['req_dt'] == $firstDay2) {
                    $this->addAccountAllEmail($record,$allEmail);
                    $sql = "select approver_type, username from acc_approver where city='" . $record['city'] . "' and approver_type='regionHead'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $rs = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$rs'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();
                    if(!empty($rs[0]['email'])&&!in_array($rs[0]['email'],$allEmail)){
                        $allEmail[]=$rs[0]['email'];
                    }
                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = json_encode($allEmail);
                    $subject = "付款申请报销提醒-" . $record['field_value'];
                    $description = "付款申请报销提醒-" . $record['field_value'];
                    $message = "单号：" . $record['field_value'] ."，城市：" . $record['city_name'] . ",申请日期为：" . $record['req_dt'] . "金额为：" . $record['amount'] . "的申请已过三个月，报销仍未完成";
                    $lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                }

        }

        //刷新直升机机制的奖金
        $this->resetPlane();
    }

    private function resetPlane(){
        $planeDate = date("Y-m-01");
        $planeDate = date("Y-m-d",strtotime("{$planeDate} - 1 months"));
        $rows = Yii::app()->db->createCommand()->select("id")->from("acc_plane")
            ->where("plane_date>='{$planeDate}'")->queryAll();
        $model = new PlaneAwardForm('view');
        if($rows){
            foreach ($rows as $row){
                $model->retrieveData($row["id"],false);
            }
        }

    }


    //查找管轄某城市的所有城市（根據小城市查找大城市）
    private function getAllCityToMinCity($minCity){
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
                $foreachList = $this->getAllCityToMinCity($row["region"]);
                $cityList = array_merge($foreachList,$cityList);
            }
        }

        return $cityList;
    }

    //添加地區負責人郵件
    private function addEmailForCityList($city_allow,&$email){
        if(!empty($city_allow)){
            $city_allow = implode("','",$city_allow);
            $sql = "a.code in ('$city_allow')";
        }else{
            return false;
        }
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.email, b.username")
            ->from("security$suffix.sec_city a")
            ->leftJoin("security$suffix.sec_user b","a.incharge=b.username")
            ->where("b.email != '' and b.status='A' and {$sql}")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                if(!in_array($row["email"],$email)){
                    $email[]=$row["email"];
                }
            }
        }
        return true;
    }

    //添加會計系統的郵件提示郵件
    private function addAccountAllEmail($record,&$allEmail){
        $suffix = Yii::app()->params['envSuffix'];
        //会计
        $sql = "SELECT a.email FROM security$suffix.sec_user a,security$suffix.sec_user_access b WHERE a.username=b.username AND a.city='" . $record['city'] . "' AND b.a_control LIKE '%CN04%' AND a.status='A' AND b.system_id='acct'";
        $recordes = Yii::app()->db->createCommand($sql)->queryAll();
        if ($recordes){//由於會計可能有多個所以循環添加會計郵箱
            foreach ($recordes as $accEmail){
                if(!in_array($accEmail["email"],$allEmail)){
                    $allEmail[] = $accEmail["email"];
                }
            }
        }
        //出纳
        $sql = "SELECT email from security$suffix.sec_user WHERE username='" . $record['req_user'] . "'";
        $recordss = Yii::app()->db->createCommand($sql)->queryRow();
        if (!empty($recordss['email'])&&!in_array($recordss['email'],$allEmail)){
            $allEmail[] = $recordss['email'];
        }
        //地區負責人
        $cityMax = $this->getAllCityToMinCity($record['city']);
        $this->addEmailForCityList($cityMax,$allEmail);
    }
}

?>
