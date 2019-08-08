<?php
class SalesPromotionCommand extends CConsoleCommand
{

    public function run()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $day = date("d");
        $month = date("m");
        $year = date("Y");
        echo $firstDay=date('Y-m-01', strtotime('-1 month'));
        echo $endDay=date('Y-m-31', strtotime('-1 month'));
        if($day=='8'){
            $sql="select substring_index(salesman,' ', 1) as code,substring_index(salesman,' ', -1) as name,city 
                  from swoper$suffix.swo_service 
                  where status_dt>='$firstDay' and status_dt<='$endDay' and salesman not like '%离职%' group by code
                  UNION
                  select a.code,a.name,a.city from hr$suffix.hr_employee a
                  inner join hr$suffix.hr_binding b on a.id=b.employee_id
                  inner join sales$suffix.sal_visit c on b.user_id=c.username
                  where c.visit_dt>='$firstDay' and c.visit_dt<='$endDay' group by code
";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            for ($i=0;$i<count($records);$i++){
                $sql1="insert into account$suffix.acc_service_comm_hdr(year_no,month_no,employee_code,employee_name,city) values ('$year','$month','".$records[$i]['code']."','".$records[$i]['name']."','".$records[$i]['city']."')";
                $record = Yii::app()->db->createCommand($sql1)->execute();
            }
        }
    }
}

?>
