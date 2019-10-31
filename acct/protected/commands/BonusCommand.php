<?php
class BonusCommand extends CConsoleCommand
{
    public function run()
    {
        $months=date('m');
        $years=date('Y');
        $day=date('d');
        $start=$years."-".$months."-01";
        $end=$years."-".$months."-31";
        $money=0;
            $suffix = Yii::app()->params['envSuffix'];
            $sql="select a.code
				from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
				where b.code is null 
				order by a.code";
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $city = $row['code'];
                    $uid = 'admin';
                    $month=$months-1;
                    $year=$years;
                    if($month==0){
                        $year=$years-1;
                        $month=12;
                    }
                    $start=$year."-".$month."-01";
                    $end=$year."-".$month."-31";
                    $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='$city'  and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'  and a.target='1'
			";
                    $rows = Yii::app()->db->createCommand($sql1)->queryAll();
                    foreach ($rows as $records){
                        if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                            $a=$records['amt_paid'];
                        }else{
                            $a=$records['amt_paid']*12;
                        }
                        $span="select * from sales$suffix.sal_performance where city='$city' and year='$year' and month='$month'";
                        $spanning = Yii::app()->db->createCommand($span)->queryRow();
                        if(empty($spanning['otherspanning'])){
                            $spanning['otherspanning']=0.5;
                        }
                        $a+=$a* $spanning['otherspanning'];
                        $money+=$a*0.04;
                    }

                    $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			
				where a.city='$city'  and a.status='A'  and a.first_dt>='$start' and a.first_dt<='$end'  and target='1'
			";
                    $rows = Yii::app()->db->createCommand($sql1)->queryAll();
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
                            $span="select * from sales$suffix.sal_performance where city='$city' and year='$year' and month='$month'";
                            $spanning = Yii::app()->db->createCommand($span)->queryRow();
                            if(empty($spanning['otherspanning'])){
                                $spanning['otherspanning']=0.5;
                            }
                            $c+=$c* $spanning['otherspanning'];
                            $money+=$c*0.04;
                        }
                    }
                    if(empty($money)){
                        $money=0;
                    }
                    $sql = "insert into account$suffix.acc_bonus(city, year, month, money,lcu, luu) 
				values('$city', '$years', '$months', '$money','$uid', '$uid')
			";
                    $command=Yii::app()->db->createCommand($sql)->execute();
                }
            }

    }
}