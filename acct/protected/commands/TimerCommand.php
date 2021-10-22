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
                $record['req_dt'] = date_format(date_create($record['req_dt']), "Y/m/d");
                if ($record['req_dt'] == $firstDay) {
                    //会计
                    $sql = "SELECT a.email FROM security$suffix.sec_user a,security$suffix.sec_user_access b WHERE a.username=b.username AND a.city='" . $record['city'] . "' AND b.a_control LIKE '%CN04%' AND a.status='A' AND b.system_id='acct'";
                    $recordes = Yii::app()->db->createCommand($sql)->queryAll();
                    //出纳
                    $sql = "SELECT email from security$suffix.sec_user WHERE username='" . $record['req_user'] . "'";
                    $recordss = Yii::app()->db->createCommand($sql)->queryAll();
                    //地区老总
                    $sql = "select approver_type, username from acc_approver where city='" . $record['city'] . "' and approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zjl = $rows[0]['username'];

                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zjl'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();

                    $from_addr = "it@lbsgroup.com.hk";
//                    $to_addr = "[" . $recordes[0]['email'] . "," . $recordss[0]['email'] . "," . $rs[0]['email'] . "]";
// 以上格式不能發送 , 不是正確 JSON
					$tmp = array();
					if (!empty($recordss[0]['email'])) $tmp[] = $recordss[0]['email'];
					if (!empty($rs[0]['email'])) $tmp[] = $rs[0]['email'];
                    if ($recordes){//由於會計可能有多個所以循環添加會計郵箱
                        foreach ($recordes as $accEmail){
                            if(!in_array($accEmail,$tmp)){
                                $tmp[] = $accEmail;
                            }
                        }
                    }
					$to_addr = json_encode($tmp);
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
//                    $sql = "select approver_type, username from acc_approver where city='" . $record['city'] . "' and approver_type='regionDirector'";
// 包括副總監
                    $sql = "select distinct username from acc_approver where city='" . $record['city'] . "' and approver_type in ('regionDirector', 'regionDirectorA')";

                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zj = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zj'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();
                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = '["' . $rs[0]['email'] . '"]';
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
                    $sql = "select approver_type, username from acc_approver where city='" . $record['city'] . "' and approver_type='regionHead'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $rs = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$rs'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();
                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = "[" . $rs[0]['email'] . "]";
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
    }
}

?>
