<?php

class PayrollList extends CListPageModel
{
    private $excelColumn=array();

	public function attributeLabels()
	{
		return array(	
			'year_no'=>Yii::t('report','Year'),
			'month_no'=>Yii::t('report','Month'),
			'city_name'=>Yii::t('misc','City'),
			'wfstatusdesc'=>Yii::t('misc','Status'),
			'file1countdoc'=>Yii::t('trans','Files'),
            'amt_total'=>Yii::t('trans','Total'),
		);
	}

    public function searchColumns() {
        $search = array(
            'year_no'=>"a.year_no",
            'month_no'=>'a.month_no',
        );
        if (!Yii::app()->user->isSingleCity()) $search['city_name'] = 'b.name';
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$version = Yii::app()->params['version'];
		$cityarg = ($version=='intl' ? 'a.city,' : '');
		$sql1 = "select a.*, b.name as city_name , 
					docman$suffix.countdoc('PAYFILE1',a.id) as file1countdoc,
					(select case workflow$suffix.RequestStatus($cityarg 'PAYROLL',a.id,a.lcd)
							when '' then '4DF' 
							when 'PB' then '1PB' 
							when 'PA' then '2PA' 
							when 'PS' then '0PS' 
							when 'ED' then '3ED' 
					end) as wfstatus,f.data_value as amt_total,
					workflow$suffix.RequestStatusDesc($cityarg 'PAYROLL',a.id,a.lcd) as wfstatusdesc
				from acc_payroll_file_hdr a 
				LEFT join security$suffix.sec_city b on a.city=b.code 
				LEFT join acc_payroll_file_dtl f on f.hdr_id=a.id and f.data_field='amt_total'
				where a.city in ($citylist)
			";
		$sql2 = "select count(a.id)
				from acc_payroll_file_hdr a 
				LEFT join security$suffix.sec_city b on a.city=b.code 
				LEFT join acc_payroll_file_dtl f on f.hdr_id=a.id and f.data_field='amt_total'
				where a.city in ($citylist)
			";
		$clause = "";
        if (!empty($this->searchField) && (!empty($this->searchValue) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause = $this->buildSQLCriteria();
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $columns = $this->searchColumns();
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
            }
        }

//		$clause .= $this->getDateRangeCondition('a.lcd');
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		} else
			$order = " order by a.year_no desc, a.month_no desc, a.city";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$wfstatus = (empty($record['wfstatus'])?'0DF':$record['wfstatus']);
					$this->attr[] = array(
						'id'=>$record['id'],
						'year_no'=>$record['year_no'],
						'month_no'=>$record['month_no'],
						'city'=>$record['city'],
                        'amt_total'=>$wfstatus=="3ED"?$record['amt_total']:"",
						'city_name'=>$record['city_name'],
						'wfstatusdesc'=>(empty($record['wfstatusdesc'])?Yii::t('misc','Draft'):$record['wfstatusdesc']) ,
						'wfstatus'=> $wfstatus,
						'file1countdoc'=>$record['file1countdoc'],
					);
			}
		}
		$session = Yii::app()->session;
        $session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

    public static function getYearList(){
	    $year = date("Y");
	    $list = array();
	    for ($i=2020;$i<=$year;$i++){
            $list[$i] = "".$i.Yii::t("trans"," Year");
        }
        return $list;
    }

    public static function getMonthList(){
	    $list = array();
	    for ($i=1;$i<=12;$i++){
            $list[$i] = "".$i.Yii::t("trans"," Month");
        }
        return $list;
    }

    public static function getCityList(){
        $citylist = Yii::app()->user->city_allow();

        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from security$suffix.sec_city where code in($citylist)";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
	    $list = array();
	    if($rows){
	        foreach ($rows as $row){
	            $list[$row["code"]] = $row["name"];
            }
        }
        return $list;
    }

    private function getTopArr($post){
        $exprList = array();
        $bgList = array("#44546A","#C65911");
        $bg_i=0;
        $date = $post["startDate"];
        while ($date<=$post["endDate"]){
            $this->excelColumn[]=$date;
            $list = explode("-",$date);
            $year = $list[0];
            $month = intval($list[1]);
            if(!key_exists($year,$exprList)){
                $bgKey = $bg_i%2;
                $exprList[$year]=array(
                    "name"=>$year.Yii::t("trans"," Year"),
                    "background"=>$bgList[$bgKey],
                    "color"=>"#ffffff",
                    "colspan"=>array()
                );
                $bg_i++;
            }
            $exprList[$year]["colspan"][]=array("name"=>"".$month.Yii::t("trans"," Month"));

            $date = date("Y-m",strtotime("{$date}-01 + 1 months"));//#
        }

        $topList=array(
            array("name"=>Yii::t("trans","Serial number"),"rowspan"=>2,"background"=>"#161616","color"=>"#ffffff"),//序号
            array("name"=>Yii::t("trans","Area"),"rowspan"=>2,"background"=>"#161616","color"=>"#ffffff"),//区域
            array("name"=>Yii::t("trans","City"),"rowspan"=>2,"background"=>"#161616","color"=>"#ffffff"),//城市
        );
        $topList = array_merge($topList,$exprList);

        return $topList;
    }

    private function getPostData(){
        $city = key_exists("city",$_POST)?$_POST["city"]:Yii::app()->user->city();
        $startYear = key_exists("year_start",$_POST)?$_POST["year_start"]:date("Y");
        $endYear = key_exists("year_end",$_POST)?$_POST["year_end"]:date("Y");
        $startMonth = key_exists("month_start",$_POST)?$_POST["month_start"]:date("n");
        $endMonth = key_exists("month_end",$_POST)?$_POST["month_end"]:date("n");
        $startData = date("Y-m",strtotime("{$startYear}-{$startMonth}-01"));
        $endData = date("Y-m",strtotime("{$endYear}-{$endMonth}-01"));
        if($startData>$endData){
            return false;
        }else{
            return array(
                "city"=>$city,
                "startYear"=>$startYear,
                "endYear"=>$endYear,
                "startMonth"=>$startMonth,
                "endMonth"=>$endMonth,
                "startDate"=>$startData,
                "endDate"=>$endData,
            );
        }
    }

    private function getExcelData($post){
//workflow$suffix.RequestStatus($cityarg 'PAYROLL',a.id,a.lcd)
        $searchCity = $post["city"];
        $searchCity = City::model()->getDescendantList($searchCity);
        $searchCity .= (empty($searchCity)) ? "'{$post["city"]}'" : ",'{$post['city']}'";
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        $version = Yii::app()->params['version'];
        $cityarg = ($version=='intl' ? 'b.city,' : '');
        $pay_date = "DATE_FORMAT(CONCAT(b.year_no,'-',b.month_no,'-01'),'%Y-%m')";
        $rows = Yii::app()->db->createCommand()
            ->select("a.data_value,b.city,b.year_no,b.month_no,
            {$pay_date} as pay_date
            ")
            ->from("acc_payroll_file_dtl a")
            ->leftJoin("acc_payroll_file_hdr b","a.hdr_id=b.id")
            ->where("a.data_field='amt_total' and b.city in ({$city_allow}) and b.city in ({$searchCity})
             and {$pay_date}>='{$post['startDate']}' and {$pay_date}<='{$post['endDate']}'
             and workflow$suffix.RequestStatus($cityarg 'PAYROLL',b.id,b.lcd)='ED'
             ")
            //and workflow$suffix.RequestStatus($cityarg 'PAYROLL',b.id,b.lcd)='ED'
            ->order("b.city asc,b.year_no asc,b.month_no asc")->queryAll();
        $list = array();
        if($rows){
            $i=0;
            foreach ($rows as $row){
                $city = $row["city"];
                $date = $row["pay_date"];
                if (!key_exists($city,$list)){
                    $cityList = $this->getCityListForCity($city);
                    $i++;
                    $list[$city]=array(
                        "number"=>$i,
                        "area"=>$cityList["area_name"],
                        "city"=>$cityList["city_name"],
                    );
                    foreach ($this->excelColumn as $item){
                        $list[$city][$item]="";
                    }
                }
                $list[$city][$date]=$row["data_value"];
            }
        }
        return $list;
    }

    private function getCityListForCity($city){
        $areaName = "";
        $cityName = $city;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.name as city_name,b.name as area_name")
            ->from("security{$suffix}.sec_city a")
            ->leftJoin("security{$suffix}.sec_city b","a.region=b.code")
            ->where("a.code = :code",array(":code"=>$city))->queryRow();
        if($row){
            $areaName = $row["area_name"];
            $cityName = $row["city_name"];
        }
        return array(
            "area_name"=>$areaName,
            "city_name"=>$cityName,
        );
    }

	public function downExcel(){
        $post = $this->getPostData();
        if($post!==false){
            $headList = $this->getTopArr($post);
            $excelData = $this->getExcelData($post);
            $group["group"][]='attr';
            $excel = new DownPay();
            $excel->colTwo=3;
            $excel->SetHeaderTitle("工资统计");
            $str="单位名称:史伟莎集团\n - 中国区月度应发工资统计\n";
            $str.="查询时间：{$post['startDate']} 至 {$post['endDate']}";
            $excel->SetHeaderString($str);
            $excel->init();
            $excel->setSummaryHeader($headList,true);
            $excel->setListData($excelData);
            $excel->outExcel("工资统计");
        }else{
            $this->addError("pageNum", "开始时间不能大于结束时间！");
        }
    }
}
