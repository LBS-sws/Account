<?php

class PlaneAwardList extends CListPageModel
{
    public $year;
    public $month;

    public $jobList;

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
		);
	}

	public function setJobList(){
        $date = "{$this->year}-{$this->month}-01";
        $this->jobList = PlaneSetJobForm::getPlaneList($date);
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
	    $this->setJobList();
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $cityList = Yii::app()->user->city_allow();
		$sql1 = "select f.id,f.job_id,f.job_num,f.money_num,f.year_num,f.other_sum,f.plane_sum,a.code,a.name,b.name as city_name
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
			    $str = key_exists($record['job_id'],$this->jobList)?$this->jobList[$record['job_id']]["name"]:"";

			    $this->attr[] = array(
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'city_name'=>$record['city_name'],
                    'job_num'=>empty($str)?$record['job_num']:$str,
                    'money_num'=>$record['money_num'],
                    'year_num'=>$record['year_num'],
                    'other_sum'=>floatval($record['other_sum']),
                    'plane_sum'=>floatval($record['plane_sum']),
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
}
