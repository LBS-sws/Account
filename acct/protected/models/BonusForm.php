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
        $money=0;
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from acc_bonus where id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        $start=$records['year']."-".$records['month']."-01";
        $end=$records['year']."-".$records['month']."-31";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='".$records['city']."'  and a.status='A'  and a.first_dt>='$start' and a.first_dt<='$end'  and a.target='1'
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
                $span="select * from sales$suffix.sal_performance where city='".$records['city']."' and year='".$records['year']."' and month='".$records['month']."'";
                $spanning = Yii::app()->db->createCommand($span)->queryRow();
                if(empty($spanning['otherspanning'])){
                    $spanning['otherspanning']=0.5;
                }
                $c+=$c* $spanning['otherspanning'];
                $money+=$c*0.04;
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
            $span="select * from sales$suffix.sal_performance where city='".$records['city']."' and year='".$records['year']."' and month='".$records['month']."'";
            $spanning = Yii::app()->db->createCommand($span)->queryRow();
            if(empty($spanning['otherspanning'])){
                $spanning['otherspanning']=0.5;
            }
            $a+=$a* $spanning['otherspanning'];
            $money+=$a*0.04;
        }
        if(empty($money)){
            $money=0;
        }
        $sql = "update account$suffix.acc_bonus set money='$money' where id='$index'
			";
        $command=Yii::app()->db->createCommand($sql)->execute();
        return true;
    }
}
