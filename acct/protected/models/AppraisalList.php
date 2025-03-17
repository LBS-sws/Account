<?php

class AppraisalList extends CListPageModel
{
    public $year_no;
    public $month_no;
	public function attributeLabels()
	{
		return array(
            'code'=>Yii::t('app','employee_code'),
            'name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'city_name'=>Yii::t('app','city'),
            'time'=>Yii::t('service','Appraisal date'),
            'dept_name'=>Yii::t('app','user_name'),
            'moneys'=>Yii::t('app','comm_total_amount'),
            'status_type'=>Yii::t('service','status type'),
            'entry_time'=>Yii::t('service','entry time'),
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
        if($this->year_no==2025&&$this->month_no==1){
            $this->month_no=2;
        }
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
        $thisDate = date("Y-m-d",strtotime("{$this->year_no}-{$this->month_no}-01"));
        $minEntry = date("Y-m-d",strtotime("{$thisDate} - 5 months"));
        $maxEntry = date("Y-m-d",strtotime("{$thisDate} + 1 months - 1 days"));
        $leaveTime = date("Y/m/01",strtotime("{$this->year_no}/{$this->month_no}/01"));
		//acc_performance_bonus
		$sql1 = "select b.id,b.code,b.name,b.entry_time,c.name as dept_name, e.name as city_name,f.status_type ,f.appraisal_amount 
				from acc_service_comm_hdr a
				LEFT JOIN hr$suffix.hr_employee b on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  	
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 		
                LEFT JOIN account$suffix.acc_appraisal f on f.employee_id=b.id AND f.year_no={$this->year_no} AND f.month_no={$this->month_no}		
				where a.city in ({$citylist}) and a.year_no={$this->year_no} and a.month_no={$this->month_no}
				AND DATE_FORMAT(b.entry_time, '%Y-%m-%d') BETWEEN '{$minEntry}' and '{$maxEntry}'
				AND (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
			";
		$sql2 = "select count(b.id)
				from acc_service_comm_hdr a
				LEFT JOIN hr$suffix.hr_employee b on b.code=a.employee_code
                LEFT JOIN hr$suffix.hr_dept c on b.position=c.id  	
                LEFT JOIN security$suffix.sec_city e on a.city=e.code 	
                LEFT JOIN account$suffix.acc_appraisal f on f.employee_id=b.id AND f.year_no={$this->year_no} AND f.month_no={$this->month_no}		
				where a.city in ({$citylist}) and a.year_no={$this->year_no} and a.month_no={$this->month_no}
				AND DATE_FORMAT(b.entry_time, '%Y-%m-%d') BETWEEN '{$minEntry}' and '{$maxEntry}'
				AND (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime'))
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
		    $userIDList = AppraisalForm::getSalesAccessForMe();
		    $quaStr = $this->year_no."年".$this->month_no."月";
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'time'=>$quaStr,
                    'city_name'=>$record['city_name'],
                    'dept_name'=>$record['dept_name'],
                    'ready'=>$record['status_type']!=1&&in_array($record["id"],$userIDList),
                    'entry_time'=>General::toDate($record['entry_time']),
                    'status_type'=>AppraisalForm::getStatusStr($record['status_type']),
                    'appraisal_amount'=>$record['status_type']!=1?"-":floatval($record['appraisal_amount']),
                    'style'=>$record['status_type']!=1?"text-danger":""
				);
			}
		}
		$session = Yii::app()->session;
		$session['appraisal_xs08'] = $this->getCriteria();
		return true;
	}

	public static function getYearList(){
        $minYear=2025;
        $maxYear = date("Y");
        $maxYear = date("n")<2?$maxYear-1:$maxYear;
        $arr = array();
        for ($i=$minYear;$i<=$maxYear;$i++){
            $arr[$i] = $i."年";
        }
        return $arr;
    }

	public static function getMonthList(){
        $arr = array();
        for ($i=1;$i<=12;$i++){
            $arr[$i] = $i."月";
        }
        return $arr;
    }
}
