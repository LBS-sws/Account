<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/3/18 0018
 * Time: 9:17
 */
class BsCurlModel
{
    public $url="/compensationv2/v2/PresetSalarySubset/AddOrEdit";
    public $sendData=array();

    public function sendBsCurl() {
        $root = Yii::app()->params['BSCurlRootURL'];
        $endUrl = $root.$this->url;
        $rtn = array('message'=>'', 'code'=>400,'outData'=>'');//成功时code=200；
        $tokenModel = new BSToken();
        $tokenList = $tokenModel->getToken();
        if($tokenList["status"]===true){
            $data_string = json_encode($this->sendData);
            $ch = curl_init($endUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'Authorization:Bearer '.$tokenList["token"],
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $out = curl_exec($ch);
            if ($out===false) {
                $rtn['message'] = curl_error($ch);
                $rtn['outData'] = $rtn['message'];
            } else {
                $rtn['outData'] = $out;
                $json = json_decode($out, true);
                if(is_array($json)&&key_exists("code",$json)&&$json["code"]==200){
                    $rtn['code'] = 200;
                    $rtn['dataJson'] = $json;
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:self::str_max_len($out);
                }
            }
        }else{
            $rtn['outData'] = $tokenList["message"];
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }
        return $rtn;
    }

    public static function str_max_len($message){
        $message = is_array($message)?json_encode($message):$message;
        $message = mb_strlen($message)>250?mb_substr($message,0,250,'UTF-8'):$message;
        return $message;
    }

    public function logError($data){
        $message= "\n";
        $message.= "sendUrl:".Yii::app()->params['BSCurlRootURL'].$this->url;
        $message.="\n";
        $message.= "sendData:".json_encode($this->sendData);
        $message.="\n";
        if(isset($data["outData"])){
            $message.= "outData:".$data["outData"];
            $message.="\n";
        }
        Yii::log($message, CLogger::LEVEL_ERROR, 'application');
    }

    public function setMoneyDataForModel($model){
        $data = array(
            "presetSalarySubsetCode"=>"PresetSalarySubset1",
            "models"=>array()
        );
        $str = get_class($model);
        switch ($str){
            case "sellTableForm"://销售提成计算
                array(
                    "staffId"=>660006277,
                    "startDate"=>"2020/01/01",
                    "isInvalid"=>false,
                    "status"=>true,
                    "stopDate"=>"2020/01/02",
                    "numericVal"=>1000.0,
                );
                break;
        }
        $this->sendData = $data;
    }
}