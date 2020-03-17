<?php
class SalesPromotionCommand extends CConsoleCommand
{

    public function run($args)
    {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $suffix = Yii::app()->params['envSuffix'];
        $day = date("d", strtotime($date));
        $month = date("m", strtotime($date));
        $last_month = date("m", strtotime($date))-1;
        $year = date("Y",strtotime($date));
        if($last_month==0){
            $last_month=12;
            $year=$year-1;
        }
        echo $firstDay=date('Y-m-d', strtotime($date.' first day of previous month'));
        echo $endDay=date('Y-m-d', strtotime($date.' last day of previous month'));
        $sql="select substring_index(a.salesman,' ', -1) as code,substring_index(a.salesman,' ', 1) as name,a.city 
                  from swoper$suffix.swo_service  a
                  inner join hr$suffix.hr_employee b on code=b.code
                  inner join hr$suffix.hr_dept c on b.position=c.id
                  where a.status_dt>='$firstDay' and a.status_dt<='$endDay' and a.salesman not like '%离职%' and c.dept_class not like '%Technician%' 
                  union
                  select a.code,a.name,a.city from hr$suffix.hr_employee a
                  inner join hr$suffix.hr_binding b on a.id=b.employee_id
                  inner join sales$suffix.sal_visit c on b.user_id=c.username
                  inner join hr$suffix.hr_dept d on a.position=d.id
                  where c.visit_dt>='$firstDay' and c.visit_dt<='$endDay'  and d.dept_class not like '%Technician%' 
";
            $records = Yii::app()->db->createCommand($sql)->queryAll();

        $code= array();
        foreach($records as $key=> $val)
        {
            $val['code']=str_replace('(','',$val['code']);
            $val['code']=str_replace(')','',$val['code']);
            $sqls="select * from hr$suffix.hr_employee where name='".$val['name']."' and code='".$val['code']."' and city='".$val['city']."'";
            $arr = Yii::app()->db->createCommand($sqls)->queryAll();
            if(empty($arr)){
                unset($records[$key]);
            }
            elseif(in_array($val['code'],$code))
            {
                unset($records[$key]);
            }else
            {
                $code[]=$val['code'];
            }
        }
        sort($records);
            for ($i=0;$i<count($records);$i++){
                $str=str_replace('(','',$records[$i]['code']);
                $str=str_replace(')','',$str);
                $sql1="insert into account$suffix.acc_service_comm_hdr(year_no,month_no,employee_code,employee_name,city) values ('$year','$last_month','".$str."','".$records[$i]['name']."','".$records[$i]['city']."')";
                $record = Yii::app()->db->createCommand($sql1)->execute();
            }
    }
}

?>
