<?php

class BonusForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $rpt_type;
	public $type_group;
	public $city;
    public $sum;
    public $sums;
    public $year;
    public $month;
    public $spanning;
    public $otherspanning;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('app','Description'),
			'rpt_type'=>Yii::t('app','Report Category'),
			'city'=>Yii::t('app','City'),
			'type_group'=>Yii::t('app','Type'),
            'sum'=>Yii::t('app','Sum'),
            'sums'=>Yii::t('app','Sums'),
            'year'=>Yii::t('app','Year'),
            'month'=>Yii::t('app','Month'),
            'money'=>Yii::t('app','Money'),


		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('','required'),
			array('id,rpt_type,sums,spanning,otherspanning','safe'),
		);
	}

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_performance where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->year = $row['year'];
			$this->month = $row['month'];
            $this->sum = $row['sum'];
            $this->sums = $row['sums'];
            $this->spanning = $row['spanning'];
            $this->otherspanning = $row['otherspanning'];
		}
		return true;
	}
	
	public function saveData($index)
    {
        $city = Yii::app()->user->city();
        $money=0;
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from acc_bonus where id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        $month=$records['month']-1;
        $year=$records['year'];
        if($month==0){
            $year=$records['year']-1;
            $month=12;
        }
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";

        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='".$records['city']."'  and a.status='A'  and a.status_dt>='$start' and a.status_dt<='$end'  and a.target='1'
			";
        $rows= Yii::app()->db->createCommand($sql1)->queryAll();
        foreach ($rows as $records){
            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*12;
            }
            if($records['b4_paid_type']=='1'||$records['b4_paid_type']=='Y'){
                $b=$records['b4_amt_paid'];
            }else{
                $b=$records['b4_amt_paid']*12;
            }
            $c=$a-$b;
            if($c>0){
                $sqlss="select id from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sqlss)->queryRow();
                $otherspanning=$this->getOtherRoyalty($records1['id'],$city,$year,$month,$records['salesman']);
//                $span="select * from sales$suffix.sal_performance where city='".$records['city']."' and year='".$year."' and month='".$month."'";
//                $spanning = Yii::app()->db->createCommand($span)->queryRow();
//                if(empty($spanning['otherspanning'])){
//                    $spanning['otherspanning']=0.5;
//                }
                $money+=$c*0.04*$otherspanning;
            }
        }
        $sql2 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='".$records['city']."'  and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'  and a.target='1'
			";
        $rows= Yii::app()->db->createCommand($sql2)->queryAll();
        foreach ($rows as $records){
            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*12;
            }
            $sqlss="select id from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
            $records1 = Yii::app()->db->createCommand($sqlss)->queryRow();
            $otherspanning=$this->getOtherRoyalty($records1['id'],$city,$year,$month,$records['salesman']);
//            $span="select * from sales$suffix.sal_performance where city='".$records['city']."' and year='".$year."' and month='".$month."'";
//            $spanning = Yii::app()->db->createCommand($span)->queryRow();
//            if(empty($spanning['otherspanning'])){
//                $spanning['otherspanning']=0.5;
//            }
            $money+=$a*0.04*$otherspanning;

        }
        if(empty($money)){
            $money=0;
        }
        $sql = "update acc_bonus set money='$money' where id='$index'
			";
        $command=Yii::app()->db->createCommand($sql)->execute();
        return true;
    }


    public function getOtherRoyalty($index,$city,$year,$month,$ohersaleman){
        //按什么比例计算被跨区提成
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.group_type from hr$suffix.hr_employee a
                    left outer join  acc_service_comm_hdr b on b.employee_code=a.code
                    where b.id='$index'
                ";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $span="select * from sales$suffix.sal_performance where city='$city' and year='$year' and month='$month'";
        $spanning = Yii::app()->db->createCommand($span)->queryRow();
        if($records==0){
            if(empty($spanning['otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['otherspanning'];
            }
        }
        if($records==1){
            if(empty($spanning['business_otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['business_otherspanning'];
            }
        }
        if($records==2){
            if(empty($spanning['restaurant_otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['restaurant_otherspanning'];
            }
        }
        if(!empty($ohersaleman)){
            $ohersaleman=str_replace('(','',$ohersaleman);
            $ohersaleman=str_replace(')','',$ohersaleman);
            $sql1="select group_type from hr$suffix.hr_employee where  concat_ws(' ',name,code)= '".$ohersaleman."' ";
            $record = Yii::app()->db->createCommand($sql)->queryScalar();
            if($record!=$records){
                $proportion=0.5;
            }
        }
        return $proportion;
    }
}
