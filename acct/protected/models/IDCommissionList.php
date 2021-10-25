<?php

class IDCommissionList extends CListPageModel
{
    public $id;
    public $year;
    public $month;
    public $city;
    public $type=0;//0:查询  1：计算
    public $noOfItem=0;

    public function rules()
    {
        return array(
            array('year, month, city, attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'city_name'=>Yii::t('app','city'),
			'first_dt'=>Yii::t('app','first_dt'),
			'sign_dt'=>Yii::t('app','sign_dt'),
			'company_name'=>Yii::t('app','company_name'),
			'type_desc'=>Yii::t('app','type_desc'),
            'service'=>Yii::t('app','service'),
            'amt_paid'=>Yii::t('app','amt_paid'),
            'amt_install'=>Yii::t('app','amt_install'),
            'employee_code'=>Yii::t('app','employee_code'),
            'employee_name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'user_name'=>Yii::t('app','user_name'),
            'sum_amount'=>Yii::t('app','comm_total_amount'),
            'log_dt'=>Yii::t('app','Log Dt'),
            'description'=>Yii::t('app','Description'),
            'qty'=>Yii::t('app','Qty'),

            'money'=>Yii::t('app','Qty Money'),
            'moneys'=>Yii::t('app','Money'),
            'ctrt_period'=>Yii::t('app','Ctrt_period'),
            'rate_num'=>Yii::t('commission','rate num'),
            'all_money'=>Yii::t('app','comm_total_amount'),
            'comm_money'=>Yii::t('commission','commission money'),
            'service_no'=>Yii::t('commission','service no'),
            'cust_type_name'=>Yii::t('commission','service type'),
            'back_date'=>Yii::t('commission','back date'),
            'back_money'=>Yii::t('commission','back money'),
            'back_ratio'=>Yii::t('service','ratio'),
            'commission'=>Yii::t('commission','commission rate'),
		);
	}

	public function YearAndMonthMinus(){
	    $this->month--;
	    if($this->month<=0){
            $this->month = 12;
            $this->year--;
        }
    }

    //銷售列表
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city = $this->city;
        $year = $this->year;
        $month = $this->month<10?"0{$this->month}":$this->month;
        $startDate = "{$year}/{$month}/01";
        $endDate = "{$year}/{$month}/31";
        $staffSql = "0";
        $staffList = Yii::app()->db->createCommand()
            ->select("b.salesman_id")
            ->from("swoper{$suffix}.swo_serviceid_info a")
            ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
            ->where("b.city = '{$city}' and a.back_date between '$startDate' and '$endDate'")
            ->group("b.salesman_id")
            ->queryAll();
        if($staffList){
            foreach ($staffList as $staff){
                $staffSql.=",".$staff["salesman_id"];
            }
        }
        $clause = " and c.dept_class<>'Technician' and a.id in ($staffSql) ";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'employee_code':
                    $clause .= General::getSqlConditionClause('a.code',$svalue);
                    break;
                case 'employee_name':
                    $clause .= General::getSqlConditionClause('a.name',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('e.name',$svalue);
                    break;

            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= $this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order ="a.id desc";
        }
        $sql = Yii::app()->db->createCommand()
            ->select("a.id as employee_id,a.code,a.name,e.name as city_name,c.name as position_name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr$suffix.hr_dept c","a.position=c.id")
            ->leftJoin("security$suffix.sec_city e","a.city=e.code")
            ->where("a.city = '{$city}' $clause")
            ->order($order)
            ->getText();
		$this->totalRow = Yii::app()->db->createCommand()
            ->select("count(a.id)")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr$suffix.hr_dept c","a.position=c.id")
            ->leftJoin("security$suffix.sec_city e","a.city=e.code")
            ->where("a.city = '{$city}' $clause")
            ->queryScalar();
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
			    $arr = Yii::app()->db->createCommand()->select("id,sum_amount")
                    ->from("acc_serviceid_comm_hdr")
                    ->where("employee_id={$record['employee_id']} and year_no={$this->year} and month_no={$this->month}")->queryRow();
			    $this->attr[] = array(
					'id'=>$arr?$arr["id"]:0,
					'employee_id'=>$record['employee_id'],
					'employee_code'=>$record['code'],
					'employee_name'=>$record['name'],
					'description'=>$record['position_name'],
					'city'=>$record['city_name'],
					'sum_amount'=>$arr?floatval($arr["sum_amount"]):"-",
                    'time'=>"$year/$month",
                    'year'=>$this->year,
                    'month'=>$this->month,
				);
			}
		}
		$session = Yii::app()->session;
		$session['IDCommission_01'] = $this->getCriteria();
		return true;
	}

    //新增列表
    public function newDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.year_no,a.month_no,a.employee_id,a.sum_amount,b.code,b.name,b.city,b.group_type")
            ->from("acc_serviceid_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.id=:id and b.city in ($city)",array(":id"=>$index))
            ->queryRow();
        $this->id = $index;
        if($row){
            $this->year = $row["year_no"];
            $this->month = $row["month_no"];
            $this->city = $row["city"];
            $month = $this->month<10?"0{$this->month}":$this->month;
            $startDate = "{$this->year}/{$month}/01";
            $endDate = "{$this->year}/{$month}/31";
            $order = "";
            if (!empty($this->orderField)) {
                $order .= $this->orderField." ";
                if ($this->orderType=='D') $order .= "desc ";
            }else{
                $order ="id desc";
            }
            $records = Yii::app()->db->createCommand()
                ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
                ->from("swoper{$suffix}.swo_serviceid_info a")
                ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
                ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
                ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
                ->where("b.salesman_id=:id and b.status = 'N' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$row["employee_id"]))
                ->order($order)
                ->queryAll();
            $this->attr = array();
            if ($records) {
                foreach ($records as $k => $record) {
                    $temp["id"] = $record["id"];
                    $temp["service_no"] = $record["service_no"];
                    $temp["city"] = General::getCityName($row["city"]);
                    $temp["back_date"] = $record["back_date"];//回款日期
                    $temp["company"] = $record["code"].$record["name"];//客户名称
                    $temp["cust_type_name"] = $record["cust_type_name"];//客户类别
                    $temp["ctrt_period"] = $record["ctrt_period"];//合同年限
                    $temp["back_money"] = floatval($record["back_money"]);//回款金额
                    $temp["back_ratio"] = $record["back_ratio"];//百分比
                    $temp["commission"] = $record["commission"];//是否已經計算 1：已计算
                    $temp["commission_name"] = $record["commission"]==1?Yii::t("misc","Yes"):Yii::t("misc","No");//是否已經計算 1：已计算
                    $this->resetTemp($temp,$record);
                    $this->attr[] = $temp;
                }
            }
            $session = Yii::app()->session;
            $session['IDCommission_new'] = $this->getCriteria();
            return true;
        }
        return false;
    }

    protected function resetTemp(&$temp,$record){
	    if($record["commission"]==1){ //已经计算过
            $temp["comm_money"] = floatval($record["comm_money"]);//實際計算金額
            $temp["rate_num"] = floatval($record["rate_num"]);//提成比例
            $temp["all_money"] = $temp["rate_num"]*$temp["comm_money"];//提成金额
        }else{ //未计算
            $temp["comm_money"] = floatval($record["back_money"])*0.01*$record["back_ratio"];
            $temp["rate_num"] = self::getRateNum($record["cust_type"],$record["ctrt_period"],$this->city);//提成比例
            $temp["all_money"] = $temp["rate_num"]*$temp["comm_money"];//提成金额
        }
    }

    //获取提成比例
    public static function getRateNum($type_id,$month,$city){
        $row = Yii::app()->db->createCommand()
            ->select("a.rate")
            ->from("acc_serviceid_rate_dtl a")
            ->leftJoin("acc_serviceid_rate_hdr b","a.hdr_id=b.id")
            ->where("a.type_id=:type_id and ((a.operator='LE' and a.month_num>=:month)or(a.operator='GT' and a.month_num<:month)) and (b.only_num=1 or (b.only_num=0 and b.city='$city'))",array(":type_id"=>$type_id,":month"=>$month))
            ->order("b.only_num asc,b.id desc,a.operator desc,a.month_num asc")->queryRow();
        if($row){
            return floatval($row["rate"]);
        }else{//提成比例默认为0.15
            return 0.15;
        }
    }

    //更改列表
    public function amendDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.year_no,a.month_no,a.employee_id,a.sum_amount,b.code,b.name,b.city,b.group_type")
            ->from("acc_serviceid_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.id=:id and b.city in ($city)",array(":id"=>$index))
            ->queryRow();
        $this->id = $index;
        if($row){
            $this->year = $row["year_no"];
            $this->month = $row["month_no"];
            $this->city = $row["city"];
            $month = $this->month<10?"0{$this->month}":$this->month;
            $startDate = "{$this->year}/{$month}/01";
            $endDate = "{$this->year}/{$month}/31";
            $order = "";
            if (!empty($this->orderField)) {
                $order .= $this->orderField." ";
                if ($this->orderType=='D') $order .= "desc ";
            }else{
                $order ="id desc";
            }
            $records = Yii::app()->db->createCommand()
                ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
                ->from("swoper{$suffix}.swo_serviceid_info a")
                ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
                ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
                ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
                ->where("b.salesman_id=:id and b.status='A' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$row["employee_id"]))
                ->order($order)
                ->queryAll();
            $this->attr = array();
            if ($records) {
                foreach ($records as $k => $record) {
                    $temp["id"] = $record["id"];
                    $temp["service_no"] = $record["service_no"];
                    $temp["city"] = General::getCityName($row["city"]);
                    $temp["back_date"] = $record["back_date"];//回款日期
                    $temp["company"] = $record["code"].$record["name"];//客户名称
                    $temp["cust_type_name"] = $record["cust_type_name"];//客户类别
                    $temp["ctrt_period"] = $record["ctrt_period"];//合同年限
                    $temp["back_money"] = floatval($record["back_money"]);//回款金额
                    $temp["back_ratio"] = $record["back_ratio"];//百分比
                    $temp["commission"] = $record["commission"];//是否已經計算 1：已计算
                    $temp["commission_name"] = $record["commission"]==1?Yii::t("misc","Yes"):Yii::t("misc","No");//是否已經計算 1：已计算
                    $this->resetTemp($temp,$record);
                    $this->attr[] = $temp;
                }
            }
            $session = Yii::app()->session;
            $session['IDCommission_new'] = $this->getCriteria();
            return true;
        }
        return false;
    }

    //续约列表
    public function renewDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()
            ->select("a.id,a.year_no,a.month_no,a.employee_id,a.sum_amount,b.code,b.name,b.city,b.group_type")
            ->from("acc_serviceid_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.id=:id and b.city in ($city)",array(":id"=>$index))
            ->queryRow();
        $this->id = $index;
        if($row){
            $this->year = $row["year_no"];
            $this->month = $row["month_no"];
            $this->city = $row["city"];
            $month = $this->month<10?"0{$this->month}":$this->month;
            $startDate = "{$this->year}/{$month}/01";
            $endDate = "{$this->year}/{$month}/31";
            $order = "";
            if (!empty($this->orderField)) {
                $order .= $this->orderField." ";
                if ($this->orderType=='D') $order .= "desc ";
            }else{
                $order ="id desc";
            }
            $records = Yii::app()->db->createCommand()
                ->select("a.*,b.status,b.cust_type_name as cust_type,g.cust_type_name,b.service_no,b.ctrt_period,f.code,f.name")
                ->from("swoper{$suffix}.swo_serviceid_info a")
                ->leftJoin("swoper{$suffix}.swo_serviceid b","a.serviceID_id=b.id")
                ->leftJoin("swoper{$suffix}.swo_company f","b.company_id=f.id")
                ->leftJoin("swoper{$suffix}.swo_customer_type_info g","b.cust_type_name=g.id")
                ->where("b.salesman_id=:id and b.status='C' and a.back_date between '$startDate' and '$endDate'",array(":id"=>$row["employee_id"]))
                ->order($order)
                ->queryAll();
            $this->attr = array();
            if ($records) {
                foreach ($records as $k => $record) {
                    $temp["id"] = $record["id"];
                    $temp["service_no"] = $record["service_no"];
                    $temp["city"] = General::getCityName($row["city"]);
                    $temp["back_date"] = $record["back_date"];//回款日期
                    $temp["company"] = $record["code"].$record["name"];//客户名称
                    $temp["cust_type_name"] = $record["cust_type_name"];//客户类别
                    $temp["ctrt_period"] = $record["ctrt_period"];//合同年限
                    $temp["back_money"] = floatval($record["back_money"]);//回款金额
                    $temp["back_ratio"] = $record["back_ratio"];//百分比
                    $temp["commission"] = $record["commission"];//是否已經計算 1：已计算
                    $temp["commission_name"] = $record["commission"]==1?Yii::t("misc","Yes"):Yii::t("misc","No");//是否已經計算 1：已计算
                    $this->resetTemp($temp,$record);
                    $this->attr[] = $temp;
                }
            }
            $session = Yii::app()->session;
            $session['IDCommission_new'] = $this->getCriteria();
            return true;
        }
        return false;
    }

    //下載excel
    public function retrieveXiaZai($index,$view){
        $pageNum=1;
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/salecommisonid.xlsx';
        $objPHPExcel = $objReader->load($path);
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('ID销售提成明细报表-'.$view['city']);
        $objPHPExcel->getActiveSheet()->setCellValue('A1','ID销售提成明细报表 - '.$view['employee_name']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A2','提成月份 : '.$view['year'].'/'.$view['month']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A3','组别 : '.$view->getGroupType($view['group_type'],true)) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A5','新增生意提成：'.$view['new_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('C5','实际新增提成金额：'.$view['new_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A7','更改生意提成：'.$view['edit_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('C7','实际更改提成金额：'.$view['edit_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A9','续约生意提成：'.$view['renewal_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('C9','实际续约提成金额：'.$view['renewal_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A11','总额：'.$view['sum_amount']) ;

        $objPHPExcel->getActiveSheet()->getStyle('A13:K13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A13:K13')->getFill()->getStartColor()->setARGB('99FFFF');

        $this->newDataByPage($pageNum,$index);
        $i=13;
        $funList = array(
            array("fun"=>"newDataByPage","name"=>"类别 : 新生意额"),
            array("fun"=>"amendDataByPage","name"=>"类别 : 更改生意额"),
            array("fun"=>"renewDataByPage","name"=>"类别 : 续约生意额"),
        );
        foreach ($funList as $list){
            $i++;
            $this->attr = array();
            $funStr = $list["fun"];
            $this->$funStr($pageNum,$index);
            $objPHPExcel->getActiveSheet()->setCellValue("A$i",$list["name"]) ;
            $objPHPExcel->getActiveSheet()->mergeCells("A{$i}:K{$i}");
            $objPHPExcel->getActiveSheet()->getStyle("A$i")->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle("A$i")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle("A$i")->getFill()->getStartColor()->setARGB('99FFFF');

            if(!empty($this->attr)){
                foreach ($this->attr as $row){
                    $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue("A$i",$row['service_no']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("B$i",$row['back_date']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("C$i",$row['company']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("D$i",$row['cust_type_name']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("E$i",$row['ctrt_period']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("F$i",$row['back_money']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("G$i",$row['back_ratio']."%") ;
                    $objPHPExcel->getActiveSheet()->setCellValue("H$i",$row['comm_money']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("I$i",$row['rate_num']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("J$i",$row['all_money']) ;
                    $objPHPExcel->getActiveSheet()->setCellValue("K$i",$row['commission_name']) ;
                }
            }
            $i+=2;
        }

        $time=time();
        $str="salecommsionid_".$time.".xlsx";
        header("Content-Type:application/vnd.ms-excel");
        header('Content-Disposition:attachment;filename="'.$str.'"');
         header("Pragma: no-cache");
        header("Expires: 0");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_end_clean();
        $objWriter->save('php://output');

        spl_autoload_register(array('YiiBase','autoload'));

    }

}
