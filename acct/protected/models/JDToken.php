<?php
//金蝶的token相关
class JDToken{

    protected $client_id="lbsapp";//第三方应用系统编码，即appId
    protected $client_secret="FriendSWSLBSAPP5264@kingdee//";//第三方应用AccessToken认证密钥，即appSecret
    protected $accountId="1955176570721156096";//数据中心id
    protected $username="LBSAPI";//第三方应用代理用户的用户名1
    //protected $nonce;//随机数
    //protected $timestamp;//时间戳，当前时间前后5分钟
    //protected $language="zh_CN";//语言字串： zh_CN，zh_TW、en_US等。 默认系统默认语言，查询接口会返回对应的多语言文本字段

    protected $tokenOut;

    public function getToken(){
        $suffix = Yii::app()->params['envSuffix'];
        $date = new DateTime();
        $nowDate = $date->format("Y-m-d H:i:s");
        $row = Yii::app()->db->createCommand()->select("id,access_token,end_date")->from("operation{$suffix}.opr_token")
            ->where("token_type='JD'")->queryRow();
        if(!$row){
            Yii::app()->db->createCommand()->insert("operation{$suffix}.opr_token",array(
                "access_token"=>"aaa",
                "end_date"=>"2020-06-12 14:24:07",
            ));
            $row=array("id"=>Yii::app()->db->getLastInsertID(),"access_token"=>"aaa","end_date"=>"2020-06-12 14:24:07");
        }
        $rtn = array('token'=>'','status'=>true,'message'=>'');
        if($row["end_date"]>=$nowDate){
            $rtn['token'] = $row["access_token"];
        }else{
            $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
            $tokenList = $this->getTokenForSend();
            if($tokenList["status"]===true){
                $tokenList["expires_in"]=30*60*1000;//30分钟有效期
                $date->modify(sprintf("+%d seconds", $tokenList["expires_in"] / 1000));
                Yii::app()->db->createCommand()->update("operation{$suffix}.opr_token",array(
                    "access_token"=>$tokenList["token"],
                    "expires_in"=>$tokenList["expires_in"],
                    "start_date"=>$nowDate,
                    "token_json"=>$this->tokenOut,
                    "luu"=>$uid,
                    "end_date"=>$date->format("Y-m-d H:i:s"),
                ),"id=".$row["id"]);
                $rtn['token'] = $tokenList["token"];
                //记录所有时间段生成的token
                Yii::app()->db->createCommand()->insert("operation{$suffix}.opr_token_history",array(
                    "access_token"=>$tokenList["token"],
                    "token_type"=>"BS",
                    "token_json"=>$this->tokenOut,
                    "lcu"=>$uid,
                    "lcd"=>$nowDate,
                    "lud"=>$date->format("Y-m-d H:i:s"),
                ));
            }else{
                $rtn['message'] = $tokenList["message"];
                $rtn['status'] = false;
            }
        }
        return $rtn;
    }


    protected function getTokenForSend() {
        $rtn = array('token'=>'', 'status'=>false,'message'=>'404');
        $root = Yii::app()->params['JDCurlRootURL'];
        $url = $root."/kapi/oauth2/getToken";

        $uid = Yii::app()->user->id;

        $data=array(
            "client_id"=>$this->client_id,
            "client_secret"=>$this->client_secret,
            "username"=>$this->username,
            "accountId"=>$this->accountId,
            "nonce"=>date_format(date_create(),"YmdHis"),
            "timestamp"=>date_format(date_create(),"Y-m-d H:i:s"),
            "language"=>"zh_CN",
        );
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Content-Length:'.strlen($data_string),
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $out = curl_exec($ch);
        if ($out!==false) {
            $this->tokenOut = $out;
            $this->tokenOut = mb_strlen($out,'UTF-8')>2000?mb_substr($out,0,2000,'UTF-8'):$out;
            $rtn['message'] = strip_tags($out);
            $json = json_decode($out, true);
            if(is_array($json)){
                if(key_exists("errorCode",$json)&&$json["errorCode"]==0){
                    $rtn["status"] = true;
                    $rtn["token"] = $json["data"]["access_token"];
                    $rtn["expires_in"] = $json["data"]["expires_in"];
                    //access_token,expires_in
                }else{
                    $rtn["message"] = $json["message"];
                }
            }
        }
        return $rtn;
    }
}
