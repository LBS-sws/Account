<?php

class PlaneAllotList extends CListPageModel
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
			'entry_time'=>Yii::t('plane','entry time'),//入职日期
			'department'=>Yii::t('plane','department'),//部门
			'position'=>Yii::t('plane','position'),//职位
			'staff_leader'=>Yii::t('plane','staff leader'),//队长/组长
			'plane'=>Yii::t('plane','Plane Reward'),//直升机奖励
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
        $entryDate = date("Y-m-d",strtotime("{$this->year}-{$this->month}-01 + 1 months - 1 day"));
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
		$sql1 = "select f.id as plane_id,f.job_id,a.id,a.code,a.name,a.entry_time,a.staff_leader,b.name as department_name,d.name as position_name 
				from hr{$suffix}.hr_employee a 
				 LEFT JOIN hr{$suffix}.hr_dept b ON a.department=b.id
				 LEFT JOIN hr{$suffix}.hr_dept d ON a.position=d.id
				 LEFT JOIN acc_plane f ON a.id=f.employee_id and f.plane_year={$this->year} and f.plane_month={$this->month} 
				where a.staff_status=0 and replace(a.entry_time,'/', '-')<='{$entryDate}' and d.dept_class='Technician' and a.city='{$city}'  
			";
		$sql2 = "select count(a.id)
				from hr{$suffix}.hr_employee a 
				 LEFT JOIN hr{$suffix}.hr_dept b ON a.department=b.id
				 LEFT JOIN hr{$suffix}.hr_dept d ON a.position=d.id
				where a.staff_status=0 and replace(a.entry_time,'/', '-')<='{$entryDate}' and d.dept_class='Technician' and a.city='{$city}'   
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
				case 'department':
					$clause .= General::getSqlConditionClause('b.name',$svalue);
					break;
				case 'position':
					$clause .= General::getSqlConditionClause('d.name',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.code desc ";
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
                    'style'=>empty($record['plane_id'])?"text-danger":"text-success",
                    'id'=>$record['id'],
                    'code'=>$record['code'],
                    'name'=>$record['name'],
                    'entry_time'=>$record['entry_time'],
                    'department'=>$record['department_name'],
                    'position'=>$record['position_name'],
                    'plane_id'=>$record['plane_id'],
                    'plane'=>empty($record['plane_id'])?Yii::t("plane","None Allot"):Yii::t("plane","Allot")." ({$str})",
                    'staff_leader'=>self::getStaffLeader($record['staff_leader'],true),
                );
			}
		}
		$session = Yii::app()->session;
		$session['planeAllot_c01'] = $this->getCriteria();
		return true;
	}

	public function allotMore($allotList){
	    if(!empty($allotList)){
            $this->setJobList();
	        $date = "{$this->year}-{$this->month}-1";
            if(empty($this->jobList)){
                $this->addError("year", "{$date}没有生效的职位级别");
                return false;
            }
            $bool = true;
            foreach ($allotList as $id=>$value){
                $oneBool = $this->allotOne($id);
                $bool = $bool&&$oneBool?true:false;
            }
            return $bool;
        }else{
            $this->addError("year", "请选择需要参与的员工");
            return false;
        }
    }

    public function allotOne($id,$job_id=false){
        $this->setJobList();
        $jobList = $this->jobList;
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $uid = Yii::app()->user->id;
        $staffBool = Yii::app()->db->createCommand()->select("a.id,a.name")
            ->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept d","a.position=d.id")
            ->where("a.id=:id and a.staff_status=0 and d.dept_class='Technician' and a.city='{$city}'",array(":id"=>$id))->queryRow();
        if($staffBool){
            $planeBool = $planeRow = Yii::app()->db->createCommand()->select("id")->from("acc_plane")
                ->where("employee_id=:id and plane_year={$this->year} and plane_month={$this->month}",array(":id"=>$id))->queryRow();

            if($planeBool){
                $this->addError("year", "员工({$staffBool['name']})已参加直升机奖励");
                return false;
            }
            if($job_id===false){
                $planeRow = Yii::app()->db->createCommand()->select("a.id,a.job_id")
                    ->from("acc_plane a")
                    ->where("a.employee_id=:id",array(":id"=>$id))->order("plane_date desc")->queryRow();
                $job_id = $planeRow?$planeRow["job_id"]:"";
            }
            if(key_exists($job_id,$jobList)){
                //参与直升机
                Yii::app()->db->createCommand()->insert("acc_plane",array(
                    "employee_id"=>$id,
                    "plane_year"=>$this->year,
                    "plane_month"=>$this->month,
                    "plane_date"=>"{$this->year}-{$this->month}-01",
                    "job_id"=>$job_id,
                    "job_num"=>$jobList[$job_id]["num"],
                    "city"=>$city,
                    "lcu"=>$uid,
                ));
                $id = Yii::app()->db->getLastInsertID();
                $model = new PlaneAwardForm();
                $model->retrieveData($id,false);//刷新直升机的其它金额
                unset($model);
                return true;
            }else{
                $this->addError("year", "员工({$staffBool['name']})需要手动设置职位级别");
                return false;
            }
        }else{
            $this->addError("year", "员工id({$id})无效");
            return false;
        }
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

	public static function getStaffLeader($id='',$bool=false){
	    $list = array(
	        "Nil"=>Yii::t("plane","Nil"),
	        "Group Leader"=>Yii::t("plane","Group Leader"),
	        "Team Leader"=>Yii::t("plane","Team Leader"),
        );
	    if($bool){
            if(key_exists($id,$list)){
                return $list[$id];
            }else{
                return $id;
            }
        }else{
	        return $list;
        }
    }

    public static function getYearList(){
        $arr = array();
        $year = date("Y");
        for($i=$year-4;$i<$year+2;$i++){
            if($i>2021){
                $arr[$i] = $i.Yii::t("plane"," year unit");
            }
        }
        return $arr;
    }

    public static function getMonthList($bool=false){
        $arr = array();
        if ($bool){
            $arr[]=Yii::t("plane","all");
        }
        for($i=1;$i<=12;$i++){
            $arr[$i] = $i.Yii::t("plane"," month unit");
        }
        return $arr;
    }
}
