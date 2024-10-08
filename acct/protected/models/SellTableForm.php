<?php

class SellTableForm extends SellComputeForm{

    public $product_id;
    public $ject_remark;//拒绝备注
    public $examine;//状态
    public $examine_name;//状态
    private $examine_row=array();//
    public $supplement_money;//补充金额合计
    public $final_money;//最终金额合计
    public $detail = array(
        array('id'=>0,
            'date'=>'',//日期
            'hdr_id'=>0,
            'customer'=>'',//客戶名稱
            'type'=>'0',//類型
            'information'=>'',//情况说明
            'commission'=>'',//提成金额
            'examine'=>'',
            'uflag'=>'N',
        ),
    );


    private $new_calc;//新增提成比例
    private $point;//销售提成激励点
    private $service_reward;//创新业务提成点
    private $yearTurnOverMoney=0;//年營業額：新增金額+更改增加的金額

    private $pro_rate_list=array(
        "paper"=>0,//纸品系列及裝機
        "disinfectant"=>0,//消毒液及皂液
        "purification"=>0,//空气净化
        "chemical"=>0,//化学剂
        "aromatherapy"=>0,//香熏系列
        "pestcontrol"=>0,//虫控系列
        "other"=>0,//其他
    );//產品的提成點

    public $serviceList=array();//服務列表
    public $turnoverList=array(0=>"",1=>"年营业额");//營業額列表
    public $rateList=array(0=>"",1=>"提成点数");//提成点数列表
    public $rateMoneyList=array(0=>"",1=>"提成金额");//提成金额列表
    public $otherRateMoneyList=array(0=>"",1=>"跨区提成金额");//提成金额列表(跨區)

    public $sumRate=array(0=>"提成点数");//匯總提成比例
    public $sumRateMoney=array(0=>"金额");//匯總提成金額
    public $sumAllMoney=array(0=>0,1=>0,2=>0);//匯總總金額（只含0:增加的所有金額,1:減少的所有金額,2:ID服務的金額）

    public function attributeLabels()
    {
        return array(
            'examine'=>Yii::t('misc','Status'),
            'date'=>Yii::t('salestable','Date'),
            'customer'=>Yii::t('salestable','Customer'),
            'type'=>Yii::t('salestable','Type'),
            'information'=>Yii::t('salestable','Information'),
            'commission'=>Yii::t('salestable','Commission'),
            'ject_remark'=>Yii::t('salestable','Ject Remark'),
            'supplement_money'=>Yii::t('salestable','Total Supplementary Amount'),//补充金额合计
            'final_money'=>Yii::t('salestable','Total final amount'),//最终金额合计
        );
    }
    /**
     * Declares the validation rules.
     */
    public function rules(){
        return array(
            array('id,detail,ject_remark','safe'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID'),
        );
    }

    public function validateID($attribute, $params){
        $scenario = $this->getScenario();
        $this->setScenario("edit");//临时解决无法调用父类的retrieveData问题
        if($this->retrieveData($this->id)){
            switch ($scenario){
                case "save"://保存
                case "examine"://要求审核
                    $this->final_money = $this->sumAllMoney[0]+$this->sumAllMoney[1]+$this->sumAllMoney[2];
                    $this->supplement_money = 0;//額外補充金額
                    $arr=array();
                    $detailList = key_exists("detail",$_POST["SellTableForm"])?$_POST["SellTableForm"]["detail"]:array();
                    if(!empty($detailList)){
                        foreach ($detailList as $row){
                            $row["commission"]=is_numeric($row["commission"])?floatval($row["commission"]):0;
                            $row["commission"] = round($row["commission"],2);
                            if($row["uflag"]!="D"){
                                $this->supplement_money+=$row["commission"];
                            }
                            if(!empty($row["commission"])&&!empty($row["date"])){
                                $arr[]=$row;
                            }
                        }
                    }
                    $this->detail = $arr;
                    $this->final_money+=$this->supplement_money;
                    if(!$this->getReadonly()){
                        $this->addError($attribute, "权限异常或多次提交，请刷新重试");
                    }
                    break;
                case "audit"://审核通过
                case "ject"://拒绝
                    $this->ject_remark=$_POST["SellTableForm"]["ject_remark"];
                    if($this->getReadonly()&&!empty($this->examine_row)){
                        $this->addError($attribute, "权限异常或多次提交，请刷新重试");
                    }
                    break;
                case "break"://退回
                    if($this->examine!="A"||$this->getScenario()!="edit"){
                        $this->addError($attribute, "权限异常或多次提交，请刷新重试");
                    }
                    break;
            }
            $this->setScenario($scenario);
        }else{
            $this->addError($attribute, "服務不存在請刷新重試");
        }
    }

    public function retrieveData($index,$bool=true){
        if(parent::retrieveData($index,$bool)){
            //新增提成比例
            $this->new_calc=key_exists("new_calc",$this->dtl_list)?$this->dtl_list["new_calc"]:0;
            //销售提成激励点
            $this->point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
            //服务奖励点
            $this->service_reward=key_exists("service_reward",$this->dtl_list)?$this->dtl_list["service_reward"]:0;
            $new_money =key_exists('new_money',$this->dtl_list)?$this->dtl_list['new_money']:0;
            $edit_money =key_exists('edit_money',$this->dtl_list)?$this->dtl_list['edit_money']:0;
            $this->yearTurnOverMoney = $new_money+$edit_money;

            $this->setProRate();//計算產品的提成點數
            $this->setRateList();//設置提成点数列表
            $this->setTurnoverList();//初始化營業額列表
            $this->setRateMoneyList();//初始化提成金额列表
            $this->setOtherRateMoneyList();//初始化跨區提成金额列表
            $this->setServiceList();//設置所有的服務列表

            $this->setSumRate();//匯總提成比例
            $this->setSumRateMoney();//匯總提成金額
            $this->setSumAllMoney();//匯總總金額
            $row = Yii::app()->db->createCommand()
                ->select("a.*")
                ->from("acc_product a")
                ->where("a.service_hdr_id=:id",array(":id"=>$this->id))->queryRow();
            if($row){
                $this->examine = $row["examine"];
                $this->ject_remark = $row["ject_remark"];
                $this->examine_name = SellTableList::examine($row["examine"]);
                $this->examine_row = $row;
                $this->setSupplementMoney();//獲取補充金額
                $this->final_money+= $this->supplement_money;//最後的金額
            }else{
                $this->examine = "N";
                $this->examine_name = SellTableList::examine("N");
            }
            return true;
        }
        return false;
    }

    //獲取補充金額
    private function setSupplementMoney(){
        $this->supplement_money = 0;
        $rows = Yii::app()->db->createCommand()->select("*")->from("acc_salestable")
            ->where("hdr_id=:id",array(":id"=>$this->id))->queryAll();
        if($rows){
            $this->detail = array();
            foreach ($rows as $row){
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['hdr_id'] = $row['hdr_id'];
                $temp['customer'] = $row['customer'];
                $temp['type'] = $row['type'];
                $temp['information'] = $row['information'];
                $temp['date'] = General::toDate($row['date']);
                $temp['commission'] = floatval($row['commission']);
                $temp['uflag'] = 'N';
                $temp['examine']=$this->examine;
                $this->detail[] = $temp;
                $this->supplement_money += $temp['commission'];
            }
        }
    }

    //初始化提成金额列表
    private function setRateMoneyList(){
        $this->rateMoneyList=array(0=>"",1=>Yii::t("salestable","Commission amount"));
        $arr = array(2,3,4,6,7,8,9);//服務
        foreach ($arr as $item){
            $this->rateMoneyList[$item]=0;
        }
        for ($i=14;$i<=23;$i++){ //產品及服務的續約
            $this->rateMoneyList[$i]=0;
        }
        $arr = array(25,26,27);//ID服務
        foreach ($arr as $item){
            $this->rateMoneyList[$item]=0;
        }
    }

    //初始化跨區提成金额列表
    private function setOtherRateMoneyList(){
        $this->otherRateMoneyList=array(0=>"",1=>Yii::t("salestable","Other Commission amount"));
        $arr = array(2,3,4,7,8,9);//服務
        foreach ($arr as $item){
            $this->otherRateMoneyList[$item]=0;
        }
    }

    //匯總提成比例
    private function setSumRate(){
        $this->sumRate=array(0=>Yii::t("salestable","rate"));
        $arr = array(2,5,8,11);//服務總提成
        $rate = $this->new_calc+$this->point+$this->service_reward;
        $rate = ($rate*100)."%";
        foreach ($arr as $item){
            $this->sumRate[$item]=$rate;
        }
        $this->sumRate[12]=($this->pro_rate_list["paper"]*100)."%";//裝機提成
        $this->sumRate[13]=($this->pro_rate_list["paper"]*100)."%";//纸品提成
        $this->sumRate[14]=($this->pro_rate_list["chemical"]*100)."%";//化学剂（洗地易）提成
        $this->sumRate[16]=($this->pro_rate_list["other"]*100)."%";//其它提成
        $this->sumRate[17]="1%";//續約提成
    }

    //匯總提成金額
    private function setSumAllMoney(){
        $this->sumAllMoney=array(0=>0,1=>0,2=>0);
        $sumNum = array(2,5,8,11,12,13,14,16,17);
        foreach ($sumNum as $num){
            $this->sumAllMoney[0]+=$this->sumRateMoney[$num];
        }
        $sumNum = array(19,21,23);
        foreach ($sumNum as $num){
            $this->sumAllMoney[1]+=$this->sumRateMoney[$num];
        }
        $sumNum = array(25,27,28);
        foreach ($sumNum as $num){
            $this->sumAllMoney[2]+=$this->sumRateMoney[$num];
        }
        $this->final_money = $this->sumAllMoney[0]+$this->sumAllMoney[1]+$this->sumAllMoney[2];
    }

    //匯總提成金額
    private function setSumRateMoney(){
        $this->sumRateMoney=array(0=>Yii::t("salestable","amount"));
        $this->sumRateMoney[2]=$this->rateMoneyList[2];//IA
        $this->sumRateMoney[5]=$this->rateMoneyList[3];//IB
        $this->sumRateMoney[8]=$this->rateMoneyList[4];//IC
        $this->sumRateMoney[11]=0;//跨區總提成
        $sumNum = array(2,3,4);
        foreach ($sumNum as $num){
            $this->sumRateMoney[11]+=$this->otherRateMoneyList[$num];
        }
        $this->sumRateMoney[12]=$this->rateMoneyList[6];//裝機
        $this->sumRateMoney[13]=$this->rateMoneyList[14];//纸品
        $this->sumRateMoney[14]=$this->rateMoneyList[17];//化學劑
        $this->sumRateMoney[16]=0;//其他销售
        $sumNum = array(15,16,18,19,20);
        foreach ($sumNum as $num){
            $this->sumRateMoney[16]+=$this->rateMoneyList[$num];
        }
        $this->sumRateMoney[17]=0;//續約
        $sumNum = array(21,22,23);
        foreach ($sumNum as $num){
            $this->sumRateMoney[17]+=$this->rateMoneyList[$num];
        }
        $this->sumRateMoney[19]=$this->rateMoneyList[7]+$this->otherRateMoneyList[7];//IA
        $this->sumRateMoney[21]=$this->rateMoneyList[8]+$this->otherRateMoneyList[8];//IB
        $this->sumRateMoney[23]=$this->rateMoneyList[9]+$this->otherRateMoneyList[9];//IC
        $this->sumRateMoney[25]=$this->rateMoneyList[25];//新增（ID服務）
        $this->sumRateMoney[27]=$this->rateMoneyList[26];//更改（ID服務）
        $this->sumRateMoney[28]=$this->rateMoneyList[27];//續約（ID服務）
    }

    //初始化營業額列表
    private function setTurnoverList(){
        $this->turnoverList=array(0=>"",1=>Yii::t("salestable","Annual turnover"));
        $arr = array(2,3,4,6);//金額增加及裝機費
        foreach ($arr as $item){
            $this->turnoverList[$item]=0;
        }
        for ($i=14;$i<=23;$i++){ //產品及服務的續約
            $this->turnoverList[$i]=0;
        }
    }

    //設置提成点数列表
    private function setRateList(){
        //Yii::t("salestable","new customer for IA/IB/IC")
        $this->rateList=array(0=>"",1=>Yii::t("salestable","Percentage points"));
        $sum_rate = $this->point+$this->service_reward+$this->new_calc;//總提成金額
        for ($i=2;$i<=4;$i++){
            $this->rateList[$i]=($sum_rate*100)."%";
        }
        $this->rateList[6]=($this->pro_rate_list["paper"]*100)."%";//裝機提成點 = 紙品提成點
        $i=13;
        foreach ($this->pro_rate_list as $rate){ //顯示銷售的所有提成點
            $i++;
            $this->rateList[$i]=($rate*100)."%";
        }
        $this->rateList[21]="1%";//續約
        $this->rateList[22]="1%";//續約
        $this->rateList[23]="1%";//續約
    }

    //設置所有的服務列表
    private function setServiceList(){
        $this->serviceList=array();
        $suffix = Yii::app()->params['envSuffix'];
        $turnoverList=array(2,3,4,6,14,15,16,17,18,19,20,21,22,23);//營業額只統計的鍵位
        $rateMoneyList=array(2,3,4,6,7,8,9,14,15,16,17,18,19,20,21,22,23,25,26,27);//提成金额只統計的鍵位
        $otherRateMoneyList=array(2,3,4,7,8,9);//跨區提成金额只統計的鍵位
        //日報表的客戶服務放入服務列表內
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.description,IF(a.status='N',a.first_dt,a.status_dt) as service_date")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
            ->where("a.city='{$this->city}' and (
            (a.status='N' and a.first_dt between '{$this->startDate}' and '{$this->endDate}') or 
            (a.status!='N' and a.status_dt between '{$this->startDate}' and '{$this->endDate}')
            ) and 
            ((a.commission is not null and a.salesman_id={$this->employee_id}) or 
            (a.target!=1 and a.other_commission is not null and a.othersalesman_id={$this->employee_id}))
            ")
            ->order("service_date desc,id desc")->queryAll();
        if($rows){
            $this->serviceList[]=array('title'=>Yii::t("salestable","customer service"));
            foreach ($rows as $row){
                $row["royalty"]=is_numeric($row["royalty"])?floatval($row["royalty"]):0;
                if(empty($row["royalty"])&&$row["status"]=="C"){
                    continue;//續約提成點為零，不顯示在銷售提成表內
                }
                $row["amt_install"]=is_numeric($row["amt_install"])?floatval($row["amt_install"]):0;
                $row['b4_amt_paid'] = is_numeric($row['b4_amt_paid'])?floatval($row['b4_amt_paid']):0;
                $row["amt_paid"]=is_numeric($row["amt_paid"])?floatval($row["amt_paid"]):0;
                $row["ctrt_period"]=is_numeric($row["ctrt_period"])?floatval($row["ctrt_period"]):0;
                $row['amt_sum'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['b4_amt_sum'] = $row['b4_paid_type']=="M"?$row['b4_amt_paid']*$row['ctrt_period']:$row['b4_amt_paid'];
                $row['turnover']=$row['amt_sum'];
                $list=array();//初始化list
                $arr = $this->setServiceNumForStatus($row);
                $minKey = $this->getIAIBICForName($row["description"]);
                $maxKey = $row["maxKey"];
                $key = $maxKey+$minKey;
                $list["data"][0]=General::toDate($row["service_date"]);
                $list["data"][1]=$row["company_name"];
                $list["data"][$key]=$row["amt_paid"];//金額
                $list['startKey'] = $maxKey;
                if(key_exists("background",$row)){
                    $list["background"]=$row["background"];
                }
                if($row["salesman_id"]==$this->employee_id&&$row["othersalesman_id"]!=$this->employee_id){ //提成點
                    if(in_array($key,$turnoverList)){
                        $this->turnoverList[$key]+=$row['turnover'];
                    }
                    if(in_array($key,$rateMoneyList)){
                        $this->rateMoneyList[$key]+=$row["rateMoney"];
                    }
                }else{ //跨區提成點
                    if(in_array($key,$otherRateMoneyList)){
                        $this->otherRateMoneyList[$key]+=$row["rateMoney"];
                    }
                }
                foreach ($arr as $i=>$item){
                    $thisKey = $maxKey+$i;
                    $list["data"][$thisKey]=$item;
                    if($thisKey==6){ //裝機費
                        $this->turnoverList[$thisKey]+=$row["amt_install"];
                        $this->rateMoneyList[$thisKey]+=$row["rateInstall"];
                    }
                }
                $this->serviceList[]=$list;
            }
        }


        //將產品放入服務列表內
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.commission,a.money,a.qty,b.log_dt,f.sales_products,b.company_name")
            ->from("swoper$suffix.swo_logistic_dtl a")
            ->leftJoin("swoper$suffix.swo_logistic b","a.log_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_task f","a.task=f.id")
            ->where("a.commission=1 and b.log_dt between '{$this->startDate}' and '{$this->endDate}' and b.salesman='{$this->staff}' and b.city ='{$this->city}' and a.qty>0 and a.money>0")
            ->order("b.log_dt desc")->queryAll();
        if($rows){
            $this->serviceList[]=array('title'=>Yii::t("salestable","product"));
            foreach ($rows as $row){
                $list=array();//初始化list
                $row['qty'] = is_numeric($row['qty'])?floatval($row['qty']):0;
                $row['money'] = is_numeric($row['money'])?floatval($row['money']):0;
                $amt_sum = $row['qty']*$row['money'];
                $list["data"][0]=General::toDate($row["log_dt"]);//時間
                $list["data"][1]=$row["company_name"];//客戶名稱
                if(key_exists($row['sales_products'],$this->pro_rate_list)){
                    $rateMoney = $amt_sum*$this->pro_rate_list[$row['sales_products']];
                    $rateMoney = round($rateMoney,2);
                    $keyNum = $this->getProNumForKey($row['sales_products']);
                    $list["data"][$keyNum]=$amt_sum;
                    if(in_array($keyNum,$turnoverList)){
                        $this->turnoverList[$keyNum]+=$amt_sum;
                    }
                    if(in_array($keyNum,$rateMoneyList)){
                        $this->rateMoneyList[$keyNum]+=$rateMoney;
                    }
                }else{//該分類不存在，顯示此服務的產品類型(顯示在紙品單元格內)
                    $list["data"][14]=$row['sales_products'];
                    $list["background"]="red";
                }
                $this->serviceList[]=$list;
            }
        }

        //將ID服务放入服務列表內
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.status,f.code,f.name")
            ->from("swoper{$suffix}.swo_serviceid_info a")
            ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
            ->where("b.city='{$this->city}' and b.salesman_id={$this->employee_id} and a.commission = 1 and a.back_date between '$this->startDate' and '$this->endDate'")
            ->order("a.back_date desc,a.id desc")
            ->queryAll();
        if($rows) {
            $statusList = array("N"=>1,"A"=>2,"C"=>3);
            $startNum=24;
            $this->serviceList[] = array('title' => Yii::t("salestable","ID customer service"));
            foreach ($rows as $row) {
                $list=array();//初始化list
                $row['comm_money'] = is_numeric($row['comm_money'])?floatval($row['comm_money']):0;
                $row['rate_num'] = is_numeric($row['rate_num'])?floatval($row['rate_num']):0;
                $amt_sum = $row['comm_money']*$row['rate_num'];
                $amt_sum = round($amt_sum,2);
                $list["data"][0]=General::toDate($row["back_date"]);//時間
                $list["data"][1]=$row["code"].$row["name"];//客戶名稱
                if(key_exists($row["status"],$statusList)){
                    $num=$startNum+$statusList[$row["status"]];
                    $list["data"][$num]=$row['comm_money'];//实际计算金额
                    if(in_array($num,$rateMoneyList)){
                        $this->rateMoneyList[$num]+=$amt_sum;
                    }
                }else{
                    $list["data"][$startNum+1]="异常:{$row["status"]}";//状态异常
                }
                $list["data"][$startNum+4]=($row["rate_num"]*100)."%";//提成比例
                $this->serviceList[]=$list;
            }
        }
    }

    //設置所有的服務列表(填充服務詳情)
    private function setServiceNumForStatus(&$row){
        if($row["othersalesman_id"]==$this->employee_id) { //跨區
            $row["background"]="blue";//紅色標記跨區
            $row["royalty"]=$row["royaltys"];//轉換跨區的提成點數
            $row["commission"]=$row["other_commission"];//轉換跨區的提成點數
            $row["amt_install"]=0;//跨區業務不計算裝機費
        }elseif (!empty($row["othersalesman_id"])){ //被跨區
            $row["background"]="red";//藍色標記被跨區
        }
        $row["commission"]=is_numeric($row["commission"])?floatval($row["commission"]):0;
        $row["royalty"]=is_numeric($row["royalty"])?floatval($row["royalty"]):0;
        $row["amt_install"]=$row["commission"]<0?0:$row["amt_install"];//提成為負數，不計算裝機費
        $row["rateInstall"]=$row["amt_install"]*$this->pro_rate_list["paper"];
        $row["rateInstall"]=round($row["rateInstall"],2);
        $row["rateMoney"]=$row["commission"]>=0?$row["commission"]*$row["royalty"]:$row["commission"];
        $row["rateMoney"]=round($row["rateMoney"],2);
        if($row["paid_type"]!="M"){ //非月金額需要顯示金額類型
            $row["ctrt_period"]=$row["paid_type"]=="Y"?Yii::t("service","Yearly"):Yii::t("service","One time");
        }
        $list = array();
        switch ($row["status"]){
            case "T"://終止
                $list[4]=$row["ctrt_period"];//合同月份
                $list[5]=$row["all_number"];//服务总次数
                $list[6]=$row["surplus"];//剩余次数
                $list[7]=$row["royalty"];//提成點數
                $row["maxKey"] = 6;
                break;
            case "N"://新增
                $list[4]=$row["ctrt_period"];//合同月份
                if($row["amt_install"]>0){
                    $list[5]=$row["amt_install"];//装机费
                }
                $row["maxKey"] = 1;
                break;
            case "C"://續約
                $row["othersalesman_id"]="-1";//續約不需要考慮跨區
                $list[4]=$row["ctrt_period"];//合同月份
                if(empty($row["royalty"])){//當續約的提成點為零時，提示
                    $row["commission"]=0;
                    $row["background"]="yellow";//藍色標記被跨區
                }
                $row["maxKey"] = 20;
                break;
            case "A"://更改
                $row["amt_paid"] = $row["amt_paid"]-$row["b4_amt_paid"];
                if($row["commission"]<0){//更改減少
                    $row["amt_paid"]*=-1;//金額需要顯示正數
                    $row["maxKey"] = 6;
                    $list[4]=$row["ctrt_period"];//合同月份
                    $list[5]=$row["all_number"];//服务总次数
                    $list[6]=$row["surplus"];//剩余次数
                    $list[7]=$row["royalty"];//提成點數
                    if($row["amt_install"]>0){
                        $list[0]=$row["amt_install"];//装机费
                    }
                }else{
                    $row["maxKey"] = 1;
                    $row['turnover']-=$row['b4_amt_sum'];
                    $list[4]=$row["ctrt_period"];//合同月份
                    if($row["amt_install"]>0){
                        $list[5]=$row["amt_install"];//装机费
                    }
                }
                break;
            default:
                $row["maxKey"] = 55;//數據異常
        }
        return $list;
    }

    //服務歸類
    private function getIAIBICForName($name){
        //由於沒有設置區分IA、IB、IC，所以用名稱模糊查詢（不區分大小寫）
        if(stripos($name,'IA')!==false){
            $num=1;
        }elseif (stripos($name,'IB')!==false){
            $num=2;
        }else{
            $num=3;
        }
        return $num;
    }

    //產品歸類
    private function getProNumForKey($key){
        $num=13;
        $i=0;
        foreach ($this->pro_rate_list as $str=>$rate){
            $i++;
            if($key==$str){
                $num+=$i;
            }
        }
        return $num;
    }

    //計算產品的提成點數
    private function setProRate(){
        $money = $this->yearTurnOverMoney;
        foreach ($this->pro_rate_list as $key=>$value){
            $rate = SellComputeList::getProductRate($money,$this->startDate,$this->city,$key);
            $this->pro_rate_list[$key]=$rate+$this->point;
        }
    }

    //顯示提成表的表格內容
    public function sellTableHtml(){
        $html= '<table id="sellTable" class="table table-fixed table-condensed table-bordered table-hover">';
        $html.=$this->tableTopHtml();
        $html.=$this->tableBodyHtml();
        $html.=$this->tableFooterHtml();
        $html.="</table>";

        return $html;
    }

    //顯示提成表的表格內容（底部汇总）
    private function tableFooterHtml(){
        $html="<tfoot>";
        $html.="<tr>";
        $html.="<td colspan='2' style='padding: 20px 0px;'>".Yii::t("salestable","new customer for IA/IB/IC")."</td>";
        $html.="<td colspan='23'>{$this->yearTurnOverMoney}</td>";
        $html.="<td colspan='4'>&nbsp;</td>";
        $html.="</tr>";
        $html.=$this->footerHeadHtml();
        $html.=$this->footerRateAndMoney();
        $html.="<tr><td colspan='29' height='1px'></td></tr></tfoot>";
        return $html;
    }

    //列表匯總（提成比例及提成金額）
    private function footerRateAndMoney(){
        $html="";
        $trOne="";
        $trTwo="";
        $keyList = array(
            array('startNum'=>0,"length"=>2),
            array('startNum'=>2,"length"=>3),
            array('startNum'=>5,"length"=>3),
            array('startNum'=>8,"length"=>3),
            array('startNum'=>11,"length"=>1),
            array('startNum'=>12,"length"=>1),
            array('startNum'=>13,"length"=>1),
            array('startNum'=>14,"length"=>2),
            array('startNum'=>16,"length"=>1),
            array('startNum'=>17,"length"=>2),
            array('startNum'=>19,"length"=>2),
            array('startNum'=>21,"length"=>2),
            array('startNum'=>23,"length"=>2),
            array('startNum'=>25,"length"=>2),
            array('startNum'=>27,"length"=>1),
            array('startNum'=>28,"length"=>1),
        );
        foreach ($keyList as $item){
            $oneNum = key_exists($item["startNum"],$this->sumRate)?$this->sumRate[$item["startNum"]]:"/";
            $twoNum = key_exists($item["startNum"],$this->sumRateMoney)?$this->sumRateMoney[$item["startNum"]]:"0";
            $trOne.="<td colspan='{$item["length"]}'>".$oneNum."</td>";
            $trTwo.="<td colspan='{$item["length"]}'>".$twoNum."</td>";
        }
        $html.="<tr>{$trOne}</tr><tr style='background-color: #bedda7'>{$trTwo}</tr>";
        $html.="<tr style='background-color: #acc8cc'>";
        $html.="<td colspan='2'>".Yii::t("salestable","Aggregate amount")."</td>";
        $html.="<td colspan='17'>".$this->sumAllMoney[0]."</td>";
        $html.="<td colspan='6'>".$this->sumAllMoney[1]."</td>";
        $html.="<td colspan='4'>".$this->sumAllMoney[2]."</td>";
        $html.="</tr>";
        return $html;
    }
    //底部表格的頁頭
    private function footerHeadHtml(){
        $html="";
        $topList=array(
            array("name"=>"name","rowspan"=>2,"colspan"=>2),//名稱
            array("name"=>"Business commission new","exprCol"=>6,
                "colspan"=>array(
                    array("name"=>"IA(clean)","colspan"=>3),//IA（清洁）
                    array("name"=>"IB(bug)","colspan"=>3),//IB（灭虫）
                    array("name"=>"IC(Rent)","colspan"=>3),//IC（租机）
                )
            ),//本月新客户营业提成
            array("name"=>"Other Commission","rowspan"=>2),//跨区提成
            array("name"=>"install money","rowspan"=>2),//装机费
            array("name"=>"sales","exprCol"=>1,
                "colspan"=>array(
                    array("name"=>"Pa"),//纸品
                    array("name"=>"Chemical(Detergent)","colspan"=>2),//化学剂（洗地易）
                    array("name"=>"other sales"),//其他销售
                )
            ),//销售
            array("name"=>"Business commission renewed","exprCol"=>1,
                "colspan"=>array(
                    array("name"=>"contract","colspan"=>2),//续约
                )
            ),//本月续约营业提成
            array("name"=>"Stop/Update/termination customer","exprCol"=>3,
                "colspan"=>array(
                    array("name"=>"IA(clean)","colspan"=>2),//IA（清洁）
                    array("name"=>"IB(bug)","colspan"=>2),//IB（灭虫）
                    array("name"=>"IC(Rent)","colspan"=>2),//IC（租机）
                )
            ),//本月停单/更改减少/续约终止客户（包括跨区）
            array("name"=>"ID customer","exprCol"=>1,
                "colspan"=>array(
                    array("name"=>"new","colspan"=>2),//IA（清洁）
                    array("name"=>"update"),//IB（灭虫）
                    array("name"=>"contract"),//IC（租机）
                )
            ),//本月停单/更改减少/续约终止客户（包括跨区）
        );
        $trOne="";
        $trTwo="";
        foreach ($topList as $list){
            $clickName=Yii::t("salestable",$list["name"]);
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $trOne.="<td";
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("colspan",$list)){
                $colNum=is_array($colList)?count($colList):$colList;
                $colNum+=key_exists("exprCol",$list)?$list["exprCol"]:0;
                $trOne.=" colspan='{$colNum}'";
            }
            $trOne.=" >".$clickName."</td>";
            if(!empty($colList)&&is_array($colList)){
                foreach ($colList as $col){
                    if(key_exists("colspan",$col)){
                        $trTwo.="<td colspan='{$col['colspan']}'>".Yii::t("salestable",$col["name"])."</td>";
                    }else{
                        $trTwo.="<td>".Yii::t("salestable",$col["name"])."</td>";
                    }
                }
            }
        }
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr>";
        return $html;
    }

    //顯示提成表的表格內容（服務板塊）
    private function tableBodyHtml(){
        $html=$this->showServiceHtml();//顯示所有參與計算的服務
        $html.=$this->showTurnoverAndRateHtml();//營業額列表、提成点数列表、提成金额列表
        return $html;
    }

    //營業額列表（turnoverList）、提成点数列表（rateList）、提成金额列表（rateMoneyList）
    private function showTurnoverAndRateHtml(){
        $html="";
        $forList = array($this->turnoverList,$this->rateList,$this->rateMoneyList,$this->otherRateMoneyList);
        foreach ($forList as $row){
            $html.="<tr class='tr-end' style='background-color: #acc8cc'>";
            for ($i=0;$i<=28;$i++){
                $value = key_exists($i,$row)?$row[$i]:"/";
                $html.="<td>".$value."</td>";
            }
            $html.="</tr>";
        }
        return $html;
    }

    //將服務寫入表格
    private function showServiceHtml(){
        $html="";
        if(!empty($this->serviceList)){
            foreach ($this->serviceList as $row){
                $html.="<tr";
                if(key_exists("startKey",$row)){//本條服務在那條顯示
                    $html.=" startKey='{$row['startKey']}'";
                }
                if(key_exists("background",$row)){//背景顏色改成文字顏色
                    $html.=" style='color: {$row["background"]}'";
                }
                $html.=">";
                if(key_exists("data",$row)){
                    $data = $row["data"];
                    for ($i=0;$i<=28;$i++){
                        $tdValue = key_exists($i,$data)?$data[$i]:"&nbsp;";
                        $html.="<td>{$tdValue}</td>";
                    }
                }else{
                    $title = key_exists("title",$row)?$row["title"]:"&nbsp;";
                    $html.="<td colspan='29' class='click-tr' style='text-align: left'><b>{$title}</b>&nbsp;&nbsp;<i class='fa'></i></td>";
                }
                $html.="<tr>";
            }
        }
        return $html;
    }

    //顯示提成表的表格內容（表頭）
    private function tableTopHtml(){
        $topList=array(
            array("name"=>"Date","rowspan"=>2),//日期
            array("name"=>"Customer","rowspan"=>2),//客户
            array("name"=>"New customers this month","background"=>"rgb(247,253,157)","startKey"=>"1",
                "colspan"=>array(
                    array("name"=>"IA money"),//IA月费
                    array("name"=>"IB money"),//IB月费
                    array("name"=>"IC money"),//IC月费
                    array("name"=>"contract month"),//合同月数
                    array("name"=>"install money"),//装机费
                )
            ),//本月新客户
            array("name"=>"Stop/Update/termination customer","background"=>"rgb(252,213,180)","startKey"=>"6",
                "colspan"=>array(
                    array("name"=>"IA money"),//IA月费
                    array("name"=>"IB money"),//IB月费
                    array("name"=>"IC money"),//IC月费
                    array("name"=>"contract month"),//合同月数
                    array("name"=>"service sum"),//服务总次数
                    array("name"=>"residue num"),//剩余次数
                    array("name"=>"rate for add"),//新增时提成比例
                )
            ),//本月停单/更改减少/续约终止客户（包括跨区）
            array("name"=>"sales","background"=>"rgb(242,220,219)",
                "colspan"=>array(
                    array("name"=>"Paper"),//纸品系列
                    array("name"=>"Disinfectant"),//消毒液及皂液
                    array("name"=>"Purification"),//空气净化
                    array("name"=>"Chemical"),//化学剂
                    array("name"=>"Aromatherapy"),//香薰系列
                    array("name"=>"Pest control"),//虫控系列
                    array("name"=>"Other"),//其他
                )
            ),//销售
            array("name"=>"Customer renewed this month","background"=>"rgb(218,213,243)","startKey"=>"20",
                "colspan"=>array(
                    array("name"=>"IA money"),//IA月费
                    array("name"=>"IB money"),//IB月费
                    array("name"=>"IC money"),//IC月费
                    array("name"=>"contract month"),//合同月数
                )
            ),//本月续约客户
            array("name"=>"ID customer","background"=>"rgb(235,241,222)",
                "colspan"=>array(
                    array("name"=>"new"),//新增
                    array("name"=>"update"),//更改
                    array("name"=>"contract"),//续约
                    array("name"=>"rate"),//提成比例
                )
            ),//ID客户
        );
        $trOne="";
        $trTwo="";
        $html="<thead>";
        foreach ($topList as $list){
            $clickName=Yii::t("salestable",$list["name"]);
            $colList=key_exists("colspan",$list)?$list['colspan']:array();
            $trOne.="<th";
            if(key_exists("rowspan",$list)){
                $trOne.=" rowspan='{$list["rowspan"]}'";
            }
            if(key_exists("colspan",$list)){
                $colNum=count($colList);
                $trOne.=" colspan='{$colNum}' class='click-th'";
            }
            if(key_exists("background",$list)){
                $trOne.=" style='background:{$list["background"]}'";
            }
            if(key_exists("startKey",$list)){
                $trOne.=" data-key='{$list['startKey']}'";
            }
            $trOne.=" >".$clickName."</th>";
            if(!empty($colList)){
                foreach ($colList as $col){
                    $trTwo.="<th>".Yii::t("salestable",$col["name"])."</th>";
                }
            }
        }
        $html.=$this->tableHeaderWidth();//設置表格的單元格寬度
        $html.="<tr>{$trOne}</tr><tr>{$trTwo}</tr>";
        $html.="</thead>";
        return $html;
    }

    //設置表格的單元格寬度
    private function tableHeaderWidth(){
        $html="<tr>";
        for($i=0;$i<=28;$i++){
            if(in_array($i,array(1))){
                $width=100;
            }elseif (in_array($i,array(0,11))){
                $width=85;
            }elseif (in_array($i,array(5,10,12,14,16,18,19,24,28))){
                $width=80;
            }else{
                $width=70;
            }
            $html.="<th class='header-width' data-width='{$width}' width='{$width}px'>{$i}</th>";
        }
        return $html."</tr>";
    }

    //判斷輸入框是否允許修改
    public function getReadonly(){
        if(in_array($this->examine,array("N","S"))&&$this->getScenario()=="edit"){ //未審核或者拒絕時可以修改
            return true;
        }else{
            return false;
        }
    }

    public function saveData(){
        switch ($this->getScenario()){
            case "save"://保存
                if(empty($this->examine_row)){
                    Yii::app()->db->createCommand()->insert("acc_product",array(
                        "service_hdr_id"=>$this->id,
                        "amt_install_royalty"=>$this->pro_rate_list["paper"],
                        "final_money"=>$this->final_money,
                        "examine"=>"N",
                        "city"=>$this->city,
                    ));
                }else{
                    Yii::app()->db->createCommand()->update("acc_product",array(
                        "service_hdr_id"=>$this->id,
                        "amt_install_royalty"=>$this->pro_rate_list["paper"],
                        "final_money"=>$this->final_money,
                        "examine"=>"N",
                    ),"id=:id",array(":id"=>$this->examine_row["id"]));
                }
                break;
            case "examine"://要求审核
                if(empty($this->examine_row)){
                    Yii::app()->db->createCommand()->insert("acc_product",array(
                        "service_hdr_id"=>$this->id,
                        "amt_install_royalty"=>$this->pro_rate_list["paper"],
                        "final_money"=>$this->final_money,
                        "examine"=>"Y",
                        "city"=>$this->city,
                    ));
                }else{
                    Yii::app()->db->createCommand()->update("acc_product",array(
                        "service_hdr_id"=>$this->id,
                        "amt_install_royalty"=>$this->pro_rate_list["paper"],
                        "final_money"=>$this->final_money,
                        "examine"=>"Y",
                    ),"id=:id",array(":id"=>$this->examine_row["id"]));
                }
                break;
            case "audit"://审核通过
                Yii::app()->db->createCommand()->update("acc_product",array(
                    "examine"=>"A",
                ),"id=:id",array(":id"=>$this->examine_row["id"]));
                break;
            case "ject"://拒绝
                Yii::app()->db->createCommand()->update("acc_product",array(
                    "examine"=>"S",
                    "ject_remark"=>$this->ject_remark,
                ),"id=:id",array(":id"=>$this->examine_row["id"]));
                break;
            case "break"://退回
                Yii::app()->db->createCommand()->update("acc_product",array(
                    "examine"=>"N",
                ),"id=:id",array(":id"=>$this->examine_row["id"]));
                break;
        }

        $this->saveInfoDetail();
        $this->saveSupplementMoney();
    }

    private function saveSupplementMoney(){
        Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
            "supplement_money"=>$this->supplement_money
        ),"hdr_id=:hdr_id",array(":hdr_id"=>$this->id));
    }

    private function saveInfoDetail(){
        if(!empty($this->detail)&&in_array($this->getScenario(),array("save","examine"))){
            foreach ($this->detail as $row){
                switch ($row["uflag"]){
                    case "D"://删除
                        Yii::app()->db->createCommand()->delete("acc_salestable",
                            "id=:id",array(":id"=>$row["id"]));
                        break;
                    default:
                        if(!empty($row["id"])){ //修改
                            Yii::app()->db->createCommand()->update("acc_salestable",array(
                                "customer"=>$row["customer"],
                                "type"=>$row["type"],
                                "information"=>$row["information"],
                                "date"=>$row["date"],
                                "commission"=>$row["commission"],
                                "luu"=>Yii::app()->user->id,
                            ),"id=:id and hdr_id=:hdr_id",array(":id"=>$row["id"],":hdr_id"=>$this->id));
                        }else{ //增加
                            Yii::app()->db->createCommand()->insert("acc_salestable",array(
                                "hdr_id"=>$this->id,
                                "customer"=>$row["customer"],
                                "type"=>$row["type"],
                                "information"=>$row["information"],
                                "date"=>$row["date"],
                                "commission"=>$row["commission"],
                                "lcu"=>Yii::app()->user->id,
                            ));
                        }
                }
            }
        }
    }

    protected function getPHPExcelForModel($model){
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/sellTable.xlsx';
        $objPHPExcel = $objReader->load($path);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');//合并单元
        $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');//合并单元
        $objPHPExcel->getActiveSheet()->mergeCells('A4:C4');//合并单元
        $objPHPExcel->getActiveSheet()->mergeCells('A5:C5');//合并单元
        $objPHPExcel->getActiveSheet()->setCellValue('A2', $model->year.'年'.$model->month.'月'.$model->employee_name.'销售提成报表') ;
        $objPHPExcel->getActiveSheet()->setCellValue('A3', "新增提成比例：".($model->new_calc*100)."%") ;
        $objPHPExcel->getActiveSheet()->setCellValue('A4', "销售提成激励点：".($model->point*100)."%") ;
        $objPHPExcel->getActiveSheet()->setCellValue('A5', "创新业务提成点：".($model->service_reward*100)."%") ;
        $rowKey=8;
        $objActSheet=$objPHPExcel->setActiveSheetIndex(0);
        $objWorksheet = $objActSheet;
        //填充服務內容
        foreach ($model->serviceList as $service) {
            $rowKey++;
            $objWorksheet->insertNewRowBefore($rowKey, 1);
            //設置默認文本顏色
            $objPHPExcel->getActiveSheet()->getStyle("A{$rowKey}:AC{$rowKey}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
            if(key_exists("data",$service)){ //含有服務數據
                $data = $service["data"];
                for ($stringNum=0;$stringNum<=28;$stringNum++){
                    $string = PHPExcel_Cell::stringFromColumnIndex($stringNum);
                    $tdValue = key_exists($stringNum,$data)?$data[$stringNum]:"";
                    $objActSheet->setCellValue($string.$rowKey, $tdValue) ;
                }
                $model->setExcelTextColor($objPHPExcel,$service,$rowKey);//設置文字顏色
            }else{//文本說明
                $title = key_exists("title",$service)?$service["title"]:"&nbsp;";
                $objPHPExcel->getActiveSheet()->mergeCells("A{$rowKey}:AD{$rowKey}");//合并单元
                $objPHPExcel->getActiveSheet()->getstyle("A{$rowKey}")->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objActSheet->setCellValue("A{$rowKey}", $title) ;
            }
        }
        //服務匯總
        $forList = array($model->turnoverList,$model->rateList,$model->rateMoneyList,$model->otherRateMoneyList);
        foreach ($forList as $row){
            $rowKey++;
            //設置默認文本顏色
            $objPHPExcel->getActiveSheet()->getStyle("A{$rowKey}:AC{$rowKey}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
            for ($stringNum=0;$stringNum<=28;$stringNum++){
                $string = PHPExcel_Cell::stringFromColumnIndex($stringNum);
                $tdValue = key_exists($stringNum,$row)?$row[$stringNum]:"/";
                $objActSheet->setCellValue($string.$rowKey, $tdValue) ;
            }
        }
        $rowKey+=8;
        $objActSheet->setCellValue("C".$rowKey, $model->supplement_money) ;//補充金額合計
        //补充说明
        $exprTypeList=array(
            "ia"=>"IA",
            "ib"=>"IB",
            "ic"=>"IC",
            "other"=>"其它",
        );
        $rowKey+=7;
        if(!empty($model->detail)){
            foreach ($model->detail as $exprRow){
                $exprRow["type"] = key_exists($exprRow["type"],$exprTypeList)?$exprTypeList[$exprRow["type"]]:$exprRow["type"];
                $rowKey++;
                $objWorksheet->insertNewRowBefore($rowKey, 1);
                $objPHPExcel->getActiveSheet()->mergeCells("D$rowKey:U$rowKey");//合并单元
                $objPHPExcel->getActiveSheet()->mergeCells("V$rowKey:Y$rowKey");//合并单元
                $objActSheet->setCellValue("A".$rowKey, $exprRow["date"]) ;
                $objActSheet->setCellValue("B".$rowKey, $exprRow["customer"]) ;
                $objActSheet->setCellValue("C".$rowKey, $exprRow["type"]) ;
                $objActSheet->setCellValue("D".$rowKey, $exprRow["information"]) ;
                $objActSheet->setCellValue("V".$rowKey, $exprRow["commission"]) ;
            }
        }
        return $objPHPExcel;
    }

    //下载excel
    public function downExcelAll($idList){
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        if(!empty($idList)){
            $newExcel = new PHPExcel();
            $newExcel->removeSheetByIndex(0);
            $i=0;
            foreach ($idList as $id){
                $this->retrieveData($id);
                $objPHPExcel = $this->getPHPExcelForModel($this);
                $sheet = $objPHPExcel->getSheet(0);
                $sheet->setTitle($this->employee_name);
                $newExcel->addExternalSheet($sheet);
                $i++;
            }
            //輸出excel
            $objWriter = PHPExcel_IOFactory::createWriter($newExcel, 'Excel2007');
            ob_start();
            $objWriter->save('php://output');
            $output = ob_get_clean();
            spl_autoload_register(array('YiiBase','autoload'));
            $str="销售提成表-All";
            $filename= iconv('utf-8','gbk//ignore',$str);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
            header("Content-Transfer-Encoding:binary");
            echo $output;
        }
    }

    //下载excel
    public function downExcel(){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

        $objPHPExcel = $this->getPHPExcelForModel($this);

        //輸出excel
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $str="销售提成表-".$this->employee_name;
        $filename= iconv('utf-8','gbk//ignore',$str);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    private function setExcelTextColor($objPHPExcel,$service,$rowKey){
        if(key_exists("background",$service)){//背景顏色改成文字顏色
            switch ($service["background"]){
                case "red":
                    $objPHPExcel->getActiveSheet()->getStyle("A{$rowKey}:AC{$rowKey}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                    break;
                case "yellow":
                    $objPHPExcel->getActiveSheet()->getStyle("A{$rowKey}:AC{$rowKey}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
                    break;
                case "blue":
                    $objPHPExcel->getActiveSheet()->getStyle("A{$rowKey}:AC{$rowKey}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
                    break;
            }
        }
    }
}