<?php

class PlaneAwardList extends CListPageModel
{
    public $year;
    public $month;

    public $jobList;
    public $city_name;

    public function init(){
        if(empty($this->year)||!is_numeric($this->year)){
            $this->year = date("Y");
        }
        if(empty($this->month)||!is_numeric($this->month)){
            $this->month = date("n");
        }
    }

    public function rules()
    {
        return array(
            array('year,month,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
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
			'code'=>Yii::t('plane','employee code'),
			'name'=>Yii::t('plane','employee name'),
			'city'=>Yii::t('plane','city'),
			'city_name'=>Yii::t('plane','city'),
			'job_num'=>Yii::t('plane','job num'),
			'money_num'=>Yii::t('plane','money num'),
			'year_num'=>Yii::t('plane','year num'),
			'other_sum'=>Yii::t('plane','other sum'),
			'plane_sum'=>Yii::t('plane','plane sum'),

			'entry_time'=>Yii::t('plane','entry time'),//入职日期
			'department'=>Yii::t('plane','department'),//部门
			'position'=>Yii::t('plane','position'),//职位
			'staff_leader'=>Yii::t('plane','staff leader'),//队长/组长
			'plane'=>Yii::t('plane','Plane Reward'),//直升机奖励
            'old_pay_wage'=>Yii::t('plane','old shall pay wages'),//原机制应发工资
            'difference'=>Yii::t('plane','difference'),//差額
		);
	}

	public function setJobList(){
        $city = Yii::app()->user->city();
        $date = "{$this->year}-{$this->month}-01";
        $this->jobList = PlaneSetJobForm::getPlaneList($date,$city);
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
	    $this->setJobList();
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $cityList = Yii::app()->user->city_allow();
		$sql1 = "select f.id,f.job_id,f.job_num,f.money_num,f.year_num,f.other_sum,f.old_pay_wage,f.plane_sum,a.code,a.name,b.name as city_name,(IFNULL(f.plane_sum,0)-IFNULL(f.old_pay_wage,0)) as difference
				from acc_plane f 
				LEFT JOIN hr{$suffix}.hr_employee a ON a.id=f.employee_id
				LEFT JOIN security$suffix.sec_city b ON b.code=f.city  
				where f.plane_year={$this->year} and f.plane_month={$this->month} and f.city in ({$cityList})  
			";
		$sql2 = "select count(f.id)
				from acc_plane f 
				LEFT JOIN hr{$suffix}.hr_employee a ON a.id=f.employee_id
				LEFT JOIN security$suffix.sec_city b ON b.code=f.city  
				where f.plane_year={$this->year} and f.plane_month={$this->month} and f.city in ({$cityList})  
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'code':
					$clause .= General::getSqlConditionClause('a.code',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by f.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
			    $str = key_exists($record['job_id'],$this->jobList)?$this->jobList[$record['job_id']]["name"]:PlaneSetJobForm::getPlaneName($record['job_id']);

			    $this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'color'=>floatval($record['difference'])>0?"":"color:red",
                    'name'=>$record['name'],
                    'city_name'=>$record['city_name'],
                    'job_num'=>empty($str)?$record['job_num']:$str,
                    'money_num'=>$record['money_num'],
                    'year_num'=>$record['year_num'],
                    'other_sum'=>floatval($record['other_sum']),
                    'plane_sum'=>floatval($record['plane_sum']),
                    'old_pay_wage'=>floatval($record['old_pay_wage']),
                    'difference'=>floatval($record['difference']),
                );
			}
		}
		$session = Yii::app()->session;
		$session['planeAward_c01'] = $this->getCriteria();
		return true;
	}

    public function getCriteria() {
        return array(
            'year'=>$this->year,
            'month'=>$this->month,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }

    //获取excel下载的数据
    private function getExcelData(){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $this->city_name = General::getCityName($city);
        $entryDate = date("Y-m-d",strtotime("{$this->year}-{$this->month}-01 + 1 months - 1 day"));
        $headList = array(
            "K01"=>array("text"=>"员工姓名","width"=>"18","num"=>0),
            "K02"=>array("text"=>"入职日期","width"=>"14","num"=>1),
            "K03"=>array("text"=>"级别职务","width"=>"10","num"=>2),
            "K04"=>array("text"=>"做单金额","width"=>"14","num"=>3),
            "K05"=>array("text"=>"员工年资","width"=>"14","num"=>4),
            "K06"=>array("text"=>"直升机奖励（做单金额）","width"=>"26","num"=>5),
            "K07"=>array("text"=>"直升机奖励（级别职务）","width"=>"26","num"=>6),
            "K08"=>array("text"=>"直升机奖励（年资）","width"=>"26","num"=>7),
            "K09"=>array("text"=>"直升机总奖励","width"=>"26","num"=>8),
        );
        $serviceList = array();
        $headExpr=array();
        $rows = Yii::app()->db->createCommand()
            ->select("f.*,a.code,a.name,a.entry_time")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept d","a.position=d.id")
            ->leftJoin("acc_plane f","a.id=f.employee_id and f.plane_year={$this->year} and f.plane_month={$this->month}")
            ->where("a.staff_status=0 and replace(a.entry_time,'/', '-')<='{$entryDate}' and d.dept_class='Technician' and a.city='{$city}'")
            ->order("f.id desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr = array(
                    "K01"=>$row["code"]." - ".$row["name"],
                    "K02"=>General::toDate($row["entry_time"]),
                    "K03"=>PlaneSetJobForm::getPlaneName($row["job_id"])
                );
                if(!empty($row["id"])){
                    $arr["K04"]=floatval($row["money_value"]);
                    $arr["K05"]=floatval($row["year_month"]);
                    $arr["K06"]=floatval($row["money_num"]);
                    $arr["K07"]=floatval($row["job_num"]);
                    $arr["K08"]=floatval($row["year_num"]);
                    $arr["K09"]=$arr["K06"]+$arr["K07"]+$arr["K08"];
                    $arr["N01"]=floatval($row["plane_sum"]);
                    $arr["N02"]=floatval($row["old_pay_wage"]);
                    $arr["N03"]=$arr["N01"]-$arr["N02"];
                    $this->resetOtherList($row,$headExpr,$arr);
                }
                $serviceList[]=$arr;
            }
        }
        $num=8;
        if(!empty($headExpr)){
            foreach ($headExpr as $key=>$item){
                $num++;
                $headList[$key]=array("text"=>$item,"width"=>"26","num"=>$num);
            }
        }
        $headList["N01"]=array("text"=>"汇总","width"=>"10","num"=>$num+1);
        $headList["N02"]=array("text"=>"原机制应发工资","width"=>"26","num"=>$num+2);
        $headList["N03"]=array("text"=>"差额","width"=>"14","num"=>$num+3);
        return array("headList"=>$headList,"serviceList"=>$serviceList);
    }

    //横向添加其它项目明细
    private function resetOtherList($row,&$headExpr,&$arr){
        $otherList = Yii::app()->db->createCommand()
            ->select("a.other_id,a.other_num,b.set_name")
            ->from("acc_plane_info a")
            ->leftJoin("acc_plane_set_other b","a.other_id=b.id")
            ->where("a.plane_id={$row['id']}")->queryAll();
        if($otherList){
            foreach ($otherList as $other){
                $key = "L{$other['other_id']}";
                if(!key_exists($key,$headExpr)){
                    $headExpr[$key]=$other["set_name"];
                }
                $arr[$key]=$other["other_num"];
            }
        }
    }

    //下载excel
    public function downExcel(){
        set_time_limit(0);
        $excelList = $this->getExcelData();
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        //spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

        $endStr = PHPExcel_Cell::stringFromColumnIndex(count($excelList["headList"])-1);
        $objPHPExcel = new PHPExcel();
//设置文档基本属性
        $objProps = $objPHPExcel->getProperties();
        $objProps->setCreator("Zeal Li");
        $objProps->setLastModifiedBy("Zeal Li");
        $objProps->setTitle("Office XLS Test Document");
        $objProps->setSubject("Office XLS Test Document, Demo");
        $objProps->setDescription("kol document, generated by PHPExcel.");
        $objProps->setKeywords("office excel PHPExcel");
        $objProps->setCategory("Test");

        $objActSheet = $objPHPExcel->setActiveSheetIndex(0); //填充表头
        $objActSheet->getDefaultStyle()->getFont()->setSize(18);//字体大小

        $objActSheet->setCellValue("A1","城市：");
        $objActSheet->setCellValue("B1",$this->city_name);
        $objActSheet->getStyle("A1")->getFont()->setBold(true);//加粗
        $objActSheet->getStyle("A1")->getAlignment()->setHorizontal("right");
        $objActSheet->getRowDimension("A1")->setRowHeight(22);//行高

        $objActSheet->setCellValue("A2","提成日期：");
        $objActSheet->setCellValue("B2","{$this->year}年{$this->month}月");
        $objActSheet->getStyle("A2")->getFont()->setBold(true);//加粗
        $objActSheet->getStyle("A2")->getAlignment()->setHorizontal("right");
        $objActSheet->getDefaultStyle()->getFont()->setSize(12);//字体大小
        $objActSheet->getRowDimension("A2")->setRowHeight(22);//行高

        $objActSheet->freezePane("E6");
        $rowKey=5;
        foreach ($excelList["headList"] as $key=>$head){
            $string = PHPExcel_Cell::stringFromColumnIndex($head["num"]);
            $objActSheet->setCellValue("{$string}{$rowKey}",$head["text"]);
            $objActSheet->getColumnDimension("{$string}")->setWidth($head["width"]);//行寬
            $objActSheet->getRowDimension($rowKey)->setRowHeight(44);//行高
            $objActSheet->getStyle("{$string}{$rowKey}")->getAlignment()->setHorizontal('center');
            $objActSheet->getStyle("{$string}{$rowKey}")->getAlignment()->setVertical('center');
            $objActSheet->getStyle("{$string}{$rowKey}")->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => "e2efda"
                )
            ));//背景顏色
        }
        if(!empty($excelList["serviceList"])){
            foreach ($excelList["serviceList"] as $service){
                $rowKey++;
                if(key_exists("N03",$service)&&$service["N03"]<0){ //差额小于零
                    $objActSheet->getStyle($rowKey)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                }
                foreach ($service as $key=>$value){
                    if(key_exists($key,$excelList["headList"])){
                        $string = PHPExcel_Cell::stringFromColumnIndex($excelList["headList"][$key]["num"]);

                        $objActSheet->setCellValue("{$string}{$rowKey}",$value);
                        $objActSheet->getRowDimension($rowKey)->setRowHeight(26);//行高

                    }
                }
            }
        }
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK
                ),
            )
        );
        $objActSheet->getStyle("A5:{$endStr}{$rowKey}")->applyFromArray($styleArray);

        $fileName="plane_".time().".xlsx";
        ob_end_clean();//清除缓冲区,避免乱码
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header('Content-Disposition: attachment;filename='.$fileName);
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
    }
}
