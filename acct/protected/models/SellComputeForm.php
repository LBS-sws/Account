<?php

class SellComputeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $employee_id;
	public $employee_code;
	public $employee_name;
	public $group_type;
	public $performance;
	public $all_amount;
	public $staff;//查詢用的格式：員工名稱 (員工編號)
    public $year;
    public $month;
    public $startDate="2022-05-01";
    public $endDate="2022-05-31";

    public $span_id;//跨区业绩目标id
    public $span_rate=0;//跨区提成比例
    public $span_other_rate=0;//被跨区的提成比例
    public $span_list=array();//跨区业绩目标数组
    public $city;
    public $city_name;
    public $lcu;

    public $showNull=true;
    public $updateBool=true;
    public $dtl_list = array();//首頁的所有信息

    public $viewType="";
    //new,edit,end,performance,performanceedit,performanceend,renewal,renewalend,product,
    public static $viewList = array(
        'view'=>array('key'=>'view','name'=>'ALL'),
        'new'=>array('key'=>'new','name'=>'New'),
        'edit'=>array('key'=>'edit','name'=>'Edit'),
        'end'=>array('key'=>'end','name'=>'END'),
        'performance'=>array('key'=>'performance','name'=>'Performance'),
        'performanceedit'=>array('key'=>'performanceedit','name'=>'PerformanceEdit'),
        'performanceend'=>array('key'=>'performanceend','name'=>'PerformanceEnd'),
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
		$suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.code,b.name,b.group_type,b.id as employee_id")
            ->from("acc_service_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.code=a.employee_code")
            ->where("a.id=:id {$sqlEpr}",array(":id"=>$index))->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->employee_id = $row['employee_id'];
			$this->employee_code = $row['code'];
			$this->employee_name = $row['name'];
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
            $this->showNull = $row['lcd']==$row['lud'];
            $this->setUpdateBool();
            if($bool){
                $this->updateBool=false;
            }
			$this->computeDtlList();
			$this->setSpan();
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
                ->where("a.status='N' and a.commission is not null and a.first_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
                ->queryScalar();
            //金额达标或者参加计算的单数达标
            if($new_money>=$row['sums']||$serviceNum>=$row['sum']){
                switch ($this->group_type){
                    case 0://0:无
                        $this->span_rate = floatval($row["spanning"]);
                        $this->span_other_rate = floatval($row["otherspanning"]);
                        break;
                    case 1://1:商业组
                        $this->span_rate = floatval($row["business_spanning"]);
                        $this->span_other_rate = floatval($row["business_otherspanning"]);
                        break;
                    case 2://2:餐饮组
                        $this->span_rate = floatval($row["restaurant_spanning"]);
                        $this->span_other_rate = floatval($row["restaurant_otherspanning"]);
                        break;
                }
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
        $this->dtl_list=array();
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
                "lcu"=>Yii::app()->user->id
            ));
        }
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
    public function newList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='N' and b.sales_rate=1 and a.first_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
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
                $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $checkBool = $row['commission']==="未计算"?false:true;
                $html.="<tr>";
                $html.="<td>".TbHtml::checkBox("{$type}[{$row['id']}]",$checkBool,array('class'=>'checkOne','readonly'=>$this->isReadOnly()))."</td>";
                $html.="<td>".General::toDate($row['first_dt'])."</td>";
                $html.="<td>".$row['company_name']."</td>";
                $html.="<td>".$row['type_desc']."</td>";
                $html.="<td>".$row['othersalesman']."</td>";
                $html.="<td>".$row['ctrt_period']."</td>";
                $html.="<td>".self::getPaidTypeName($row['paid_type'])."：".$row['amt_paid']."</td>";
                $html.="<td>".$amt_sum."</td>";
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                //$row['amt_install'] = empty($row['amt_install'])?"":$row['amt_install'];
                $html.="<td style='border-left: 1px solid #f4f4f4'>".$row['amt_install']."</td>";
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $amt_install = $row['commission']==="未计算"?"未计算":$amt_install;
                $html.="<td>".$amt_install."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //跨区新增
    public function performanceList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='N' and b.sales_rate=1 and a.first_dt between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();;
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
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //续约
    public function renewalList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc,f.rpt_cat as nature_rpt,f.description as nature_name")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->leftJoin("swoper{$suffix}.swo_nature f","a.nature_type=f.id")
        ->where("a.status='C' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id}")
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
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //变更
    public function editList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='A' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['b4_amt_paid'] = is_numeric($row['b4_amt_paid'])?floatval($row['b4_amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $before_sum = $row['b4_paid_type']=="M"?$row['b4_amt_paid']*$row['ctrt_period']:$row['b4_amt_paid'];
                $after_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['amt_money']=$after_sum-$before_sum;
                if($row['amt_money']<0){
                    $row['history']=SellComputeList::getBeforeServiceList($row,"N");
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
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }else{ //金额增加
                    $html.="<td>&nbsp;</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="<td style='border-left: 1px solid #f4f4f4'>".$row['amt_install']."</td>";
                $amt_install = $row['amt_install'];
                $amt_install = is_numeric($amt_install)?floatval($amt_install)*$installRate:"";
                $amt_install = $row['commission']==="未计算"?"未计算":$amt_install;
                $html.="<td>".$amt_install."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //跨区变更
    public function performanceeditList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='A' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['b4_amt_paid'] = is_numeric($row['b4_amt_paid'])?floatval($row['b4_amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $before_sum = $row['b4_paid_type']=="M"?$row['b4_amt_paid']*$row['ctrt_period']:$row['b4_amt_paid'];
                $after_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                $row['amt_money']=$after_sum-$before_sum;
                if($row['amt_money']<0){
                    $row['history']=SellComputeList::getBeforeServiceList($row,"N");
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
                        $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                    }else{//没有历史提成
                        $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                        $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                    }
                }else{ //金额增加
                    $html.="<td>&nbsp;</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //终止
    public function endList(){ //1：终止服务  2：续约终止
        $suffix = Yii::app()->params['envSuffix'];
        $list = array('stop'=>array(),'renewal'=>array());
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='T' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.salesman_id={$this->employee_id} and a.othersalesman_id!={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['all_number'] = is_numeric($row['all_number'])?floatval($row['all_number']):0;
                $row['surplus'] = is_numeric($row['surplus'])?floatval($row['surplus']):0;
                $row['amt_money'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                //变动金额 = (总金额/服务总次数) * 剩余次数
                $row['amt_money']=empty($row['all_number'])?0:$row['amt_money']/$row['all_number']*$row['surplus'];
                $row['amt_money']=round($row['amt_money'],2)*-1;
                $arr = SellComputeList::getBeforeServiceList($row,'C');
                if(empty($arr)){
                    $arr = SellComputeList::getBeforeServiceList($row,'N');
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
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
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
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //跨区终止
    public function performanceendList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.*,b.description as type_desc")
        ->from("swoper{$suffix}.swo_service a")
        ->leftJoin("swoper{$suffix}.swo_customer_type b","a.cust_type=b.id")
        ->where("a.status='T' and b.sales_rate=1 and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and a.othersalesman_id={$this->employee_id}")
        ->order("a.cust_type asc,a.cust_type_name asc")->queryAll();
        if($rows){
            foreach ($rows as &$row){
                $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                $row['all_number'] = is_numeric($row['all_number'])?floatval($row['all_number']):0;
                $row['surplus'] = is_numeric($row['surplus'])?floatval($row['surplus']):0;
                $row['amt_money'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                //变动金额 = (总金额/服务总次数) * 剩余次数
                $row['amt_money']=empty($row['all_number'])?0:$row['amt_money']/$row['all_number']*$row['surplus'];
                $row['amt_money']=round($row['amt_money'],2)*-1;
                $row['history']=SellComputeList::getBeforeServiceList($row,"N");
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
                    $html.="<td data-id='{$row['history']['id']}'>".floatval($row["history"]["royalty"])."</td>";
                }else{
                    $royalty=empty($row['royalty'])?0.01:$row['royalty'];
                    $html.="<td>".TbHtml::numberField("royalty[{$row['id']}]",$royalty)."</td>";
                }
                $html.="<td>".$row['royalty']."</td>";
                $row['commission'] = is_numeric($row['commission'])?round($row['commission']*$row['royalty'],2):$row['commission'];
                $html.="<td>".$row['commission']."</td>";
                $html.="</tr>";
            }
        }
        return $html;
    }

    //产品生意额
    public function productList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
        ->select("a.id,a.commission,a.money,a.qty,f.description,f.sales_products,b.log_dt,b.company_name")
        ->from("swoper$suffix.swo_logistic_dtl a")
        ->leftJoin("swoper$suffix.swo_logistic b","a.log_id=b.id")
        ->leftJoin("swoper{$suffix}.swo_task f","a.task=f.id")
        ->where("b.log_dt between '{$this->startDate}' and '{$this->endDate}' and b.salesman='{$this->staff}' and b.city ='{$this->city}' and a.qty>0 and a.money>0")
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
            }
        }
        return $html;
    }

	public function setUpdateBool(){
        $this->startDate = date("Y-m-d",strtotime("{$this->year}/{$this->month}/01"));
        $this->endDate = date("Y-m-d",strtotime("{$this->startDate} + 1months - 1day"));
        $ageTime = date("Y-m-01");
        $ageTime = date("Y-m-d",strtotime("$ageTime - 1 months"));
        if($ageTime<=$this->startDate) { //只能修改上个月及以后的数据
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
    private function newSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $rows = $this->newList();
        $service_reward=$this->getServiceReward();//服务奖励点
        $point=$this->getPoint();//销售提成激励点
        $new_money=0;//新增业绩
        //更改业绩
        $edit_money=key_exists("edit_money",$this->dtl_list)?$this->dtl_list["edit_money"]:0;

        $new_amount=0;//新增生意提成
        $new_calc=0;//新增提成比例
        $updateRows = array();
        $span_num=0;//参与计算的单数
        if($rows){
            foreach ($rows as $row){ //需要先计算新增的总金额
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                    $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                    $row['amt_sum'] = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                    $updateRows[]=$row;
                    $new_money+=$row['amt_sum'];
                    $span_num++;
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
            if($new_money<$this->span_list['sums']&&$span_num<$this->span_list['sums']){
                $this->span_rate=0;//跨区提成比例为0
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
                    "commission"=>$updateRow['amt_sum'],
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$updateRow["id"]));
            }
        }
        //记录主表修改
        Yii::app()->db->createCommand()->update("acc_service_comm_hdr",array(
            "luu"=>$uid,
            "lud"=>date("Y-m-d H:i:s")
        ),"id=:id",array(":id"=>$this->id));
        //修改主表的提成数据
        $this->saveDtlList(array(
            "new_money"=>$new_money,
            "new_amount"=>$new_amount,
            "service_reward"=>$service_reward,
            "point"=>$point,
            "new_calc"=>$new_calc
        ));
    }

    //更改生意额(保存)
    private function editSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
                    $updateRow['amt_money'] *=$this->span_rate;
                }
                $updateRow['amt_money'] = round($updateRow['amt_money'],2);
                $updateRoyalty=key_exists("history",$updateRow)?$updateRow["history"]["royalty"]:$royalty;
                $commission=$updateRow['amt_money']*$updateRoyalty;
                $commission = round($commission,2);
                $edit_amount+=$commission;//更改的提成金额
                //计算
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                    "royalty"=>$updateRoyalty,
                    "commission"=>$updateRow['amt_money'],
                    "luu"=>$uid
                ),"id=:id",array(":id"=>$updateRow["id"]));
            }
        }
        $this->saveDtlList(array(
            "edit_money"=>$edit_money,
            "edit_amount"=>$edit_amount,
            "new_calc"=>$new_calc
        ));

    }

    //终止生意额(保存)
    private function endSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
                        $row['amt_money'] *= $this->span_rate;
                    }
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $end_amount+=$commission;//更改的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$row["history"]["royalty"],
                        "commission"=>$row['amt_money'],
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
    }

    //跨区新增生意额(保存)
    private function performanceSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    $row['amt_paid'] = is_numeric($row['amt_paid'])?floatval($row['amt_paid']):0;
                    $row['ctrt_period'] = is_numeric($row['ctrt_period'])?floatval($row['ctrt_period']):0;
                    $amt_sum = $row['paid_type']=="M"?$row['amt_paid']*$row['ctrt_period']:$row['amt_paid'];
                    $amt_sum*=$this->span_other_rate;//跨区
                    $commission =$amt_sum*$royalty;
                    $commission = round($commission,2);
                    $out_money+=$amt_sum;//跨区业绩
                    $performance_amount+=$commission;//跨区新增的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$royalty,
                        "other_commission"=>$amt_sum,
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
            "performance_amount"=>$performance_amount,
            "out_money"=>$out_money
        ));
    }

    //跨区更改生意额(保存)
    private function performanceeditSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
                    if(key_exists("history",$row)){ //金额变少了（需要历史提成）
                        if(empty($row["history"])){//手动修改历史提成
                            $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                        }
                        $thisRoyalty=$row["history"]["royalty"];
                    }
                    $row['amt_money'] =$row['amt_money']*$this->span_other_rate;//跨区
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$thisRoyalty;
                    $commission = round($commission,2);
                    $performanceedit_amount+=$commission;//跨区更改提成金额
                    $performanceedit_money+=$row['amt_money']>0?$row['amt_money']:0;//跨区更改业绩
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$thisRoyalty,
                        "other_commission"=>$row['amt_money'],
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
            "performanceedit_amount"=>$performanceedit_amount,
            "performanceedit_money"=>$performanceedit_money
        ));
    }

    //跨区终止生意额(保存)
    private function performanceendSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $rows = $this->performanceendList();
        $performanceend_amount=0;//跨区终止生意提成
        $royaltyList = key_exists("royalty",$_POST)?$_POST["royalty"]:array();//需要手动修改的提成
        if(!empty($rows)){
            foreach ($rows as $row){
                if(key_exists($row["id"],$data)){ //该服务需要参与提成计算
                    if(empty($row["history"])){//手动修改历史提成
                        $row["history"]["royalty"]=key_exists($row["id"],$royaltyList)?floatval($royaltyList[$row["id"]]):0.01;
                    }
                    $row['amt_money'] *= $this->span_other_rate;//跨区服务
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $performanceend_amount+=$commission;//跨区终止生意提成
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royaltys"=>$row["history"]["royalty"],
                        "other_commission"=>$row['amt_money'],
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
    }

    //续约生意额(保存)
    private function renewalSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
                        "commission"=>$row['amt_money'],
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
    private function renewalendSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
                        $row['amt_money'] *= $this->span_rate;
                    }
                    $row['amt_money'] = round($row['amt_money'],2);
                    $commission =$row['amt_money']*$row["history"]["royalty"];
                    $commission = round($commission,2);
                    $renewalend_amount+=$commission;//更改的提成金额
                    //计算
                    Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                        "royalty"=>$row["history"]["royalty"],
                        "commission"=>$row['amt_money'],
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
    }

    //产品生意额(保存)
    private function productSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
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
        $uid = Yii::app()->user->id;
        //修改副表
        $list = array();
        foreach ($data as $key=>$value){
            if(key_exists($key,$this->dtl_list)){
                if(floatval($this->dtl_list[$key])!=$value){
                    $list[$key] = $value; //有数据变动，需要修改
                }
            }
        }
        if(!empty($list)){
            $list['luu']=$uid;
            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",$list,"hdr_id=:id",array(":id"=>$this->id));

            if(key_exists('new_calc',$list)||key_exists('point',$list)||key_exists('service_reward',$list)){
                //如果提成比例变动，需要刷新数据
                $this->resetNewAndEditSave($data);
            }
            if(key_exists('point',$list)||key_exists('new_money',$list)||key_exists('edit_money',$list)){
                //如果销售提成激励点、新增业绩、更改新增业绩变动，需要刷新数据
                $this->resetProductSave($data);

                $this->resetInstallSave($data);//刷新装机金额
            }
        }
    }

    //如果提成比例变动，需要刷新数据(非產品的所有服務)
    private function resetNewAndEditSave($data){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $new_calc =key_exists('new_calc',$data)?$data['new_calc']:$this->dtl_list['new_calc'];
        $point =key_exists('point',$data)?$data['point']:$this->dtl_list['point'];
        $service_reward =key_exists('service_reward',$data)?$data['service_reward']:$this->dtl_list['service_reward'];
        $royalty = $new_calc+$point+$service_reward;
        //修改新增及跨区新增
        $newRows = Yii::app()->db->createCommand()
            ->select("a.id,a.other_commission,a.royaltys,a.commission,a.royalty,a.othersalesman_id")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.status = 'N' and a.first_dt between '{$this->startDate}' and '{$this->endDate}' and 
            ((a.commission+0>0 and a.salesman_id={$this->employee_id}) or 
            (a.other_commission+0>0 and a.othersalesman_id={$this->employee_id}))")->queryAll();
        if($newRows){
            $new_amount = 0;//新增的提成
            $performance_amount = 0;//跨区新增的提成
            foreach ($newRows as $row){
                $updateArr = array("luu"=>$uid);
                if($row["othersalesman_id"]==$this->employee_id){ //跨区
                    $row["other_commission"] = is_numeric($row["other_commission"])?floatval($row["other_commission"]):0;
                    $other_commission = $row["other_commission"]*$royalty;
                    $other_commission = round($other_commission,2);
                    $performance_amount+=$other_commission;
                    $updateArr["royaltys"]=$royalty;
                }else{
                    $row["commission"] = is_numeric($row["commission"])?floatval($row["commission"]):0;
                    $commission = $row["commission"]*$royalty;
                    $commission = round($commission,2);
                    $new_amount+=$commission;
                    $updateArr["royalty"]=$royalty;
                }
                //刷新數據
                Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",$updateArr,"id=:id",array(":id"=>$row["id"]));
            }
            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
                "new_amount"=>$new_amount,
                "performance_amount"=>$performance_amount
            ),"hdr_id=:id",array(":id"=>$this->id));
        }
        //修改更改及跨区更改
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.other_commission,a.royaltys,a.commission,a.royalty,a.othersalesman_id")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.status ='A' and a.status_dt between '{$this->startDate}' and '{$this->endDate}' and 
            ((a.commission is not null and a.salesman_id={$this->employee_id}) or 
            (a.other_commission is not null and a.othersalesman_id={$this->employee_id}))")->queryAll();
        if($rows){
            $edit_amount = 0;//更改的提成
            $performanceedit_amount = 0;//跨区更改的提成
            foreach ($rows as $row){
                if($row["othersalesman_id"]==$this->employee_id){ //跨区
                    $other_commission=0;
                    $row["royaltys"] = is_numeric($row["royaltys"])?floatval($row["royaltys"]):0;
                    $row["other_commission"] = is_numeric($row["other_commission"])?floatval($row["other_commission"]):0;
                    if($row["other_commission"]>0){ //更改增加
                        $other_commission = $row["other_commission"]*$royalty;
                        $other_commission = round($other_commission,2);//保留两位小数点
                        //刷新數據
                        Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                            "royaltys"=>$royalty,
                            "luu"=>$uid
                        ),"id=:id",array(":id"=>$row["id"]));
                    }
                    $performanceedit_amount+=$other_commission;
                }else{
                    $commission=0;
                    $row["commission"] = is_numeric($row["commission"])?floatval($row["commission"]):0;
                    $row["royalty"] = is_numeric($row["royalty"])?floatval($row["royalty"]):0;
                    if($row["commission"]>0){ //更改增加
                        $commission = $row["commission"]*$royalty;
                        $commission = round($commission,2);//保留两位小数点
                        //刷新數據
                        Yii::app()->db->createCommand()->update("swoper{$suffix}.swo_service",array(
                            "royalty"=>$royalty,
                            "luu"=>$uid
                        ),"id=:id",array(":id"=>$row["id"]));
                    }
                    $edit_amount+=$commission;
                }
            }
            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
                "edit_amount"=>$edit_amount,
                "performanceedit_amount"=>$performanceedit_amount
            ),"hdr_id=:id",array(":id"=>$this->id));
        }
    }

    //如果销售提成激励点、新增业绩、更改新增业绩变动，需要刷新产品提成
    private function resetProductSave($data){
        $point =key_exists('point',$data)?$data['point']:$this->dtl_list['point'];
        $new_money =key_exists('new_money',$data)?$data['new_money']:$this->dtl_list['new_money'];
        $edit_money =key_exists('edit_money',$data)?$data['edit_money']:$this->dtl_list['edit_money'];

        $suffix = Yii::app()->params['envSuffix'];
        $product_amount=0;//产品提成
        $money = $edit_money+$new_money;
        $computeRate=array();//保存已计算的产品提成比例

        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.commission,a.money,a.qty,f.description,f.sales_products,b.log_dt,b.company_name")
            ->from("swoper$suffix.swo_logistic_dtl a")
            ->leftJoin("swoper$suffix.swo_logistic b","a.log_id=b.id")
            ->leftJoin("swoper{$suffix}.swo_task f","a.task=f.id")
            ->where("a.commission=1 and b.log_dt between '{$this->startDate}' and '{$this->endDate}' and b.salesman='{$this->staff}' and b.city ='{$this->city}' and a.qty>0 and a.money>0")
            ->order("f.sales_products asc,b.log_dt desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $type = $row['sales_products'];
                $row['qty'] = is_numeric($row['qty'])?floatval($row['qty']):0;
                $row['money'] = is_numeric($row['money'])?floatval($row['money']):0;
                $amt_sum = $row['qty']*$row['money'];
                if(!key_exists($type,$computeRate)){
                    $computeRate[$type] = SellComputeList::getProductRate($money,$this->startDate,$this->city,$type);
                }
                //提成点 = 产品提成点 + 销售提成激励点
                $thisRoyalty = $computeRate[$type]+$point;
                $commission =$amt_sum*$thisRoyalty;
                $commission = round($commission,2);
                $product_amount+=$commission;//产品提成
            }

            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
                "product_amount"=>$product_amount
            ),"hdr_id=:id",array(":id"=>$this->id));
        }
    }

    //刷新装机金额
    private function resetInstallSave($data){
        $installRate = $this->getPaperRateAndPoint($data);
        $suffix = Yii::app()->params['envSuffix'];
        $install_amount=0;//装机提成
        $install_money=0;//装机业绩

        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.amt_install")
            ->from("swoper{$suffix}.swo_service a")
            ->where("a.commission is not null and (
            (a.status='N' and a.first_dt between '{$this->startDate}' and '{$this->endDate}') or 
            (a.status='A' and a.status_dt between '{$this->startDate}' and '{$this->endDate}')
            ) and a.salesman_id={$this->employee_id} and a.amt_install+0>0")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $amt_sum = is_numeric($row['amt_install'])?floatval($row['amt_install']):0;

                $commission =$amt_sum*$installRate;
                $commission = round($commission,2);
                $install_amount+=$commission;//装机提成
                $install_money+=$amt_sum;//装机业绩
            }

            Yii::app()->db->createCommand()->update("acc_service_comm_dtl",array(
                "install_amount"=>$install_amount,
                "install_money"=>$install_money
            ),"hdr_id=:id",array(":id"=>$this->id));
        }
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
                    ->where("username=:id and visit_dt='{$this->startDate}'",array(":id"=>$staffRow["user_id"]))
                    ->queryRow();
                if(!$visitRow){
                    $salesBool = false;//當月一號沒有銷售拜訪
                }
            }
            if($salesBool){
                $integralRow = Yii::app()->db->createCommand()->select("id,point")
                    ->from("sales$suffix.sal_integral")
                    ->where("year={$this->year} and month={$this->month} and username=:id",array(":id"=>$staffRow["user_id"]))
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
        $logisticSum = Yii::app()->db->createCommand()->select("sum(a.qty)")
            ->from("swoper$suffix.swo_logistic_dtl a")
            ->leftJoin("swoper$suffix.swo_logistic b","a.log_id = b.id")
            ->leftJoin("swoper$suffix.swo_task c","a.task = c.id")
            ->where("c.task_type='FLOOR' and b.salesman='{$this->staff}' and money>0 and b.log_dt between '{$this->startDate}' and '{$this->endDate}'")
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
        $dateSql = " and a.first_dt between '{$this->startDate}' and '{$this->endDate}'";
        $dateIDSql = " and a.status_dt between '{$this->startDate}' and '{$this->endDate}'";
        $serviceMoney =Yii::app()->db->createCommand()
            ->select("sum(CASE WHEN a.paid_type = 'M' THEN a.amt_paid*a.ctrt_period ELSE a.amt_paid END)")
            ->from("swoper$suffix.swo_service a")
            ->leftJoin("swoper$suffix.swo_customer_type_twoname b","a.cust_type_name = b.id")
            ->leftJoin("swoper$suffix.swo_customer_type c","a.cust_type = c.id")
            ->where("b.bring = 1 and a.status = 'N' and a.salesman_id='{$this->employee_id}' $dateSql")
            ->queryScalar();
        $serviceMoney=$serviceMoney?floatval($serviceMoney):0;
        $serviceIDMoney =Yii::app()->db->createCommand()
            ->select("sum(a.amt_money)")
            ->from("swoper$suffix.swo_serviceid a")
            ->where("a.status = 'N' and a.salesman_id='{$this->employee_id}' $dateIDSql")
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

    //非餐饮提成点数
    private function getRenewalRateForB($service){
        $minMoney=1000;//最低的月金额
        if(in_array($this->city,array("GZ","SH","SZ","BJ"))){ //一线城市
            $minMoney=2000;
        }
        if($service["month_money"]>=$minMoney){
            return 0.01;
        }
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
}