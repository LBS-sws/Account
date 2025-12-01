<?php

class SellTableList extends CListPageModel
{
    public $year;
    public $month;
    public $down_id=array();

    public $city;

    public function init(){
        if(empty($this->year)||!is_numeric($this->year)){
            $this->year = date("Y");
        }
        if(empty($this->month)||!is_numeric($this->month)){
            $this->month = date("n");
        }
        if(empty($this->city)){
            $this->city=Yii::app()->user->city();
        }
    }

    public function rules()
    {
        return array(
            array('year,month,city,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
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
            'code'=>Yii::t('app','employee_code'),
            'name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'city_name'=>Yii::t('app','city'),
            'time'=>Yii::t('app','Time'),
            'dept_name'=>Yii::t('app','user_name'),
            'moneys'=>Yii::t('app','comm_total_amount'),
            'examine'=>Yii::t('misc','Status'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
	    $this->down_id=array();
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $cityList = Yii::app()->user->city_allow();
        $leaveTime = date("Y/m/01",strtotime("{$this->year}/{$this->month}/01"));
		$sql1 = "select b.name,b.code,c.name as dept_name,a.id,e.name as city_name,f.examine
				from acc_service_comm_hdr a 
				LEFT JOIN hr$suffix.hr_employee b  on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 
                LEFT JOIN acc_product f on a.id=f.service_hdr_id 
				where  a.city in ({$cityList}) and a.year_no= {$this->year} and a.month_no={$this->month} and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
			";
		$sql2 = "select count(a.id)
				from acc_service_comm_hdr a 
				LEFT JOIN hr$suffix.hr_employee b  on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 
                LEFT JOIN acc_product f on a.id=f.service_hdr_id 
				where a.city in ({$cityList}) and a.year_no= {$this->year} and a.month_no={$this->month} and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'code':
					$clause .= General::getSqlConditionClause('b.code',$svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
				case 'city_name':
					$clause .= General::getSqlConditionClause('e.name',$svalue);
					break;
				case 'dept_name':
					$clause .= General::getSqlConditionClause('c.name',$svalue);
					break;
                case 'examine':
                    $examine=$this->examineSql($svalue);
                    $clause .= General::getSqlConditionClause("f.examine",$examine);
                    break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.city desc,a.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
                $this->down_id[]=$record["id"];
			    $this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'time'=>"{$this->year}/{$this->month}",
                    'city_name'=>$record['city_name'],
                    'dept_name'=>$record['dept_name'],
                    'style'=>self::examineStyle($record['examine']),
                    'examine'=>self::examine($record['examine']),
                    'moneys'=>key_exists("moneys",$record)?floatval($record['moneys']):0,
                );
			}
		}
		$session = Yii::app()->session;
		$session["sellTable_c01"] = $this->getCriteria();
        $this->down_id = implode(",",$this->down_id);
		return true;
	}

    public function getCriteria() {
        return array(
            'year'=>$this->year,
            'month'=>$this->month,
            'city'=>$this->city,
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

    public static function examine($examine){
	    $arr = array(
	        "N"=>Yii::t("salestable","Not reviewed"),
	        "A"=>Yii::t("salestable","Adopt"),
	        "S"=>Yii::t("salestable","Rejected"),
	        "Y"=>Yii::t("salestable","Reviewed"),
	        ""=>Yii::t("salestable","Not reviewed"),
        );
	    if(key_exists($examine,$arr)){
	        return $arr[$examine];
        }else{
            return Yii::t("salestable","Not reviewed");
        }
    }

    public static function examineStyle($examine){
	    $arr = array(
	        "N"=>'',//未审核
	        "A"=>'style="color: green"',//审核通过
	        "S"=>'style="color: red"',//已拒绝
	        "Y"=>'style="color: blue"',//待审核
        );
	    if(key_exists($examine,$arr)){
	        return $arr[$examine];
        }else{
            return "";
        }
    }

    public static function examineSql($examine){
        $a="";
        if($examine=='N')$a=Yii::t('salestable','Not reviewed');//未审核
        if($examine=='A')$a=Yii::t('salestable','Adopt');//审核通过
        if($examine=='S')$a=Yii::t('salestable','Rejected');//已拒绝
        if($examine=='Y')$a=Yii::t('salestable','Reviewed');//待审核
        if($examine=='')$a=Yii::t('salestable','Not reviewed');//未审核
        return $a;
    }
}
