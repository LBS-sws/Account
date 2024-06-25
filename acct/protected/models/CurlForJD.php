<?php
//仓库物品的curl
class CurlForJD{
    protected $info_type="warehouse";
    protected $saveArr=array();

    protected function sendData($data,$url) {
        $root = Yii::app()->params['JDCurlRootURL'];
        $endUrl = $root.$url;
        $rtn = array('message'=>'', 'code'=>400,'outData'=>'');//成功时code=200；
        $tokenModel = new JDToken();
        $tokenList = $tokenModel->getToken();
        $sendDate = date_format(date_create(),"Y/m/d H:i:s");
        if($tokenList["status"]===true){
            $data_string = json_encode($data);

            $ch = curl_init($endUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'accessToken:'.$tokenList["token"],
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
                if(is_array($json)&&key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn['code'] = 200;
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:"";
                }
            }
        }else{
            $rtn['outData'] = $tokenList["message"];
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }

        $this->saveArr = array(
            "status_type"=>$rtn['code']==200?"C":"E",
            "info_type"=>$this->info_type,
            "info_url"=>$endUrl,
            "min_url"=>$url,
            "data_content"=>json_encode($data),
            "out_content"=>$rtn['outData'],
            "message"=>$rtn['message'],
            "lcu"=>Yii::app()->user->id,
            "lcd"=>$sendDate,
        );
        return $rtn;
    }

    public function saveTableForArr(){
        if(!empty($this->saveArr)){
            $suffix = Yii::app()->params['envSuffix'];
            Yii::app()->db->createCommand()->insert("operation{$suffix}.opr_api_curl",$this->saveArr);
        }
    }

    protected static function sendDataForJD($data,$url,$info_type="warehouse") {
        $root = Yii::app()->params['JDCurlRootURL'];
        $endUrl = $root.$url;
        $rtn = array('message'=>'', 'code'=>400,'outData'=>'');//成功时code=200；
        $tokenModel = new JDToken();
        $tokenList = $tokenModel->getToken();
        $sendDate = date_format(date_create(),"Y/m/d H:i:s");
        if($tokenList["status"]===true){
            $data_string = json_encode($data);

            $ch = curl_init($endUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'accessToken:'.$tokenList["token"],
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
                if(is_array($json)&&key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn['code'] = 200;
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:"";
                }
            }
        }else{
            $rtn['outData'] = $tokenList["message"];
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }

        $sqlData=array(
            "status_type"=>$rtn['code']==200?"C":"E",
            "info_type"=>$info_type,
            "info_url"=>$endUrl,
            "min_url"=>$url,
            "data_content"=>json_encode($data),
            "out_content"=>$rtn['outData'],
            "message"=>$rtn['message'],
            "lcu"=>Yii::app()->user->id,
            "lcd"=>$sendDate,
        );
        $suffix = Yii::app()->params['envSuffix'];
        Yii::app()->db->createCommand()->insert("operation{$suffix}.opr_api_curl",$sqlData);
        return $rtn;
    }

    public static function sendUpdateRowForJD($row) {
        $rtn = array('message'=>'', 'code'=>400,'outData'=>'');//成功时code=200；
        $tokenModel = new JDToken();
        $tokenList = $tokenModel->getToken();
        $url = $row["min_url"];
        $data = json_decode($row["data_content"],true);
        $sendDate = date_format(date_create(),"Y/m/d H:i:s");
        if($tokenList["status"]===true){
            $root = Yii::app()->params['JDCurlRootURL'];
            $url = $root.$url;
            $data_string = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'accessToken:'.$tokenList["token"],
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
                if(is_array($json)&&key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn['code'] = 200;
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:"";
                }
            }
        }else{
            $rtn['outData'] = $tokenList["message"];
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }

        $sqlData=array(
            "status_type"=>$rtn['code']==200?"C":"E",
            "data_content"=>json_encode($data),
            "out_content"=>$rtn['outData'],
            "message"=>$rtn['message'],
            "lcu"=>Yii::app()->user->id,
            "lcd"=>$sendDate,
        );
        $suffix = Yii::app()->params['envSuffix'];
        Yii::app()->db->createCommand()->update("operation{$suffix}.opr_api_curl",$sqlData,"id=".$row["id"]);
        return $rtn;
    }

    public static function getData($data,$url) {
        $root = Yii::app()->params['JDCurlRootURL'];
        $endUrl = $root.$url;
        $rtn = array('message'=>'', 'code'=>400,'outData'=>array());//成功时code=200；
        $tokenModel = new JDToken();
        $tokenList = $tokenModel->getToken();
        if($tokenList["status"]===true){
            $data_string = json_encode($data);
            //echo "请求内容:<br/>{$data_string}<br/><br/>";

            $ch = curl_init($endUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'accessToken:'.$tokenList["token"],
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $out = curl_exec($ch);
            //echo "返回内容:<br/>{$out}<br/><br/>";
            if ($out===false) {
                $rtn['message'] = curl_error($ch);
            } else {
                $json = json_decode($out, true);
                if(is_array($json)&&key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn['code'] = 200;
                    $rtn['outData'] = $json["data"]["rows"];
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:"";
                }
            }
        }else{
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }

        return $rtn;
    }

    public static function getDataToLocal($data,$url) {
        $root = Yii::app()->params['uCurlIP'];
        $endUrl = $root.$url;
        $rtn = array('message'=>'', 'code'=>400,'outData'=>array());//成功时code=200；
        $tokenModel = new JDToken();
        $tokenList = $tokenModel->getToken();
        if($tokenList["status"]===true){
            $data_string = json_encode($data);

            $ch = curl_init($endUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json',
                'Content-Length:'.strlen($data_string),
                'accessToken:'.$tokenList["token"],
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $out = curl_exec($ch);
            if ($out===false) {
                $rtn['message'] = curl_error($ch);
            } else {
                $json = json_decode($out, true);
                if(is_array($json)&&key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn['code'] = 200;
                    $rtn['outData'] = $json["data"]["rows"];
                }else{
                    $rtn['message'] = isset($json["message"])?$json["message"]:"";
                }
            }
        }else{
            $rtn["message"] = "token获取失败:".$tokenList["message"];//token获取失败
        }

        return $rtn;
    }
}
