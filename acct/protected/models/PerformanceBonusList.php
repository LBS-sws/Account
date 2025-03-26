<?php

class PerformanceBonusList extends CListPageModel
{
    public $year_no;
    public $month_no;
    public $quarter_no;
	public function attributeLabels()
	{
		return array(
            'code'=>Yii::t('app','employee_code'),
            'name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'city_name'=>Yii::t('app','city'),
            'time'=>Yii::t('app','Time'),
            'quarter_no'=>Yii::t('service','quarter no'),
            'dept_name'=>Yii::t('app','user_name'),
            'moneys'=>Yii::t('app','comm_total_amount'),
            'status_type'=>Yii::t('service','status type'),
            'bonus_amount'=>Yii::t('service','bonus amount'),
            'bonus_out'=>"当月实发奖金",
		);
	}

    public function rules()
    {
        return array(
            array('year_no,month_no,attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

    public function getCriteria() {
        return array(
            'year_no'=>$this->year_no,
            'month_no'=>$this->month_no,
            'quarter_no'=>$this->quarter_no,
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

    public function init(){
        if(empty($this->year_no)||!is_numeric($this->year_no)){
            $this->year_no = date("Y",strtotime("-1 months"));
        }
        if(empty($this->month_no)||!is_numeric($this->month_no)){
            $this->month_no = date("n",strtotime("-1 months"));
        }
        if($this->year_no==2024){
            $this->year_no = 2025;
            $this->month_no=1;
        }
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
        $this->quarter_no = ceil($this->month_no/3);
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
        $minMonth = ($this->quarter_no-1)*3 + 1;
        $monthList = "".$minMonth;
		for ($i=$minMonth+1;$i<$minMonth+3;$i++){
            $monthList.=",".$i;
        }
		$hdrSql = "
		SELECT employee_code,city FROM acc_service_comm_hdr WHERE
		 city in ({$citylist}) and year_no={$this->year_no} and month_no in ({$monthList})
		 GROUP BY employee_code,city
		";
        $leaveTime = date("Y/m/01",strtotime("{$this->year_no}/{$minMonth}/01"));
		//acc_performance_bonus
		$sql1 = "select b.id,b.code,b.name,c.name as dept_name, e.name as city_name,
                g.status_type ,g.bonus_out ,f.new_amount ,f.bonus_amount 
				from ($hdrSql) a
				LEFT JOIN hr$suffix.hr_employee b on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  	
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 		
                LEFT JOIN account$suffix.acc_performance_bonus f on f.employee_id=b.id AND f.year_no={$this->year_no} AND f.quarter_no={$this->quarter_no}		
                LEFT JOIN account$suffix.acc_performance_info g on f.id=g.bonus_id AND g.year_no={$this->year_no} AND g.month_no={$this->month_no}		
				where 1=1 and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime')) 
			";
		$sql2 = "select count(b.id)
				from ($hdrSql) a
				LEFT JOIN hr$suffix.hr_employee b on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  	
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 		
                LEFT JOIN account$suffix.acc_performance_bonus f on f.employee_id=b.id AND f.year_no={$this->year_no} AND f.quarter_no={$this->quarter_no}		
                LEFT JOIN account$suffix.acc_performance_info g on f.id=g.bonus_id AND g.year_no={$this->year_no} AND g.month_no={$this->month_no}			
				where 1=1 and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime')) 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'code':
                    $clause .= General::getSqlConditionClause('b.code', $svalue);
                    break;
                case 'name':
                    $clause .= General::getSqlConditionClause('b.name', $svalue);
                    break;
                case 'city_name':
                    $clause .= General::getSqlConditionClause('e.name', $svalue);
                    break;
                case 'dept_name':
                    $clause .= General::getSqlConditionClause('c.name', $svalue);
                    break;
            }
		}
		
		$order = "";
        if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order .= " order by b.city desc,b.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
		    $bool = Yii::app()->user->validRWFunction('XS12');
		    $quaStr = PerformanceBonusForm::getQuarterStr($this->year_no,$this->quarter_no);
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'time'=>"{$this->year_no}年{$this->month_no}月",
                    'quaStr'=>$quaStr,
                    'city_name'=>$record['city_name'],
                    'dept_name'=>$record['dept_name'],
                    'ready'=>$bool&&$record['status_type']!=1,
                    'status_type'=>PerformanceBonusForm::getStatusStr($record['status_type']),
                    'new_amount'=>$record['status_type']!=1?"-":floatval($record['new_amount']),
                    'bonus_out'=>$record['status_type']!=1?"-":floatval($record['bonus_out']),
                    'style'=>$record['status_type']!=1?"text-danger":""
				);
			}
		}
		$session = Yii::app()->session;
		$session['performanceBonus_xs08'] = $this->getCriteria();
		return true;
	}

	public static function getYearList(){
        $minYear=2025;
        $maxYear = date("Y");
        $arr = array();
        for ($i=$minYear;$i<=$maxYear;$i++){
            $arr[$i] = $i."年";
        }
        return $arr;
    }

	public static function getQuarterList(){
        return array(
            1=>"1月~3月",
            2=>"4月~6月",
            3=>"7月~9月",
            4=>"10月~12月",
        );
    }

	public static function getMonthList(){
        $arr = array();
        for ($i=1;$i<=12;$i++){
            $arr[$i] = $i."月";
        }
        return $arr;
    }
}
