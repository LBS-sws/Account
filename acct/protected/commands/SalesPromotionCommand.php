<?php
class SalesPromotionCommand extends CConsoleCommand
{

    public function run($args)
    {
        $date = empty($args) ? date("Y-m-d") : $args[0];
        $suffix = Yii::app()->params['envSuffix'];
        $day = date("d", strtotime($date));
        $month = date("m", strtotime($date));
        $last_month = date("m", strtotime($date));//2024-08-14日改成刷新当月，不是上月
        $last_month =intval($last_month);
        $year = date("Y",strtotime($date));
        //$leave_time=date('Y/m/d', strtotime($date . ' -1 month'));
        echo $firstDay=date('Y-m-01', strtotime($date));
        echo $endDay=date('Y-m-t', strtotime($date));
        $sql="select b.code,b.name,a.city 
                  from swoper$suffix.swo_service  a
                  inner join hr$suffix.hr_employee b on a.salesman_id = b.id
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
                $bool = Yii::app()->db->createCommand()->select("id")->from("account$suffix.acc_service_comm_hdr")
                    ->where("year_no=:year_no and month_no=:month_no and employee_code=:employee_code and employee_name=:employee_name and city=:city",
                        array(":year_no"=>$year,":month_no"=>$last_month,":employee_code"=>$str,":employee_name"=>$records[$i]['name'],":city"=>$records[$i]['city']))->queryRow();
                if(!$bool){//如果不存在則新增
                    $record = Yii::app()->db->createCommand($sql1)->execute();
                }
            }
    }
}

?>
