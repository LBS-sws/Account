<?php

class SellComputeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $office_name;
	public $group_type;
	public $performance;
	public $all_amount;
	public $final_money;//最终金额
	public $staff;//查詢用的格式：員工名稱 (員工編號)
    public $year;
    public $month;
    public $startDate="2022-05-01";
    public $endDate="2022-05-31";

    public $span_id;//跨区业绩目标id
    public $span_rate=0;//跨区提成比例
    public $span_other_rate=0;//被跨区的提成比例(判断是否满足要求后的提成)
    public $bonus_other_rate=0;//被跨区的提成比例(奖金库专用)
    public $span_list=array();//跨区业绩目标数组
    public $city;
    public $city_name;
    public $lcu;

    public $showNull=true;
    public $updateBool=true;
    public $dtl_list = array();//首頁的所有信息

    public $viewType="";

    private $textSum=0;//文本显示专用（总数）
    private $textNum=0;//文本显示专用（已计算）

    private $old_calc=0;//兼容旧版的提成点（旧版未保存）

    public $onlySql="";//只允許查看自己的提成
    //new,edit,end,performance,performanceedit,performanceend,renewal,renewalend,product,
    public static $viewList = array(
        'view'=>array('key'=>'view','name'=>'ALL'),
        'new'=>array('key'=>'new','name'=>'New'),
        'edit'=>array('key'=>'edit','name'=>'Edit'),
        'end'=>array('key'=>'end','name'=>'END'),
        'recovery'=>array('key'=>'recovery','name'=>'Recovery'),
        'performance'=>array('key'=>'performance','name'=>'Performance'),
        'performanceedit'=>array('key'=>'performanceedit','name'=>'PerformanceEdit'),
        'performanceend'=>array('key'=>'performanceend','name'=>'PerformanceEnd'),
        'perRecovery'=>array('key'=>'perRecovery','name'=>'PerRecovery'),
        'renewal'=>array('key'=>'renewal','name'=>'Renewal'),
        'renewalend'=>array('key'=>'renewalend','name'=>'RenewalEnd'),
        'product'=>array('key'=>'product','name'=>'Prodcct'),
    );
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels(){
		return array(
            'employee_name'=>Yii::t('commission','employee_name'),
            'group_type'=>Yii::t('commission','group_type'),
            'office_name'=>Yii::t('commission','office_name'),
            'year'=>Yii::t('commission','saleyear'),
            'city_name'=>Yii::t('commission','city'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id','safe'),
			array('employee_id,id','required'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
		);
	}

	public function retrieveData($index,$bool=true){
	    if(!key_exists($this->getScenario(),self::$viewList)){
	        return false;
        }
        if($bool){
            $cityList = Yii::app()->user->city_allow();
            $sqlEpr = " and a.city in ({$cityList})";
        }else{
            $sqlEpr = "";
        }
        $localOffice = Yii::t("commission","local office");
		$suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.code,b.name,b.group_type,b.id as employee_id,if(b.office_id=0,'{$localOffice}',f.name) as office_name")
            ->from("acc_service_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.code=a.employee_code")
            ->leftJoin("hr{$suffix}.hr_office f","f.id=b.office_id")
            ->where("a.id=:id {$sqlEpr} {$this->onlySql}",array(":id"=>$index))->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->employee_code = $row['code'];
			$this->employee_name = $row['name'];
			$this->office_name = $row['office_name'];
			$this->staff = "{$row['name']} ({$row['code']})";
			$this->year = $row['year_no'];
			$this->month = $row['month_no'];
			//跨区提成是否计算
			$this->performance = empty($row['performance'])?Yii::t("misc","No"):Yii::t("misc","Yes");
			//0:无 1:商业组 2:餐饮组
			$this->group_type = $row['group_type'];

            $this->lcu = $row['lcu'];
			$this->city = $row['city'];
			$this->city_name = General::getCityName($row['city']);
            $this->setUpdateBool();
            if($bool){
                $this->updateBool=false;
            }
			$this->computeDtlList();
			$this->setSpan();
            $this->showNull = ($row['lcd']==$row['lud']&&empty($this->dtl_list["new_calc"]));
            return true;
		}else{
		    return false;
        }
	}

	private function setSpan(){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("*")->from("sales{$suffix}.sal_performance")
            ->where("city=:city and ((year={$this->year} and month<={$this->month}) or year<{$this->year})",array(":city"=>$this->city))
            ->order("year desc,month desc")->queryRow();
        if($row){
            $row['sums']=is_numeric($row['sums'])?floatval($row['sums']):0;
            $row['sum']=is_numeric($row['sum'])?floatval($row['sum']):0;
            $this->span_list=$row;
            $this->span_id = $row["id"];
            $new_money=key_exists("new_money",$this->dtl_list)?$this->dtl_list["new_money"]:0;
            $serviceNum = Yii::app()->db->createCommand()
                ->select("count(a.id)")
                ->from("swoper{$suffix}.swo_service a")
                ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
                ->where("a.status='N' and a.commission is not null and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
                ->queryScalar();
            //金额达标或者参加计算的单数达标(僅針對被跨區限制)
            $this->bonus_other_rate = 0;
            $bool = $new_money>=$row['sums']||$serviceNum>=$row['sum'];
            switch ($this->group_type){
                case 0://0:无
                    $this->span_rate = floatval($row["spanning"]);
                    $this->span_other_rate = floatval($row["otherspanning"]);
                    if($bool){
                        $this->bonus_other_rate = floatval($row["otherspanning"]);
                    }
                    break;
                case 1://1:商业组
                    $this->span_rate = floatval($row["business_spanning"]);
                    $this->span_other_rate = floatval($row["business_otherspanning"]);
                    if($bool){
                        $this->bonus_other_rate = floatval($row["business_otherspanning"]);
                    }
                    break;
                case 2://2:餐饮组
                    $this->span_rate = floatval($row["restaurant_spanning"]);
                    $this->span_other_rate = floatval($row["restaurant_otherspanning"]);
                    if($bool){
                        $this->bonus_other_rate = floatval($row["restaurant_otherspanning"]);
                    }
                    break;
            }
        }
    }

    public static function getGroupName($group_type){
	    $list = array(
            Yii::t("misc","none"),//无
            Yii::t("misc","group business"),//商业组
            Yii::t("misc","group repast")//餐饮组
        );
	    if(key_exists($group_type,$list)){
	        return $list[$group_type];
        }
        return $list[0];
    }

    public static function getPaidTypeName($paid_type){
	    $list = array(
            "M"=>Yii::t("service","Monthly"),
            "Y"=>Yii::t("service","Yearly"),
            "1"=>Yii::t("service","One time")
        );
	    if(key_exists($paid_type,$list)){
	        return $list[$paid_type];
        }
        return $paid_type;
    }

    public static function getTaskType($type){
	    $list = array(
            'wu'=>Yii::t('commission','None'),//无
            'paper'=>Yii::t('commission','Paper'),//纸
            'disinfectant'=>Yii::t('commission','Disinfectant'),//消毒液
            'purification'=>Yii::t('commission','Purification'),//空气净化
            'chemical'=>Yii::t('commission','Chemical'),//化学剂
            'aromatherapy'=>Yii::t('commission','Aromatherapy'),//香薰
            'pestcontrol'=>Yii::t('commission','Pest control'),//虫控
            'other'=>Yii::t('commission','Other'),//其他
        );
	    if(key_exists($type,$list)){
	        return $list[$type];
        }
        return $list['wu'];
    }

	private function computeDtlList(){
        $this->all_amount=0;
        $this->dtl_list=array("new_calc"=>0);
        $row = Yii::app()->db->createCommand()
            ->select("*")->from("acc_service_comm_dtl")
            ->where("hdr_id=:id",array(":id"=>$this->id))->queryRow();
        $row = $row?$row:array();
        foreach (SellComputeList::$sellComputeAttr as $item){
            if(key_exists($item["value"],$row)){
                if(key_exists("amount",$item)&&$item["amount"]){
                    $num = is_numeric($row[$item["value"]])?floatval($row[$item["value"]]):0;
                    $this->all_amount += $num;
                }
                $this->dtl_list[$item["value"]] = is_numeric($row[$item["value"]])?floatval($row[$item["value"]]):$row[$item["value"]];
            }else{
                $this->dtl_list[$item["value"]]="";
            }
        }
        if(empty($row)){
            Yii::app()->db->createCommand()->insert("acc_service_comm_dtl",array(
                "hdr_id"=>$this->id,
                "lcu"=>Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id
            ));
        }
        $this->final_money = $this->all_amount+$this->dtl_list["supplement_money"];
    }

    public function getMenuHtml($linkType=''){
        $linkType = $linkType=="Search"?"Search":"Compute";
        $type = $this->getScenario();
        $html = '<ul class="nav nav-tabs" role="menu">';
        foreach (self::$viewList as $row){
            if($row["key"]==$type){
                $html.='<li class="active">';
            }else{
                $html.="<li>";
            }
            $text = Yii::t('commission',$row["name"]);
            if($row['key']=='view'){
                $url = Yii::app()->createUrl("sell{$linkType}/view",array('index'=>$this->id));
            }else{
                $url = Yii::app()->createUrl("sell{$linkType}/list",array('index'=>$this->id,'type'=>$row['key']));
            }
            $html.=TbHtml::link($text,$url,array("tabindex"=>-1));
            $html.="</li>";
        }
        return $html.'</ul>';
    }

    public function getListHtml(){
        $tableHtml='<table class="table table-hover">';
        $tableHtml.=$this->tableHtml();
        $tableHtml.='</table>';
        return $tableHtml;
    }

    private function tableHtml(){
        $fun = $this->getScenario();
        $fun.="Table";
        $html=$this->$fun();
        return $html;
    }

    //新增
    public function newList($checkBool=false){
        $checkSql = $checkBool?"a.commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='N' and a.city='{$this->city}' and b.sales_rate=1 and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.external_source asc,a.cust_type asc,a.cust_type_name asc")->queryAll();
        return $rows?$rows:array();
    }

    //新增
    private function newTable(){
        $installRate = $this->getPaperRateAndPoint();//装机提成
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("app","first_dt")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='110px'>"."外部数据来源"."</th>";
        $html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","amt_sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","royalty")."</th>";//commission
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="<th width='90px' style='border-left: 1px solid #f4f4f4'>".Yii::t("commission","install money")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","install amount")."</th>";
        $html.="</tr></thead>";
        $rows = $this->newList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $row['royalty'] = $row['external_source']==5?0.05:$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['first_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".self::getExternalSourceForKey($row['external_source'])."</td>";
                $html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$amt_sum."</td>";
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                //$row['amt_install'] = empty($row['amt_install'])?"":$row['amt_install'];
                $html.="<td style='border-left: 1px solid #f4f4f4'>".$row['amt_install']."</td>";
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $amt_install = $row['commission']==="未计算"?"未计算":$amt_install;
                $html.="<td>".$amt_install."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //新增
    private function newExcel($rows,$tempArr,$newExcel,&$detailRow,$installRate){
        if(!empty($rows)){
            $num = 3;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row["amt_money"] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];

                $row["old_royalty"]="";
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("新增生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["first_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["othersalesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($tempArr["new_calc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($tempArr["point"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($tempArr["service_reward"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["commission"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+13,$detailRow)->setValue($row["amt_install"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+14,$detailRow)->setValue($amt_install);
                //83
            }
        }
    }

    //跨区新增
    public function performanceList($checkBool=false){
        $checkSql = $checkBool?"a.other_commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='N' and a.city='{$this->city}' and b.sales_rate=1 and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        return $rows?$rows:array();
    }

    //跨区新增
    private function performanceTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("app","first_dt")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Salesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","amt_sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","royalty")."</th>";//commission
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->performanceList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['first_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['salesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$amt_sum."</td>";
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                //獎金庫擴充
                $row['commission'] = $row['target']==1?"奖金库":$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //跨区新增
    private function performanceExcel($rows,$tempArr,$newExcel,&$detailRow){
        if(!empty($rows)){
            $num = 47;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row["amt_money"] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                //獎金庫擴充
                $row['commission'] = $row['target']==1?"奖金库":$row['commission'];
                $row["old_royalty"]="";
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("跨区新增生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["first_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["salesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row["commission"]);
                //83
            }
        }
    }

    //恢复
    public function recoveryList($checkBool=false){
        $checkSql = $checkBool?"a.commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.description as type_desc,f.rpt_cat as nature_rpt,f.description as nature_name")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
            ->leftJoin("swoper{$suffix}.swo_nature f","a.nature_type=f.id")
            ->where("{$checkSql} a.status='R' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
            ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $arr = SellComputeList::getBeforeServiceListByStop($row,"salesman_id",$this->group_type);
                if(!empty($arr)){//变动金额
                    $row["history"]=$arr;
                    $list[]=$row;
                }
            }
        }
        return $list;
    }

    //恢复
    private function recoveryTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","recovery date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='70px'>".Yii::t("commission","nature_type")."</th>";
        //$html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->recoveryList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()));
                $html.="</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['nature_name']."</td>";
                //$html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $amt_sum = $row['history']['amt_money'];
                    $html.="<td>".$amt_sum."</td>";
                    $royalty=empty($row['history']['royalty'])?0:floatval($row['history']['royalty']);
                    if(!empty($royalty)){
                        $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    }else{
                        $html.="<td data-id='{$row['history']['id']}'>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //恢复
    private function recoveryExcel($rows,$tempArr,$newExcel,&$detailRow){
        if($rows){
            $num = 117;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row["amt_money"] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $row["old_royalty"]=floatval($row["history"]["royalty"]);
                if(empty($row["old_royalty"])){
                    $royalty=empty($row['royalty'])?0:$row['royalty'];
                    $row["old_royalty"] = $royalty;
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("恢复生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["nature_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row["commission"]);
                //93
            }
        }
    }

    //跨区恢复
    public function perRecoveryList($checkBool=false){
        $checkSql = $checkBool?"a.other_commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc,f.rpt_cat as nature_rpt,f.description as nature_name")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->leftJoin("swoper{$suffix}.swo_nature f","a.nature_type=f.id")
        ->where("{$checkSql} a.status='R' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id!={$this->employee_id} and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $arr = SellComputeList::getBeforeServiceListByStop($row,"othersalesman_id",$this->group_type);
                if(!empty($arr)){//变动金额
                    $row["history"]=$arr;
                    $list[]=$row;
                }
            }
        }
        return $list;
    }

    //跨区恢复
    private function perRecoveryTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","recovery date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='70px'>".Yii::t("commission","nature_type")."</th>";
        //$html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->perRecoveryList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['other_commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['other_commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['other_commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>";
                $html.=TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()));
                $html.="</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['nature_name']."</td>";
                //$html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $amt_sum = $row['history']['amt_money'];
                    $html.="<td>".$amt_sum."</td>";
                    $royalty=empty($row['history']['royalty'])?0:floatval($row['history']['royalty']);
                    if(!empty($royalty)){
                        $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    }else{
                        $html.="<td data-id='{$row['history']['id']}'>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['other_commission'] = is_numeric($row['other_commission'])&&$row['other_commission']>0?round($row['other_commission']*$row['royalty'],2):$row['other_commission'];
                $html.="<td>".$row['other_commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //跨区恢复
    private function perRecoveryExcel($rows,$tempArr,$newExcel,&$detailRow){
        if($rows){
            $num = 128;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row["amt_money"] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['other_commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['other_commission'] = is_numeric($row['other_commission'])&&$row['other_commission']>0?round($row['other_commission']*$row['royalty'],2):$row['other_commission'];
                $row["old_royalty"]=floatval($row["history"]["royalty"]);
                if(empty($row["old_royalty"])){
                    $royalty=empty($row['royalty'])?0:$row['royalty'];
                    $row["old_royalty"] = $royalty;
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("跨区恢复生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["nature_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row["other_commission"]);
                //93
            }
        }
    }

    //续约
    public function renewalList($checkBool=false){
        $checkSql = $checkBool?"a.commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc,f.rpt_cat as nature_rpt,f.description as nature_name")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->leftJoin("swoper{$suffix}.swo_nature f","a.nature_type=f.id")
        ->where("{$checkSql} a.status='C' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        return $rows?$rows:array();
    }

    //续约
    private function renewalTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","renewal date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='70px'>".Yii::t("commission","nature_type")."</th>";
        //$html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","renewal_sum")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->renewalList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>";
                if(in_array($row['nature_rpt'],array("A01","B01"))){ //只计算餐饮、非餐饮
                    $html.=TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()));
                }else{
                    $row['commission']="性质异常";
                }
                $html.="</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['nature_name']."</td>";
                //$html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$amt_sum."</td>";
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //续约
    private function renewalExcel($rows,$tempArr,$newExcel,&$detailRow){
        if($rows){
            $num = 84;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row["amt_money"] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $row["old_royalty"]="";
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("续约生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["nature_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row["commission"]);
                //93
            }
        }
    }

    //变更
    public function editList($checkBool=false){
        $checkSql = $checkBool?"a.commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='A' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows&&!$checkBool){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['b4_amt_paid'] = is_numeric($row['b4_amt_paid'])?floatval($row['b4_amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $before_sum = $row['b4_paid_type']=="M"?$row['b4_amt_paid']*$row['ctrt_period']:$row['b4_amt_paid'];
                $after_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['amt_money']=$after_sum-$before_sum;
                if($row['amt_money']<0){
                    $row['history']=SellComputeList::getBeforeServiceList($row,"N","salesman_id",$this->group_type);
                }
            }
        }
        return $rows?$rows:array();
    }

    //变更
    private function editTable(){
        $installRate = $this->getPaperRateAndPoint();//装机提成
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","edit date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='150px'>".Yii::t("commission","amt_paid").Yii::t("commission","(update before)")."</th>";
        $html.="<th width='150px'>".Yii::t("commission","amt_paid").Yii::t("commission","(update after)")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","service sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","residue num")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","commission_num")."</th>";
        $html.="<th width='90px' style='border-left: 1px solid #f4f4f4'>".Yii::t("commission","install money")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","install amount")."</th>";
        $html.="</tr></thead>";
        $rows = $this->editList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['b4_paid_type'])."：".$row['b4_amt_paid']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$row['all_number']."</td>";
                $html.="<td>".$row['surplus']."</td>";
                $html.="<td>".$row['amt_money']."</td>";
                if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                    if(!empty($row["history"])){//有历史提成
                        $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                        if(key_exists("oldSell",$row['history'])){//顯示舊數據信息
                            $html.="<td class='hide'>".implode(",",$row['history']["oldSell"])."</td>";
                        }
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }else{ //金额增加
                    $html.="<td>&nbsp;</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="<td style='border-left: 1px solid #f4f4f4'>".$row['amt_install']."</td>";
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $amt_install = $row['commission']==="未计算"?"未计算":$amt_install;
                $amt_install = is_numeric($row['commission'])&&$row['commission']<0?"不计算":$amt_install;
                $html.="<td>".$amt_install."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //变更
    private function editExcel($rows,$tempArr,$newExcel,&$detailRow,$installRate){
        if(!empty($rows)){
            $num = 18;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $b4_str = self::getPaidTypeName($row['b4_paid_type'])."：".$row['b4_amt_paid'];
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $row["old_royalty"]="";
                if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                    if(!empty($row["history"])){//有历史提成
                        $row["old_royalty"] = floatval($row["history"]["royalty"]);
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $row["old_royalty"] = $royalty;
                    }
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("更改生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["othersalesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($b4_str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row['all_number']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row['surplus']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+13,$detailRow)->setValue($row["commission"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+14,$detailRow)->setValue($row["amt_install"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+15,$detailRow)->setValue($amt_install);
                //83
            }
        }
    }

    //跨区变更
    public function performanceeditList($checkBool=false){
        $checkSql = $checkBool?"a.other_commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='A' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows&&!$checkBool){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['b4_amt_paid'] = is_numeric($row['b4_amt_paid'])?floatval($row['b4_amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $before_sum = $row['b4_paid_type']=="M"?$row['b4_amt_paid']*$row['ctrt_period']:$row['b4_amt_paid'];
                $after_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['amt_money']=$after_sum-$before_sum;
                if($row['amt_money']<0){
                    $row['history']=SellComputeList::getBeforeServiceList($row,"N","othersalesman_id",$this->group_type);
                }
            }
        }
        return $rows?$rows:array();
    }

    //跨区变更
    private function performanceeditTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","edit date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Salesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='150px'>".Yii::t("commission","amt_paid").Yii::t("commission","(update before)")."</th>";
        $html.="<th width='150px'>".Yii::t("commission","amt_paid").Yii::t("commission","(update after)")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","service sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","residue num")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->performanceeditList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['salesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['b4_paid_type'])."：".$row['b4_amt_paid']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$row['all_number']."</td>";
                $html.="<td>".$row['surplus']."</td>";
                $html.="<td>".$row['amt_money']."</td>";
                if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                    if(!empty($row["history"])){//有历史提成
                        if($row["history"]["target"]==1){
                            $html.="<td data-id='{$row['history']['id']}'>已放入奖金库</td>";
                        }else{
                            $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                        }
                        if(key_exists("oldSell",$row['history'])){//顯示舊數據信息
                            $html.="<td class='hide'>".implode(",",$row['history']["oldSell"])."</td>";
                        }
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }else{ //金额增加
                    $html.="<td>&nbsp;</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                //獎金庫擴充
                $row['commission'] = $row['target']==1?"奖金库":$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //跨区变更
    private function performanceeditExcel($rows,$tempArr,$newExcel,&$detailRow){
        if(!empty($rows)){
            $num = 57;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $b4_str = self::getPaidTypeName($row['b4_paid_type'])."：".$row['b4_amt_paid'];
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                //獎金庫擴充
                $row['commission'] = $row['target']==1?"奖金库":$row['commission'];
                $row["old_royalty"]="";
                if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                    if(!empty($row["history"])){//有历史提成
                        $row["old_royalty"] = floatval($row["history"]["royalty"]);
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $row["old_royalty"] = $royalty;
                    }
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("跨区更改生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["salesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($b4_str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row['all_number']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row['surplus']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+13,$detailRow)->setValue($row["commission"]);
                //83
            }
        }
    }

    //终止
    public function endList($checkBool=false){ //1：终止服务  2：续约终止
        $checkSql = $checkBool?"a.commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $list = array('stop'=>array(),'renewal'=>array());
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='T' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['all_number'] = is_numeric($row['all_number'])?floatval($row['all_number']):0;
                $row['surplus'] = is_numeric($row['surplus'])?floatval($row['surplus']):0;
                $row['amt_money'] = is_numeric($row['surplus_amt'])?floatval($row['surplus_amt']):0;//终止用剩余金额
                /*
                 * 2025年8月25日17:38:30终止增加了剩余金额，不需要手动计算
                $row['amt_money'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                //变动金额 = (总金额/服务总次数) * 剩余次数
                $row['amt_money']=empty($row['all_number'])?0:$row['amt_money']/$row['all_number']*$row['surplus'];
                $row['amt_money']=round($row['amt_money'],2)*-1;
                */
                $row['amt_money']=round($row['amt_money'],2)*-1;
                $arr = SellComputeList::getBeforeServiceList($row,'C',"salesman_id",$this->group_type);
                if(empty($arr)){
                    $arr = !$checkBool?SellComputeList::getBeforeServiceList($row,'N',"salesman_id",$this->group_type):array();
                    $row["history"]=$arr;
                    $list['stop'][]=$row;
                }else{//续约终止
                    $row["history"]=$arr;
                    $list['renewal'][]=$row;
                }
            }
        }
        return $list;
    }

    //终止
    private function endTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","edit date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","service sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","residue num")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->endList();
        if($rows["stop"]){
            foreach ($rows["stop"] as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$row['all_number']."</td>";
                $html.="<td>".$row['surplus']."</td>";
                $html.="<td>".$row['amt_money']."</td>";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    if(key_exists("oldSell",$row['history'])){//顯示舊數據信息
                        $html.="<td class='hide'>".implode(",",$row['history']["oldSell"])."</td>";
                    }
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //终止
    private function endExcel($rows,$tempArr,$newExcel,&$detailRow){
        if($rows["stop"]){
            $num = 34;
            foreach ($rows["stop"] as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $row["old_royalty"]="";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $row["old_royalty"] = $row["history"]["royalty"];
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $row["old_royalty"] = $royalty;
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("终止生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["othersalesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['all_number']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row['surplus']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row['amt_money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["commission"]);
                //83
            }
        }
    }

    //续约终止
    private function renewalendTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","edit date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Othersalesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","service sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","residue num")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->endList();
        if($rows["renewal"]){
            foreach ($rows["renewal"] as $row){
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$row['all_number']."</td>";
                $html.="<td>".$row['surplus']."</td>";
                $html.="<td>".$row['amt_money']."</td>";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    if(key_exists("oldSell",$row['history'])){//顯示舊數據信息
                        $html.="<td class='hide'>".implode(",",$row['history']["oldSell"])."</td>";
                    }
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //续约终止
    private function renewalendExcel($rows,$tempArr,$newExcel,&$detailRow){
        if($rows["renewal"]){
            $num = 94;
            foreach ($rows["renewal"] as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royalty'])?floatval($row['royalty']):0;
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $row["old_royalty"]="";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $row["old_royalty"] = $row["history"]["royalty"];
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $row["old_royalty"] = $royalty;
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("续约终止生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["othersalesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['all_number']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row['surplus']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row['amt_money']);
                //$newExcel->getSheet(1)->getCellByColumnAndRow(14,$detailRow)->setValue($tempArr["new_point_reward"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["commission"]);
                //106
            }
        }
    }

    //跨区终止
    public function performanceendList($checkBool=false){
        $checkSql = $checkBool?"a.other_commission is not null and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("{$checkSql} a.status='T' and a.city='{$this->city}' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows&&!$checkBool){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['all_number'] = is_numeric($row['all_number'])?floatval($row['all_number']):0;
                $row['surplus'] = is_numeric($row['surplus'])?floatval($row['surplus']):0;
                $row['amt_money'] = is_numeric($row['surplus_amt'])?floatval($row['surplus_amt']):0;
                /*
                 * 2025年8月25日17:38:30终止增加了剩余金额，不需要手动计算
                $row['amt_money'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                //变动金额 = (总金额/服务总次数) * 剩余次数
                $row['amt_money']=empty($row['all_number'])?0:$row['amt_money']/$row['all_number']*$row['surplus'];
                $row['amt_money']=round($row['amt_money'],2)*-1;
                */
                $row['amt_money']=round($row['amt_money'],2)*-1;
                $row['history']=SellComputeList::getBeforeServiceList($row,"N","othersalesman_id",$this->group_type);
            }
        }
        return $rows?$rows:array();
    }

    //跨区终止
    private function performanceendTable(){
        $type = $this->getScenario();
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("commission","edit date")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='110px'>".Yii::t("app","type_desc")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Salesman")."</th>";
        $html.="<th width='105px'>".Yii::t("commission","ctrt_period")."</th>";
        $html.="<th width='130px'>".Yii::t("commission","amt_paid")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","service sum")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","residue num")."</th>";
        $html.="<th width='90px'>".Yii::t("commission","update money")."</th>";
        $html.="<th width='195px'>".Yii::t("commission","history royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='80px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->performanceendList();
        if($rows){
            foreach ($rows as $row){
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['royalty'] = $row['commission']==="未计算"?"":$row['royalty'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['status_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['salesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$row['all_number']."</td>";
                $html.="<td>".$row['surplus']."</td>";
                $html.="<td>".$row['amt_money']."</td>";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    if($row["history"]["target"]==1){
                        $html.="<td data-id='{$row['history']['id']}'>已放入奖金库</td>";
                    }else{
                        $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    }
                    if(key_exists("oldSell",$row['history'])){//顯示舊數據信息
                        $html.="<td class='hide'>".implode(",",$row['history']["oldSell"])."</td>";
                    }
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //跨区终止
    private function performanceendExcel($rows,$tempArr,$newExcel,&$detailRow){
        if(!empty($rows)){
            $num = 71;
            foreach ($rows as $row){
                $detailRow++;
                $row["first_dt"] = General::toMyDate($row["first_dt"]);
                $row["status_dt"] = General::toMyDate($row["status_dt"]);
                $str = self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid'];
                $row['royalty'] = is_numeric($row['royaltys'])?floatval($row['royaltys']):0;
                $row['commission'] = is_numeric($row['other_commission'])?floatval($row['other_commission']):"未计算";
                $row['commission'] = is_numeric($row['commission'])&&$row['commission']>0?round($row['commission']*$row['royalty'],2):$row['commission'];
                $row["old_royalty"]="";
                if(key_exists("history",$row)&&!empty($row["history"])){ //有历史提成
                    $row["old_royalty"] = $row["history"]["royalty"];
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $row["old_royalty"] = $royalty;
                }
                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("跨区终止生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["status_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["type_desc"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($row["othersalesman"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row["ctrt_period"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($row['all_number']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row['surplus']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($row['amt_money']);
                //$newExcel->getSheet(1)->getCellByColumnAndRow(14,$detailRow)->setValue($tempArr["new_point_reward"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+10,$detailRow)->setValue($row["old_royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+11,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+12,$detailRow)->setValue($row["commission"]);
                //83
            }
        }
    }

    //产品生意额
    public function productList($checkBool=false){
        $checkSql = $checkBool?"a.commission=1 and ":"";//筛选已经选中的数据
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.id,a.commission,a.money,a.qty,f.description,f.sales_products,b.log_dt,b.company_name")
        ->from("swoper$suffix.swo_logistic_dtl a")
        ->leftJoin("swoper$suffix.swo_logistic b","a.log_id=b.id")
        ->leftJoin("swoper{$suffix}.swo_task f","a.task=f.id")
        ->where("{$checkSql} b.log_dt between '{$this->startDate}' and '{$this->endDate}' and b.salesman='{$this->staff}' and b.city ='{$this->city}' and a.qty>0 and a.money>0")
        ->order("f.sales_products asc,b.log_dt desc")->queryAll();
        return $rows?$rows:array();
    }

    //产品生意额
    private function productTable(){
        $type = $this->getScenario();
        //销售提成激励点
        $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
        //更改新增业绩
        $edit_money=key_exists("edit_money",$this->dtl_list)?$this->dtl_list["edit_money"]:0;
        //更改新增业绩+新增业绩
        $edit_money+=key_exists("new_money",$this->dtl_list)?$this->dtl_list["new_money"]:0;

        $computeRate=array();//保存已计算的产品提成比例
        $html="<thead><tr>";
        $html.="<th width='35px'>";
        if($this->updateBool){
            $html.=TbHtml::checkBox("all",false,array("id"=>"checkAll"));
        }
        $html.="</th>";
        $html.="<th width='105px'>".Yii::t("app","Log Dt")."</th>";
        $html.="<th width='160px'>".Yii::t("app","company_name")."</th>";
        $html.="<th width='120px'>".Yii::t("app","Description")."</th>";
        $html.="<th width='120px'>".Yii::t("commission","product type")."</th>";
        $html.="<th width='100px'>".Yii::t("app","Qty")."</th>";
        $html.="<th width='100px'>".Yii::t("app","Qty Money")."</th>";
        $html.="<th width='120px'>".Yii::t("commission","product_sum")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","royalty")."</th>";
        $html.="<th width='100px'>".Yii::t("commission","commission_num")."</th>";
        $html.="</tr></thead>";
        $rows = $this->productList();
        if($rows){
            foreach ($rows as $row){
                $proType = $row['sales_products'];
                $row['commission'] = is_numeric($row['commission'])?floatval($row['commission']):2;
                $row['qty'] = is_numeric($row['qty'])?floatval($row['qty']):0;
                $row['money'] = is_numeric($row['money'])?floatval($row['money']):0;
                $row['royalty'] = "";
                $amt_sum = $row['qty']*$row['money'];
                $commission = $row['commission']==2?"未计算":$amt_sum;
                $checkBool = $row['commission']==2?false:true;
                $html.="<tr>";
                $html.="<td>";
                if(!empty($proType)&&$proType!='wu'){ //没分类的产品不允许计算提成
                    $html.=TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()));
                }else{
                    $commission="未分类";
                }
                $html.="</td>";
                $html.="<td>".General::toDate($row['log_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['description']."</td>";
                $html.="<td>".self::getTaskType($proType)."</td>";
                $html.="<td>".$row['qty']."</td>";
                $html.="<td>".$row['money']."</td>";
                $html.="<td>".$amt_sum."</td>";
                if(is_numeric($commission)){
                    if(!key_exists($proType,$computeRate)){
                        $computeRate[$proType] = SellComputeList::getProductRate($edit_money,$this->startDate,$this->city,$proType);
                    }
                    $row['royalty'] = $computeRate[$proType]+$point;
                    $commission*=$row['royalty'];
                    $commission=round($commission,2);
                }
                $html.="<td>".$row['royalty']."</td>";
                $html.="<td>".$commission."</td>";
                $html.="</tr>";
                $this->textSum++;
                if($checkBool){
                    $this->textNum++;
                }
            }
        }
        return $html;
    }

    //跨区终止
    private function productExcel($rows,$tempArr,$newExcel,&$detailRow){
        if(!empty($rows)){
            //销售提成激励点
            $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
            //更改新增业绩
            $edit_money=key_exists("edit_money",$this->dtl_list)?$this->dtl_list["edit_money"]:0;
            //更改新增业绩+新增业绩
            $edit_money+=key_exists("new_money",$this->dtl_list)?$this->dtl_list["new_money"]:0;

            $computeRate=array();//保存已计算的产品提成比例
            $num = 107;
            foreach ($rows as $row){
                $detailRow++;
                $row["log_dt"] = General::toMyDate($row["log_dt"]);
                $proType = $row['sales_products'];
                $str = self::getTaskType($proType);
                $row['qty'] = is_numeric($row['qty'])?floatval($row['qty']):0;
                $row['money'] = is_numeric($row['money'])?floatval($row['money']):0;
                $row['royalty'] = "";
                $amt_sum = $row['qty']*$row['money'];
                if(!key_exists($proType,$computeRate)){
                    $computeRate[$proType] = SellComputeList::getProductRate($edit_money,$this->startDate,$this->city,$proType);
                }
                $row['royalty'] = $computeRate[$proType]+$point;
                $commission=$amt_sum*$row['royalty'];
                $commission=round($commission,2);

                $newExcel->getSheet(1)->getCellByColumnAndRow(0,$detailRow)->setValue($tempArr["yearMonth"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(1,$detailRow)->setValue($tempArr["city_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow(2,$detailRow)->setValue($tempArr["employee_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num,$detailRow)->setValue("产品生意额");
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+1,$detailRow)->setValue($row["log_dt"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+2,$detailRow)->setValue($row["company_name"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+3,$detailRow)->setValue($row["description"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+4,$detailRow)->setValue($str);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+5,$detailRow)->setValue($row['qty']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+6,$detailRow)->setValue($row['money']);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+7,$detailRow)->setValue($amt_sum);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+8,$detailRow)->setValue($row["royalty"]);
                $newExcel->getSheet(1)->getCellByColumnAndRow($num+9,$detailRow)->setValue($commission);
                //83
            }
        }
    }

	public function setUpdateBool(){
        $this->startDate = date("Y-m-d",strtotime("{$this->year}/{$this->month}/01"));
        $this->endDate = date("Y-m-d",strtotime("{$this->startDate} + 1months - 1day"));
        $ageTime = date("Y-m-01");
        //$ageTime = date("Y-m-d",strtotime("$ageTime - 1 months"));
        if(self::isVivienne()||$ageTime<=$this->startDate) { //只能修改本月及以后的数据
            $this->updateBool=true;
        }else{
            $this->updateBool=false;
        }
    }

	public function listSave($saveType="upload"){
        $fun = $this->getScenario();
        $data = $saveType=="upload"&&key_exists($fun,$_POST)?$_POST[$fun]:array();
        $fun.="Save";
        $this->$fun($data);
	}

    //新增生意额(保存)
    private function newSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->newList();
        $service_reward=$this->getServiceReward();//服务奖励点
        $point=$this->getPoint();//销售提成激励点
        $lbs_new_money=0;//利比斯新增业绩
        $lbs_new_amount=0;//利比斯新增提成
        $new_money=0;//新增业绩
        //更改业绩
        $edit_money=key_exists("edit_money",$this->dtl_list)?$this->dtl_list["edit_money"]:0;

        $new_amount=0;//新增生意提成
        $new_calc=0;//新增提成比例
        $updateRows = array();
        $lbs_updateRows = array();//利比斯专用
        $span_num=0;//参与计算的单数
        $performance=1;//跨区提成是否计算
        if($rows){
            foreach ($rows as $row){ //需要先计算新增的总金额
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                    $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                    $row['amt_sum'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                    if($row["external_source"]==5){
                        $lbs_new_money+=$row['amt_sum'];
                        $lbs_updateRows[]=$row;
                    }else{
                        $updateRows[]=$row;
                        $new_money+=$row['amt_sum'];
                        $span_num++;
                    }
                }else{
                    //不需要提成
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
            //目标金额或者目标单数都不达标
            if($new_money<$this->span_list['sums']&&$span_num<$this->span_list['sum']){
                //$this->span_rate=0;//跨区提成比例为0
                $performance = 0;//不滿足條件，不計算跨區提成
                $this->bonus_other_rate=0;
            }else{
                $performance=1;
                $this->bonus_other_rate=$this->span_other_rate;
            }
            //计算新增业务提成
            $new_calc=SellComputeList::getNewCalc($edit_money+$new_money,$this->startDate,$this->city);
            $royalty = $new_calc+$point+$service_reward;//总提成比例
            //开始修改
            foreach ($updateRows as $updateRow){
                if(!empty($updateRow['othersalesman'])){ //跨区服务
                    $updateRow['amt_sum']*=$this->span_rate;
                }
                $updateRow['amt_sum'] = round($updateRow['amt_sum'],2);
                $commission=$updateRow['amt_sum']*$royalty;
                $commission = round($commission,2);
                $new_amount+=$commission;//新增的提成金额
                //计算
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                    "royalty"=>$royalty,
                    "commission"=>$updateRow['amt_sum']>=0?$updateRow['amt_sum']:$commission,
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$updateRow["id"]));
            }
            //开始修改
            foreach ($lbs_updateRows as $lbs_updateRow){
                $lbs_royalty = 0.05;//利比斯提成固定0.05
                $lbs_updateRow['amt_sum'] = round($lbs_updateRow['amt_sum'],2);
                $commission=$lbs_updateRow['amt_sum']*$lbs_royalty;
                $commission = round($commission,2);
                $lbs_new_amount+=$commission;//新增的提成金额
                //计算
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                    "royalty"=>$lbs_royalty,
                    "commission"=>$lbs_updateRow['amt_sum']>=0?$lbs_updateRow['amt_sum']:$commission,
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$lbs_updateRow["id"]));
            }
        }
        //记录主表修改
        Yii::app()->db->createCommand()->update("acc_service_comm_hdr",array(
            "performance"=>$performance,
            "luu"=>$uid,
            "lud"=>date("Y-m-d H:i:s")
        ),"id=:id",array(":id"=>$this->id));

        //修改主表的提成数据
        $this->saveDtlList(array(
            "new_money"=>$new_money,
            "new_amount"=>$new_amount,
            "lbs_new_money"=>$lbs_new_money,
            "lbs_new_amount"=>$lbs_new_amount,
            "service_reward"=>$service_reward,
            "point"=>$point,
            "new_calc"=>$new_calc
        ));
        if($for_bool){
            $this->resetInstallSave();//刷新装机金额
        }
        $this->simulationClick($for_bool,array("new"));
    }

    //更改生意额(保存)
    private function editSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->editList();
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        $new_calc=0;//新增提成比例
        //服务奖励点
        $service_reward=key_exists("service_reward",$this->dtl_list)?$this->dtl_list["service_reward"]:0;
        //销售提成激励点
        $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
        //新增业绩
        $new_money=key_exists("new_money",$this->dtl_list)?$this->dtl_list["new_money"]:0;
        $edit_money=0;//更改新增业绩
        $edit_amount=0;//更改生意提成
        $updateRows=array();

        if($rows){
            foreach ($rows as $row){ //需要先计算新增的总金额
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                        if(empty($row["history"])){//手动修改历史提成
                            $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                        }
                    }
                    if($row['amt_money']>0){//更改增加
                        $edit_money+=$row['amt_money'];//新增提成比例只计算更改增加
                    }else{ //更改減少的提成需要計算剩餘次數
                        $row['amt_money'] = empty($row['all_number'])?0:$row['amt_money']/floatval($row['all_number'])*floatval($row['surplus']);
                    }
                    $updateRows[]=$row;
                }else{
                    //不需要提成
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
            //计算新增业务提成
            $new_calc=SellComputeList::getNewCalc($edit_money+$new_money,$this->startDate,$this->city);
            $royalty = $new_calc+$point+$service_reward;//总提成比例
            //开始修改
            foreach ($updateRows as $updateRow){
                if(!empty($updateRow['othersalesman'])){ //跨区服务
                    $span_rate=isset($updateRow["history"]['span_rate'])?$updateRow["history"]['span_rate']:$this->span_rate;
                    $updateRow['amt_money'] *=$span_rate;
                }
                $updateRow['amt_money'] = round($updateRow['amt_money'],2);
                $updateRoyalty=key_exists("history",$updateRow)?$updateRow["history"]["royalty"]:$royalty;
                $commission=$updateRow['amt_money']*$updateRoyalty;
                $commission = round($commission,2);
                $edit_amount+=$commission;//更改的提成金额
                //计算
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                    "royalty"=>$updateRoyalty,
                    "commission"=>$updateRow['amt_money']>=0?$updateRow['amt_money']:$commission,
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$updateRow["id"]));
            }
        }else{
            //计算新增业务提成
            $new_calc=SellComputeList::getNewCalc($edit_money+$new_money,$this->startDate,$this->city);
        }
        $this->saveDtlList(array(
            "edit_money"=>$edit_money,
            "edit_amount"=>$edit_amount,
            "new_calc"=>$new_calc
        ));

        if($for_bool){
            $this->resetInstallSave();//刷新装机金额
        }
        $this->simulationClick($for_bool,array("new","edit"));
    }

    //终止生意额(保存)
    private function endSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->endList();
        $rows = $rows["stop"];
        $end_amount=0;//终止生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    if(empty($row["history"])){//手动修改历史提成
                        $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                    }
                    if(!empty($row['othersalesman'])){ //跨区服务
                        $span_rate=isset($row["history"]['span_rate'])?$row["history"]['span_rate']:$this->span_rate;
                        $row['amt_money'] *= $span_rate;
                    }
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $end_amount+=$commission;//更改的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$row["history"]["royalty"],
                        "commission"=>$row['amt_money']>=0?$row['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "end_amount"=>$end_amount
        ));
        $this->simulationClick($for_bool,array("new","edit","end","performance","performanceedit","performanceend","renewal","renewalend","product"));
    }

    //恢复生意额(保存)
    private function recoverySave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->recoveryList();
        $recovery_amount=0;//恢复生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $historyArr = $row["history"];
                    $historyArr['amt_money']*=-1;
                    if($historyArr["commission"]===null||$historyArr["commission"]===''){//手动修改历史提成
                        $royalty=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0;
                        if(!empty($historyArr['othersalesman'])){ //跨区服务
                            $span_rate=isset($historyArr['span_rate'])?$historyArr['span_rate']:$this->span_rate;
                            $historyArr['amt_money'] *= $span_rate;
                        }
                        $commission = $historyArr["amt_money"]*$royalty;//恢复的提成金额
                    }else{
                        $royalty=$historyArr["royalty"];
                        $historyArr["amt_money"] = $historyArr["commission"]*-1;
                        $historyArr['amt_money']=empty($royalty)?0:round($historyArr["amt_money"]/$royalty,2);
                        $commission = $historyArr["commission"]*-1;//恢复的提成金额
                    }
                    $recovery_amount+=$commission;
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$royalty,
                        "commission"=>$historyArr['amt_money']>=0?$historyArr['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "recovery_amount"=>$recovery_amount
        ));
    }

    //跨区新增生意额(保存)
    private function performanceSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->performanceList();
        $performance_amount=0;//跨区新增提成
        $out_money=0;//跨区业绩
        //新增提成比例
        $new_calc=key_exists("new_calc",$this->dtl_list)?$this->dtl_list["new_calc"]:0;
        //销售提成激励点
        $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
        //服务奖励点
        $service_reward=key_exists("service_reward",$this->dtl_list)?$this->dtl_list["service_reward"]:0;

        $royalty = $new_calc+$point+$service_reward;
        //$royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            $target = empty($this->bonus_other_rate)?1:0;//被跨区提成比例为零，放入奖金库
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                    $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                    $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                    $span_other_rate = isset($row["history"]["span_other_rate"])?$row["history"]["span_other_rate"]:$this->span_other_rate;
                    $amt_sum*=$span_other_rate;//跨区
                    $commission =$amt_sum*$royalty;
                    $commission = round($commission,2);
                    $out_money+=$target==1?0:$amt_sum;//跨区业绩
                    $performance_amount+=$target==1?0:$commission;//跨区新增的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$royalty,
                        "target"=>$target,
                        "other_commission"=>$amt_sum>=0?$amt_sum:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>0,
                        "target"=>0,
                        "other_commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "performance_amount"=>$performance_amount,
            "out_money"=>$out_money
        ));
        $this->resetBonusSave();//计算奖金库的金额
    }

    //跨区更改生意额(保存)
    private function performanceeditSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->performanceeditList();
        $performanceedit_amount=0;//跨区更改提成
        $performanceedit_money=0;//跨区跨区更改业绩
        //新增提成比例
        $new_calc=key_exists("new_calc",$this->dtl_list)?$this->dtl_list["new_calc"]:0;
        //销售提成激励点
        $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
        //服务奖励点
        $service_reward=key_exists("service_reward",$this->dtl_list)?$this->dtl_list["service_reward"]:0;

        $royalty = $new_calc+$point+$service_reward;
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $thisRoyalty = $royalty;
                    $target = empty($this->bonus_other_rate)?1:0;//被跨区提成比例为零，放入奖金库
                    if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                        $row['amt_money'] = empty($row['all_number'])?0:$row['amt_money']/floatval($row['all_number'])*floatval($row['surplus']);
                        $target=0;//更改减少不需要放入奖金库
                        if(empty($row["history"])){//手动修改历史提成
                            $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                        }
                        $thisRoyalty=$row["history"]["royalty"];
                    }
                    $span_other_rate = isset($row["history"]["span_other_rate"])?$row["history"]["span_other_rate"]:$this->span_other_rate;
                    $row['amt_money'] =$row['amt_money']*$span_other_rate;//跨区
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$thisRoyalty;
                    $commission = round($commission,2);
                    $performanceedit_amount+=$target==1?0:$commission;//跨区更改提成金额
                    $performanceedit_money+=$row['amt_money']>0&&$target===0?$row['amt_money']:0;//跨区更改业绩
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$thisRoyalty,
                        "target"=>$target,
                        "other_commission"=>$row['amt_money']>=0?$row['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>0,
                        "target"=>0,//是否放入奖金库 0：否  1：是
                        "other_commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }

        $this->saveDtlList(array(
            "performanceedit_amount"=>$performanceedit_amount,
            "performanceedit_money"=>$performanceedit_money
        ));
        $this->resetBonusSave();//计算奖金库的金额
    }

    //跨区终止生意额(保存)
    private function performanceendSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->performanceendList();
        $performanceend_amount=0;//跨区终止生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    if(empty($row["history"])){//手动修改历史提成
                        $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                    }
                    $span_other_rate = isset($row["history"]["span_other_rate"])?$row["history"]["span_other_rate"]:$this->span_other_rate;
                    $row['amt_money'] *= $span_other_rate;//跨区服务
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $performanceend_amount+=$commission;//跨区终止生意提成
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$row["history"]["royalty"],
                        "other_commission"=>$row['amt_money']>=0?$row['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>0,
                        "other_commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "performanceend_amount"=>$performanceend_amount
        ));
        $this->simulationClick($for_bool,array("new","edit","end","performance","performanceedit","performanceend","renewal","renewalend","product"));
    }

    //跨区恢复生意额(保存)
    private function perRecoverySave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->perRecoveryList();
        $perrecovery_amount=0;//跨区恢复生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $historyArr = $row["history"];
                    $span_other_rate = isset($historyArr["span_other_rate"])?$historyArr["span_other_rate"]:$this->span_other_rate;
                    $historyArr["amt_money"]*=-1*$span_other_rate;
                    if($historyArr["other_commission"]===null||$historyArr["other_commission"]===''){//手动修改历史提成
                        $royalty=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0;
                        $other_commission = $historyArr["amt_money"]*$royalty;//恢复的提成金额
                    }else{
                        $royalty=$historyArr["royalty"];
                        $other_commission = $historyArr["other_commission"]*-1;//恢复的提成金额
                    }
                    $other_commission = round($other_commission,2);
                    $perrecovery_amount+=$other_commission;//跨区恢复生意提成
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$royalty,
                        "other_commission"=>$historyArr["amt_money"]>=0?$historyArr["amt_money"]:$other_commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>0,
                        "other_commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "perrecovery_amount"=>$perrecovery_amount
        ));
        $this->simulationClick($for_bool,array("new","edit","end","performance","performanceedit","performanceend","renewal","renewalend","product"));
    }

    //续约生意额(保存)
    private function renewalSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->renewalList();
        $renewal_money=0;//续约生意额
        $renewal_amount=0;//续约提成
        //$royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                    $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                    if($row['paid_type']=="M"){//月金额
                        $row['amt_money'] = $row['amt_paid']*$row['ctrt_period'];
                        $row['month_money'] = $row['amt_paid'];//用来查询提成比例
                    }else{
                        $row['amt_money'] = $row['amt_paid'];
                        $row['month_money'] = empty($row['ctrt_period'])?0:$row['amt_paid']/$row['ctrt_period'];//用来查询提成比例
                    }

                    $royalty=$this->getRenewalRate($row);
                    /* 续约不需要计算跨区
                    if(!empty($row['othersalesman'])){ //跨区服务
                        $row['amt_money'] *= $this->span_rate;
                    }
                    */
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$royalty;
                    $commission = round($commission,2);
                    $renewal_amount+=$commission;//续约提成
                    $renewal_money+=$row['amt_money'];//续约生意额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$royalty,
                        "commission"=>$row['amt_money']>=0?$row['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "renewal_money"=>$renewal_money,
            "renewal_amount"=>$renewal_amount
        ));
    }

    //续约终止生意额(保存)
    private function renewalendSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->endList();
        $rows = $rows["renewal"];
        $renewalend_amount=0;//续约终止生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    if(empty($row["history"])){//手动修改历史提成
                        $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                    }
                    if(!empty($row['othersalesman'])){ //跨区服务
                        $span_rate=isset($row["history"]['span_rate'])?$row["history"]['span_rate']:$this->span_rate;
                        $row['amt_money'] *= $span_rate;
                    }
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $renewalend_amount+=$commission;//更改的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$row["history"]["royalty"],
                        "commission"=>$row['amt_money']>=0?$row['amt_money']:$commission,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>0,
                        "commission"=>null,
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }
        $this->saveDtlList(array(
            "renewalend_amount"=>$renewalend_amount
        ));
        $this->simulationClick($for_bool,array("new","edit","end","performance","performanceedit","performanceend","renewal","renewalend","product"));
    }

    //产品生意额(保存)
    private function productSave($data,$for_bool=true){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $rows = $this->productList();
        $product_amount=0;//产品提成
        $computeRate=array();//保存已计算的产品提成比例
        //销售提成激励点
        $point=key_exists("point",$this->dtl_list)?$this->dtl_list["point"]:0;
        //更改新增业绩
        $edit_money=key_exists("edit_money",$this->dtl_list)?$this->dtl_list["edit_money"]:0;
        //更改新增业绩+新增业绩
        $edit_money+=key_exists("new_money",$this->dtl_list)?$this->dtl_list["new_money"]:0;

        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $type = $row['sales_products'];
                    //var_dump($type);die();
                    if(!empty($type)&&$type!='wu') { //没分类的产品不允许计算提成
                        $row['qty'] = is_numeric($row['qty'])?floatval($row['qty']):0;
                        $row['money'] = is_numeric($row['money'])?floatval($row['money']):0;
                        $amt_sum = $row['qty']*$row['money'];
                        if(!key_exists($type,$computeRate)){
                            $computeRate[$type] = SellComputeList::getProductRate($edit_money,$this->startDate,$this->city,$type);
                        }
                        //提成点 = 产品提成点 + 销售提成激励点
                        $thisRoyalty = $computeRate[$type]+$point;
                        $commission =$amt_sum*$thisRoyalty;
                        $commission = round($commission,2);
                        $product_amount+=$commission;//产品提成
                        //计算
                        Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_logistic_dtl",array(
                            "commission"=>1,//1:参加计算 2：不参加
                            "luu"=>$uid
                        ),"id=:id",array(":id"=>$row["id"]));
                    }
                }else{
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_logistic_dtl",array(
                        "commission"=>2,//1:参加计算 2：不参加
                        "luu"=>$uid
                    ),"id=:id",array(":id"=>$row["id"]));
                }
            }
        }

        $this->saveDtlList(array(
            "product_amount"=>$product_amount
        ));
    }

    private function saveDtlList($data){
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        //修改副表
        $list = array();
        foreach ($data as $key=>$value){
            if(key_exists($key,$this->dtl_list)){
                if(floatval($this->dtl_list[$key])!=$value){
                    $list[$key] = $value; //有数据变动，需要修改
                    $this->dtl_list[$key] = $value;
                }
            }
        }
        if(!empty($list)){
            $list['luu']=$uid;
            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",$list,"hdr_id=:id",array(":id"=>$this->id));
        }
    }

    private function getClickMenu(){
        return array(
            "new"=>array("list"=>"newList","save"=>"newSave","royalty"=>"royalty"),
            "edit"=>array("list"=>"editList","save"=>"editSave","royalty"=>"royalty"),
            "end"=>array("list"=>"endList","save"=>"endSave","royalty"=>"royalty"),
            "recovery"=>array("list"=>"recoveryList","save"=>"recoverySave","royalty"=>"royalty"),
            "performance"=>array("list"=>"performanceList","save"=>"performanceSave","royalty"=>"royaltys"),
            "performanceedit"=>array("list"=>"performanceeditList","save"=>"performanceeditSave","royalty"=>"royaltys"),
            "performanceend"=>array("list"=>"performanceendList","save"=>"performanceendSave","royalty"=>"royaltys"),
            "perRecovery"=>array("list"=>"perRecoveryList","save"=>"perRecoverySave","royalty"=>"royaltys"),
            "renewal"=>array("list"=>"renewalList","save"=>"renewalSave","royalty"=>"royalty"),
            "renewalend"=>array("list"=>"endList","save"=>"renewalendSave","royalty"=>"royalty"),
            "product"=>array("list"=>"productList","save"=>"productSave","royalty"=>"commission"),
        );
    }

    //模拟点击其它服务
    private function simulationClick($bool=true,$notIdList){
        if($bool){
            $clickMenu=self::getClickMenu();
            foreach ($clickMenu as $id=>$arr){
                if(in_array($id,$notIdList)){
                    continue;
                }
                $funcList = $arr["list"];
                $funcSave = $arr["save"];
                $rows = $this->$funcList(true);
                if($funcList=="endList"){
                    if($id=="end"){
                        $rows=$rows["stop"];
                    }else{
                        $rows=$rows["renewal"];
                    }
                }
                $postData=array();
                $_POST["royalty"]=array();
                foreach ($rows as $row){
                    $postData[$row["id"]]=$row["id"];
                    $_POST["royalty"][$row["id"]]=$row[$arr["royalty"]];
                }
                $this->$funcSave($postData,false);
            }
        }
    }

    //奖金库的计算逻辑
    private function resetBonusSave(){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $bonusMoney = Yii::app()->db->createCommand()
            ->select("sum(a.other_commission)")
            ->from("swoper{$suffix}.swo_service a")
            ->where("(
                (a.status='N' and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}')
                 or 
                (a.status='A' and a.status_dt between '{$this->startDate}' and '{$this->endDate}')
                ) and a.target=1 and a.other_commission+0>0 and a.city='{$this->city}'")
            ->queryScalar();
        $bonusMoney*=0.04;
        $bonusMoney=round($bonusMoney,2);
        $year = $this->year;
        $month = $this->month;
        $month++;
        if($month>12){ //服务金额放到下一个月的奖金库
            $year++;
            $month = 1;
        }
        $month = $month>=10?$month:"0".$month;
        $bonusRow = Yii::app()->db->createCommand()->select("id,money")->from("acc_bonus")
            ->where("city='$this->city' and year='{$year}' and month='{$month}'")
            ->queryRow();
        if($bonusRow){
            Yii::app()->db->createCommand()->update("acc_bonus",array(
                "money"=>$bonusMoney,
                "luu"=>$uid
            ),"id=:id",array(":id"=>$bonusRow["id"]));
        }else{
            Yii::app()->db->createCommand()->insert("acc_bonus",array(
                "city"=>$this->city,
                "year"=>$year,
                "month"=>$month,
                "money"=>$bonusMoney,
                "lcu"=>$uid,
                "luu"=>$uid,
            ));
        }

    }

    //刷新装机金额
    public function resetInstallSave($data=array()){
        $installRate = $this->getPaperRateAndPoint($data);
        $suffix = Yii::app()->params['envSuffix'];
        $install_amount=0;//装机提成
        $install_money=0;//装机业绩

        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.amt_install,a.commission")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.commission is not null and (
            (a.status='N' and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}') or 
            (a.status='A' and a.status_dt between '{$this->startDate}' and '{$this->endDate}')
            ) and a.city='{$this->city}' and a.salesman_id={$this->employee_id} and a.amt_install+0>0")->queryAll();
        if($rows){
            foreach ($rows as $row){
                if($row["commission"]>=0){ //提成金額為負數，則不計算裝機費
                    $amt_sum = is_numeric($row['amt_install'])?floatval($row['amt_install']):0;

                    $commission =$amt_sum*$installRate;
                    $commission = round($commission,2);
                    $install_amount+=$commission;//装机提成
                    $install_money+=$amt_sum;//装机业绩
                }
            }
        }

        Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
            "install_amount"=>$install_amount,
            "install_money"=>$install_money
        ),"hdr_id=:id",array(":id"=>$this->id));
    }

	public function isReadOnly(){
	    return !$this->updateBool;
    }

    //计算销售提成激励点
    private function getPoint(){
	    $point = 0;
        $suffix = Yii::app()->params['envSuffix'];
        $staffRow = Yii::app()->db->createCommand()
            ->select("a.user_id,f.manager_type,d.entry_time")
            ->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee d","d.id=a.employee_id")
            ->leftJoin("hr$suffix.hr_dept f","f.id=d.position")
            ->where("a.employee_id={$this->employee_id} and f.manager_type in (1,2)")
            ->queryRow();//員工及副經理才有销售提成激励点：f.manager_type in (1,2)
        if($staffRow){
            $salesBool = true;//是否需要銷售系統的銷售提成點數
            $entry_time = date("Y-m-01",strtotime("{$staffRow['entry_time']} + 1 months"));
            if($entry_time>=$this->startDate){
                //新入職員工需要判斷該員工是否在本月1號有銷售拜訪
                $visitRow = Yii::app()->db->createCommand()->select("id")
                    ->from("sales$suffix.sal_visit")
                    ->where("city='{$this->city}' and username=:id and visit_dt<='{$this->startDate}'",array(":id"=>$staffRow["user_id"]))
                    ->queryRow();
                if(!$visitRow){
                    $salesBool = false;//當月一號沒有銷售拜訪
                }
            }
            if($salesBool){
                $integralRow = Yii::app()->db->createCommand()->select("id,point")
                    ->from("sales$suffix.sal_integral")
                    ->where("city='{$this->city}' and year={$this->year} and month={$this->month} and username=:id",array(":id"=>$staffRow["user_id"]))
                    ->queryRow();
                if($integralRow){
                    $point = empty($integralRow['point'])?0:floatval($integralRow['point']);
                    Yii::app()->db->createCommand()->update("sales$suffix.sal_integral",array(
                        "hdr_id"=>$this->id,
                        //"luu"=>Yii::app()->user->id //不知道为啥，这张表没有这个字段
                    ),"id=:id",array(":id"=>$integralRow["id"]));
                }
            }
        }
        return $point;
    }

    //计算服务奖励点
    private function getServiceReward(){
        $suffix = Yii::app()->params['envSuffix'];
        //检测是否有配送过三瓶以上的洗地易
        if($this->startDate>="2025-02-01"){
            return 0;//2025年2月开始，取消创新提成点
        }
        $logisticSum = Yii::app()->db->createCommand()->select("sum(a.qty)")
            ->from("swoper$suffix.swo_logistic_dtl a")
            ->leftJoin("swoper$suffix.swo_logistic b","a.log_id = b.id")
            ->leftJoin("swoper$suffix.swo_task c","a.task = c.id")
            ->where("b.city='{$this->city}' and c.task_type='FLOOR' and b.salesman='{$this->staff}' and money>0 and b.log_dt between '{$this->startDate}' and '{$this->endDate}'")
            ->queryScalar();
        if($logisticSum>=3){//满足三瓶洗地易
            //2022/01/01服務獎勵點改名為创新业务提成点（並修改邏輯）
            $serviceMoney = $this->serviceFourMoney();
            if($serviceMoney>=2500){
                return 0.01;
            }
        }
        return 0;
    }

    //非一次性新增业务的金額
    private function serviceFourMoney(){
        $suffix = Yii::app()->params['envSuffix'];
        $dateSql = " and if(ifnull(a.first_dt,'2222-12-31')>a.status_dt,a.first_dt,a.status_dt) between '{$this->startDate}' and '{$this->endDate}'";
        $dateIDSql = " and a.status_dt between '{$this->startDate}' and '{$this->endDate}'";
        $serviceMoney =Yii::app()->db->createCommand()
            ->select("sum(CASE WHEN a.paid_type = 'M' THEN a.amt_paid*a.ctrt_period ELSE a.amt_paid END)")
            ->from("swoper$suffix.swo_service a")
            ->leftJoin("swoper$suffix.swo_customer_type_twoname b","a.cust_type_name = b.id")
            ->leftJoin("swoper$suffix.swo_customer_type c","a.cust_type = c.id")
            ->where("a.city='{$this->city}' and b.bring = 1 and a.status = 'N' and a.salesman_id='{$this->employee_id}' $dateSql")
            ->queryScalar();
        $serviceMoney=$serviceMoney?floatval($serviceMoney):0;
        $serviceIDMoney =Yii::app()->db->createCommand()
            ->select("sum(a.amt_money)")
            ->from("swoper$suffix.swo_serviceid a")
            ->where("a.city='{$this->city}' and a.status = 'N' and a.salesman_id='{$this->employee_id}' $dateIDSql")
            ->queryScalar();
        $serviceIDMoney=$serviceIDMoney?floatval($serviceIDMoney):0;
        return $serviceMoney+$serviceIDMoney;
    }

    //获取续约的提成比例
    private function getRenewalRate($service){
        switch ($service["nature_rpt"]){
            case "A01"://餐饮
                return $this->getRenewalRateForA($service);
            case "B01"://非餐饮
                return $this->getRenewalRateForB($service);
            default:
                return 0;
        }
    }

    //餐饮提成点数
    private function getRenewalRateForA($service){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("id,group_id,group_name")
            ->from("swoper{$suffix}.swo_company")
            ->where("id=:id",array(":id"=>$service["company_id"]))->queryRow();
        if($row){
            if(!empty($row["group_id"])&&!empty($row["group_name"])){//集团编号不能為空
                $count = Yii::app()->db->createCommand()
                    ->select("count(id)")
                    ->from("swoper{$suffix}.swo_company")
                    ->where("status=1 and group_id=:group_id and group_name=:group_name",array(
                            ":group_id"=>$row["group_id"],
                            ":group_name"=>$row["group_name"]
                        )
                    )->queryScalar();
                if($count>=10){//找到十家集团编号相同且在服务中的客户资料
                    return 0.01;
                }
            }
        }
        return 0;
    }

    //非餐饮提成点数:月金额（可不同服务累加）大于等于minMoney
    private function getRenewalRateForB($service){
        $date = date("Y/m",strtotime($service["status_dt"]));
        $suffix = Yii::app()->params['envSuffix'];
        $minMoney=1000;//最低的月金额
        if(in_array($this->city,array("GZ","SH","SZ","BJ"))){ //一线城市
            $minMoney=2000;
        }
        $month_money = 0;
        $rows = Yii::app()->db->createCommand()
            ->select("id,paid_type,amt_paid,ctrt_period")
            ->from("swoper{$suffix}.swo_service")
            ->where("date_format(status_dt,'%Y/%m') = '{$date}'and company_id=:company_id and salesman_id=:salesman_id and status='C'",
                array(":company_id"=>$service["company_id"],":salesman_id"=>$service["salesman_id"])
            )->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row["ctrt_period"] = empty($row["ctrt_period"])?0:floatval($row["ctrt_period"]);
                $row["amt_paid"] = empty($row["amt_paid"])?0:floatval($row["amt_paid"]);
                if($row["paid_type"]!="M"){
                    $money = !empty($row["ctrt_period"])?$row["amt_paid"]/$row["ctrt_period"]:0;
                }else{
                    $money = $row["amt_paid"];
                }
                $month_money+=is_numeric($money)?$money:0;
            }
        }
        if($month_money>=$minMoney){//2023-03-03年修改金额允许同公司累加
            return 0.01;
        }
/*        if($service["month_money"]>=$minMoney){
            return 0.01;
        }*/
        return 0;
    }

    //纸品的提成包含激励点（装机提成专用）
    private function getPaperRateAndPoint($data=array()){
        $point =key_exists('point',$data)?$data['point']:$this->dtl_list['point'];
        $new_money =key_exists('new_money',$data)?$data['new_money']:$this->dtl_list['new_money'];
        $edit_money =key_exists('edit_money',$data)?$data['edit_money']:$this->dtl_list['edit_money'];
        $money = $new_money+$edit_money;
        $rate = SellComputeList::getProductRate($money,$this->startDate,$this->city,"paper");
        return $rate+$point;
    }

    //显示总数
    public function getTextSpanHtml(){
        return "总记录:{$this->textSum}条,已计算:{$this->textNum}条";
    }

    //下载excel
    public function downExcelAll($idList){
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        if(!empty($idList)){
            $newExcel = new PHPExcel();
            $newExcel->getDefaultStyle()->getFont()
                ->setSize(10);
            $newExcel->getDefaultStyle()->getAlignment()
                ->setWrapText(true);
            $newExcel->getActiveSheet()->getDefaultRowDimension()
                ->setRowHeight(-1);
            $this->printExcelAll($idList,$newExcel);
            //輸出excel
            $objWriter = PHPExcel_IOFactory::createWriter($newExcel, 'Excel2007');
            ob_start();
            $objWriter->save('php://output');
            $output = ob_get_clean();
            spl_autoload_register(array('YiiBase','autoload'));
            $str="销售提成汇总-All";
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

    private function printExcelAll($idList,&$newExcel){
        //$newExcel = new PHPExcel();
        $newExcel->getSheet(0)->setTitle("总页");
        $newExcel->createSheet(1)->setTitle("明细");
        $summaryRow=1;
        $detailRow=1;
        $summaryTitleArr = $this->getSummaryTitleArr();
        $this->setExcelTitle($newExcel,$summaryTitleArr,$summaryRow,0);

        $summaryDetailArr=$this->getSummaryDetailArr();
        $this->setExcelTitle($newExcel,$summaryDetailArr,$detailRow,1);

        foreach ($idList as $id){
            if($this->retrieveData($id)){
                $summaryRow++;
                $tempArr = $this->getSummaryPrintData();
                $this->setSummaryBody($newExcel,$summaryRow,$summaryTitleArr,$tempArr);
                $this->setDetailBody($newExcel,$detailRow,$tempArr);
            }
        }
    }

    private function setDetailBody($newExcel,&$detailRow,$tempArr){
        $installRate = $this->getPaperRateAndPoint();//装机提成
        //新生意额
        $newRows = $this->newList(true);
        $this->newExcel($newRows,$tempArr,$newExcel,$detailRow,$installRate);

        //更改生意额
        $editLists = $this->editList(true);
        $this->editExcel($editLists,$tempArr,$newExcel,$detailRow,$installRate);

        //终止生意额
        $endLists = $this->endList(true);
        $this->endExcel($endLists,$tempArr,$newExcel,$detailRow);

        //跨区新增生意额
        $performanceLists = $this->performanceList(true);
        $this->performanceExcel($performanceLists,$tempArr,$newExcel,$detailRow);

        //跨区更改生意额
        $performanceeditLists = $this->performanceeditList(true);
        $this->performanceeditExcel($performanceeditLists,$tempArr,$newExcel,$detailRow);

        //跨区终止生意额
        $performanceendLists = $this->performanceendList(true);
        $this->performanceendExcel($performanceendLists,$tempArr,$newExcel,$detailRow);

        //续约生意额
        $renewalLists = $this->renewalList(true);
        $this->renewalExcel($renewalLists,$tempArr,$newExcel,$detailRow);

        //续约终止生意额
        $this->renewalendExcel($endLists,$tempArr,$newExcel,$detailRow);

        //产品生意额
        $productLists = $this->productList(true);
        $this->productExcel($productLists,$tempArr,$newExcel,$detailRow);

        //恢复生意额
        $recoveryLists = $this->recoveryList(true);
        $this->recoveryExcel($recoveryLists,$tempArr,$newExcel,$detailRow);

        //跨区恢复生意额
        $perRecoveryLists = $this->perRecoveryList(true);
        $this->perRecoveryExcel($perRecoveryLists,$tempArr,$newExcel,$detailRow);
    }

    private function setSummaryBody($newExcel,$summaryRow,$summaryTitleArr,$tempArr){
        foreach ($summaryTitleArr as $num=>$row){
            $key = $row["key"];
            if(key_exists($key,$tempArr)){
                $newExcel->getSheet(0)->getCellByColumnAndRow($num,$summaryRow)->setValue($tempArr[$key]);
            }
        }
    }

    private function getSummaryPrintData(){
        $tempArr = array(
            "yearMonth"=>date_format(date_create($this->startDate),"Y年m月"),
        );
        $tempArr["group_type"] = SellComputeForm::getGroupName($this->group_type);
        $modelKey=array("city_name","employee_name","performance");
        foreach ($modelKey as $item){
            $tempArr[$item] = $this->$item;
        }
        $tempArr["new_point_reward"]=0;//提成点数
        $dtlKey = array("new_calc","point","service_reward","new_money","lbs_new_money","edit_money","out_money","performanceedit_money",
            "renewal_money","install_money","supplement_money","new_amount","lbs_new_amount","edit_amount","end_amount","recovery_amount","performance_amount","performanceedit_amount","performanceend_amount","perrecovery_amount","renewal_amount",
            "renewalend_amount","product_amount","install_amount");
        foreach ($dtlKey as $item){
            if(in_array($item,array("new_calc","point","service_reward"))){
                $tempArr["new_point_reward"]+=$this->dtl_list[$item];
            }
            $tempArr[$item] = SellComputeList::showText($this->dtl_list[$item],$this->showNull);
        }
        $tempArr["new_point_reward"] = SellComputeList::showText($tempArr["new_point_reward"],$this->showNull);
        $onlyKey = array("span_rate","span_other_rate","final_money","all_amount");
        foreach ($onlyKey as $item){
            $tempArr[$item] = SellComputeList::showText($this->$item,$this->showNull);
        }
        return $tempArr;
    }

    private function setExcelTitle(&$newExcel,$arr,$currentRow=1,$sheetNum=0){
        //$newExcel = new PHPExcel();
        // 创建边框样式
        $borderStyle = array(
            'borders' => array(
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            )
        );
        foreach ($arr as $num=>$row){
            $newExcel->getSheet($sheetNum)->getCellByColumnAndRow($num,$currentRow)->setValue($row["title"]);
            $newExcel->getSheet($sheetNum)->getStyleByColumnAndRow($num,$currentRow)->applyFromArray($borderStyle);
            $newExcel->getSheet($sheetNum)->getStyleByColumnAndRow($num,$currentRow)->getAlignment()->setHorizontal("center")->setVertical("center");
            $newExcel->getSheet($sheetNum)->getStyleByColumnAndRow($num,$currentRow)->getFont()->setBold(true);
            if(key_exists("width",$row)&&!empty($row["width"])){
                $newExcel->getSheet($sheetNum)->getColumnDimensionByColumn($num)->setWidth($row["width"]);
            }
            if(key_exists("height",$row)&&!empty($row["height"])){
                $newExcel->getSheet($sheetNum)->getRowDimension($currentRow)->setRowHeight($row["height"]);
            }
            if(key_exists("background",$row)&&!empty($row["background"])){
                $newExcel->getSheet($sheetNum)->getStyleByColumnAndRow($num,$currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB($row["background"]);
            }
            if(key_exists("color",$row)&&!empty($row["color"])){
                $newExcel->getSheet($sheetNum)->getStyleByColumnAndRow($num,$currentRow)->getFont()->getColor()->setRGB($row["color"]);
            }
        }
    }

    private function getSummaryTitleArr(){
        return array(
            array("key"=>"yearMonth","title"=>"年月","width"=>"14pt","height"=>"27pt","background"=>"D6DCE4","color"=>""),
            array("key"=>"city_name","title"=>"地区","width"=>"10pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("key"=>"employee_name","title"=>"姓名","width"=>"14pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("key"=>"new_money","title"=>"新增业绩","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("key"=>"lbs_new_money","title"=>"利比斯新增业绩","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("key"=>"edit_money","title"=>"更改新增业绩","width"=>"14pt","height"=>"","background"=>"D9E1F4","color"=>""),
            //array("key"=>"end_money","title"=>"终止生意额","width"=>"14pt","height"=>"","background"=>"D9E1F4","color"=>""),
            array("key"=>"out_money","title"=>"跨区新增业绩","width"=>"14pt","height"=>"","background"=>"D9E1F4","color"=>""),
            array("key"=>"performanceedit_money","title"=>"跨区更改新增业绩","width"=>"18pt","height"=>"","background"=>"D9E1F4","color"=>""),
            //array("key"=>"performanceend_money","title"=>"跨区终止业绩","width"=>"14pt","height"=>"","background"=>"","color"=>""),
            array("key"=>"renewal_money","title"=>"续约业绩","width"=>"10pt","height"=>"","background"=>"D9E1F4","color"=>""),
            //array("key"=>"product_money","title"=>"产品生意额","width"=>"16pt","height"=>"","background"=>"D9E1F4","color"=>""),
            array("key"=>"install_money","title"=>"装机业绩","width"=>"10pt","height"=>"","background"=>"D9E1F4","color"=>""),
            array("key"=>"new_calc","title"=>"新增提成比例","width"=>"14pt","height"=>"","background"=>"D2F4F2","color"=>"C00000"),
            array("key"=>"point","title"=>"销售提成激励点","width"=>"16pt","height"=>"","background"=>"D2F4F2","color"=>"C00000"),
            array("key"=>"service_reward","title"=>"创新业务提成点","width"=>"16pt","height"=>"","background"=>"D2F4F2","color"=>"C00000"),
            array("key"=>"new_point_reward","title"=>"提成点数","width"=>"10pt","height"=>"","background"=>"D2F4F2","color"=>""),
            array("key"=>"span_rate","title"=>"跨区提成比例","width"=>"14pt","height"=>"","background"=>"D2F4F2","color"=>""),
            array("key"=>"span_other_rate","title"=>"被跨区提成比例","width"=>"16pt","height"=>"","background"=>"D2F4F2","color"=>""),
            array("key"=>"new_amount","title"=>"新增生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"lbs_new_amount","title"=>"利比斯新增生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"edit_amount","title"=>"更改生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"end_amount","title"=>"终止生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"recovery_amount","title"=>"恢复生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"performance_amount","title"=>"跨区新增提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"performanceedit_amount","title"=>"跨区更改提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"performanceend_amount","title"=>"跨区终止提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"perrecovery_amount","title"=>"跨区恢复提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"renewal_amount","title"=>"续约生意提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"renewalend_amount","title"=>"续约终止提成","width"=>"14pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"product_amount","title"=>"产品提成","width"=>"10pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"install_amount","title"=>"装机提成","width"=>"10pt","height"=>"","background"=>"FADADE","color"=>""),
            array("key"=>"all_amount","title"=>"总额","width"=>"8pt","height"=>"","background"=>"","color"=>""),
            array("key"=>"supplement_money","title"=>"补充金额","width"=>"10pt","height"=>"","background"=>"","color"=>""),
            array("key"=>"final_money","title"=>"最终合计金额","width"=>"14pt","height"=>"","background"=>"","color"=>""),
        );
    }

    private function getSummaryDetailArr(){
        return array(
                array("title"=>"年月","width"=>"9pt","height"=>"27pt","background"=>"D6DCE4","color"=>""),
                array("title"=>"地区","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"姓名","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"首次日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"被跨区业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"新增提成比例","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>"C00000"),
                array("title"=>"销售提成激励点","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>"C00000"),
                array("title"=>"创新业务提成点","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>"C00000"),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"装机金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"装机提成","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"更改日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"被跨区业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额(更改前)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额(更改后)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"剩余次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"装机金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"装机提成","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"更改日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"被跨区业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"剩余次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"首次日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"更改日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额(更改前)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额(更改后)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"剩余次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"历史提成比例(例1%：0.01)	","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"更改日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"剩余次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"续约日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"性质","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"续约总金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"更改日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"被跨区业务员","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"服务总次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"剩余次数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"出单日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"产品名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"产品分类","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"数量","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"单价","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"产品总金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
                array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),

            array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"恢复日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"性质","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),

            array("title"=>"类型","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"恢复日期","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"客户名称","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"类别","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"性质","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"合同年限(月)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"服务金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"变动金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"历史提成比例(例1%：0.01)","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"提成点数","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),
            array("title"=>"提成金额","width"=>"9pt","height"=>"","background"=>"D6DCE4","color"=>""),

        );
    }

    public static function getExternalSourceForKey($num=''){
        $num = "".$num;
        //1=史伟莎,2=中央KA,3=马氏,4=敏捷,5=利比斯
        $list = array(
            0=>"",
            1=>"史伟莎",
            2=>"中央KA",
            3=>"马氏",
            4=>"敏捷",
            5=>"利比斯",
        );
        if(key_exists($num,$list)){
            return $list[$num];
        }else{
            return $num;
        }
    }

    //批量审核某月的所有销售提成
    public function auditAll($year,$month,$id=''){
        $whereSql = "";
        if(!empty($id)){
            $whereSql = " and id={$id}";
        }
        $sellRows = Yii::app()->db->createCommand()->select("id,employee_code,employee_name")->from("acc_service_comm_hdr")
            ->where("year_no=:year and month_no=:month {$whereSql}",array(":year"=>$year,":month"=>$month))->queryAll();
        if($sellRows){
            $this->setScenario("view");
            foreach ($sellRows as $sellRow){
                echo "staff:".$sellRow["employee_name"]."({$sellRow["employee_code"]})";
                $bool = $this->retrieveData($sellRow["id"],false);
                if($bool){
                    $this->auditAllOne();
                    echo " - Success!";
                }else{
                    echo " - Error!";
                }
                echo "<br>\n";
            }
        }
    }

    private function auditAllOne(){
        $clickMenu=self::getClickMenu();
        foreach ($clickMenu as $id=>$arr){
            $funcList = $arr["list"];
            $funcSave = $arr["save"];
            $rows = $this->$funcList();
            if($funcList=="endList"){
                if($id=="end"){
                    $rows=$rows["stop"];
                }else{
                    $rows=$rows["renewal"];
                }
            }
            $postData=array();
            $_POST["royalty"]=array();
            $deRoyalty = $id=="perRecovery"?0:0.01;//恢复的默认提成是0，其它默认提成是0.01
            foreach ($rows as $row){
                $postData[$row["id"]]=$row["id"];
                $royalty = floatval($row[$arr["royalty"]]);
                $royalty = empty($royalty)?$deRoyalty:$royalty;
                $_POST["royalty"][$row["id"]]=$royalty;
            }
            $this->$funcSave($postData,false);
        }
        $this->resetInstallSave();//需要额外计算装机金额
    }

    public static function isVivienne(){
        $vivienneList = isset(Yii::app()->params['vivienneList'])?Yii::app()->params['vivienneList']:array("VivienneChen88888");
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        $thisData = date("Y-m-d H:i:s");
        return $thisData<="2025-07-03 18:00:00"||in_array($uid,$vivienneList);
    }
}