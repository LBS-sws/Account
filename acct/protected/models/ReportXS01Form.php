<?php
/* Reimbursement Form */

class ReportXS01Form extends CReportForm
{
    public $staffs;
    public $status_dt;
    public $company_name;
    public $status;
    public $cust_type;
    public $service;
    public $product_id;
    public $amt_paid;
    public $paid_type;
    public $amt_install;
    public $staffs_desc;
    public $first_dt;
    public $sign_dt;
    public $all_number;
    public $surplus;
    public $employee_name;
    public $new_calc;
    public $new_amount;
    public $edit_amount;
    public $end_amount;
    public $all_amount;
    public $saleyear;
    public $othersalesman;
    public $salesman;
    public $performance_amount;
    public $new_money;
    public $edit_money;
    public $out_money;
    public $performance;
    public $performanceedit_amount;
    public $performanceend_amount;
    public $performanceedit_money;
    public $group_type;
    public $ctrt_period;
    public $point;
    public $renewal_amount;
    public $renewalend_amount;
    public $renewal_money;
    public $product_amount;

    protected function labelsEx() {
        return array(
            'staffs'=>Yii::t('report','Staffs'),
            'city_name'=>Yii::t('app','city'),
            'first_dt'=>Yii::t('app','first_dt'),
            'sign_dt'=>Yii::t('app','sign_dt'),
            'company_name'=>Yii::t('app','company_name'),
            'type_desc'=>Yii::t('app','type_desc'),
            'service'=>Yii::t('app','service'),
            'amt_paid'=>Yii::t('app','amt_paid'),
            'amt_install'=>Yii::t('app','amt_install'),
            'cust_type'=>Yii::t('app','cust_type'),
            'all_number'=>Yii::t('app','Number'),
            'surplus'=>Yii::t('app','Surplus'),
            'othersalesman'=>Yii::t('app','Othersalesman'),
            'salesman'=>Yii::t('app','Salesman'),
            'ctrt_period'=>Yii::t('app','Ctrt_period'),
        );
    }

    protected function rulesEx() {
        return array(
            array('staffs, staffs_desc','safe'),
        );
    }

    protected function queueItemEx() {
        return array(
            'STAFFS'=>$this->staffs,
            'STAFFSDESC'=>$this->staffs_desc,
        );
    }

    public function init() {
        $this->id = 'RptFive';
        $this->name = Yii::t('app','Five Steps');
        $this->format = 'EXCEL';
        $this->city =Yii::app()->user->city();
        $this->fields = 'start_dt,end_dt,staffs,staffs_desc';
        $this->start_dt = date("Y/m/d");
        $this->end_dt = date("Y/m/d");
        $this->first_dt = date("Y/m/d");
        $this->sign_dt = date("Y/m/d");
        $this->staffs = '';
        $this->month=date("m");
        $this->year=date("Y");
        $this->staffs_desc = Yii::t('misc','All');
    }

//    public function retrieveDatas($model){
//        $start_date = '2017-01-01'; // 自动为00:00:00 时分秒
//        $end_date = date("Y-m-d");
//        $start_arr = explode("-", $start_date);
//        $end_arr = explode("-", $end_date);
//        $start_year = intval($start_arr[0]);
//        $start_month = intval($start_arr[1]);
//        $end_year = intval($end_arr[0]);
//        $end_month = intval($end_arr[1]);
//        $diff_year = $end_year-$start_year;
//        $year_arr=[];
//        for($year=$end_year;$year>=$start_year;$year--){
//            $year_arr[] = $year;
//        }
//        $this->date=$year_arr;
//    }

//    public function city(){
//        $suffix = Yii::app()->params['envSuffix'];
//        $model = new City();
//        $city=Yii::app()->user->city();
//        $records=$model->getDescendant($city);
//        array_unshift($records,$city);
//        $cityname=array();
//        foreach ($records as $k) {
//            $sql = "select name from security$suffix.sec_city where code='" . $k . "'";
//            $name = Yii::app()->db->createCommand($sql)->queryAll();
//            $cityname[]=$name[0]['name'];
//        }
//        $city=array_combine($records,$cityname);
//        return $city;
//    }

    public function retrieveData($index,$a){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.*,b.*,c.name as city_name ,d.group_type from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join security$suffix.sec_city c on  a.city=c.code 
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              where a.id='$index'
";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(!empty($records)){
            $city=Yii::app()->user->city();
            $date=$records['year_no']."/".$records['month_no'].'/'."01";
            $date1='2020/07/01';
            $employee=$this->getEmployee($records['employee_code'],$records['year_no'],$records['month_no']);
            if($records['city']=='CD'||$records['city']=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($records['city']=='FS'||$records['city']=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
                $month=$records['month_no'];
                $year=$records['year_no'];

            }else{
                $month=$records['month_no']-1;
                $year=$records['year_no'];
                if($month==0){
                    $month=12;
                    $year=$records['year_no']-1;
                }
            }
            $sql="select employee_name from acc_service_comm_hdr where id=$index";
            $name = Yii::app()->db->createCommand($sql)->queryScalar();
            $sql1="select a.*, b.new_calc ,e.user_id from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id            
              where  a.year_no='$year' and  a.month_no='$month' and a.employee_name='$name' and d.city='".$records['city']."'
";
            $arr = Yii::app()->db->createCommand($sql1)->queryRow();
//            if($employee==2){
//                $months=$records['month_no']-1;
//                $years=$records['year_no'];
//                if($months==0){
//                    $months=12;
//                    $years=$records['year_no']-1;
//                }
//            }else{
//                $months=$month;
//                $years=$year;
//            }
            $sql_point="select * from sales$suffix.sal_integral where year='$year' and month='$month' and username='".$arr['user_id']."' and city='".$records['city']."'";
            $point = Yii::app()->db->createCommand($sql_point)->queryRow();
            //新增判断当月是否入职月
            if($employee==1){
                $employee_code = $records['employee_code'];
                $sql_r="select e.user_id from  hr$suffix.hr_employee d                  
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where d.code='$employee_code'";
                $records_u = Yii::app()->db->createCommand($sql_r)->queryScalar();
                $sql_c="select visit_dt from sales$suffix.sal_visit   where username='$records_u'  order by visit_dt ";
                $record = Yii::app()->db->createCommand($sql_c)->queryRow();
                $timestrap=strtotime($record['visit_dt']);
                $year_rz=date('Y',$timestrap);
                $month_rz=date('m',$timestrap);
                if($year_rz==$year&&$month_rz==($month-1)){
                    $employee = 2;
                }
            }
            if(empty($point)||$employee==1){
                $point['point']=0;
                $point['id']=0;
            }
            $sql_points="update sales$suffix.sal_integral set hdr_id='$index' where id='".$point['id']."'";
            $record = Yii::app()->db->createCommand($sql_points)->execute();
            $this->city=$records['city_name'];
            $this->employee_name=$records['employee_name'];
            $this->saleyear=$records['year_no']."/".$records['month_no'];
            $new_calc=$arr['new_calc']*100;
            if($new_calc==0){
                $new_calc=5;
            }
            $this->new_calc=$new_calc."%";
            $this->new_amount=$records['new_amount'];
            $this->edit_amount=$records['edit_amount'];
            $this->end_amount=$records['end_amount'];
            $num=$records['new_amount']+$records['edit_amount']+$records['end_amount']+$records['performance_amount']+$records['performanceedit_amount']+$records['performanceend_amount']+$records['renewal_amount']+$records['renewalend_amount']+$records['product_amount'];
            $this->all_amount=number_format($num,2);
            $this->performance_amount=$records['performance_amount'];
            $this->year=$records['year_no'];
            $this->month=$records['month_no']+1;
            $this->new_money=$records['new_money'];
            $this->edit_money=$records['edit_money'];
            $this->out_money=$records['out_money'];
//            if($point['point']<0){
//                $point['point'] = abs($point['point']);
//                $point=$point['point']*100;
//                $this->point='-'.$point."%";
//            }else{
//                $point=$point['point']*100;
//                $this->point=$point."%";
//            }
            $point=$point['point']*100;
            $this->point=$point."%";


            $this->performanceedit_amount=$records['performanceedit_amount'];
            $this->performanceend_amount=$records['performanceend_amount'];
            $this->performanceedit_money=$records['performanceedit_money'];
            $this->renewal_amount=$records['renewal_amount'];
            $this->renewalend_amount=$records['renewalend_amount'];
            $this->renewal_money=$records['renewal_money'];
            $this->product_amount=$records['product_amount'];
            $this->group_type=$this->getGroupType($records['group_type']);
            if($records['performance']==1){
                $a='是';
            }else{
                $a='否';
            }
            $this->performance=$a;
        }
        return true;
    }

    public function saveData($add,$index){
        $city=Yii::app()->user->city();
        $add['amt_paid']=$add['amt_paid']==""?0:$add['amt_paid'];
        $add['amt_install']=$add['amt_install']==""?0:$add['amt_install'];
        $add['all_number']=$add['all_number']==""?0:$add['all_number'];
        $add['surplus']=$add['surplus']==""?0:$add['surplus'];
        $add['ctrt_period']=$add['ctrt_period']==""?12:$add['ctrt_period'];
        $sql = "insert into acc_service_comm_copy(
					hdr_id, first_dt, sign_dt, cust_type, service, paid_type,amt_paid,amt_install,company_name,city,all_number,surplus,othersalesman,ctrt_period,salesman
				) values (
					'".$index."','".$add['first_dt']."','".$add['sign_dt']."','".$add['cust_type']."','".$add['service']."','".$add['paid_type']."','".$add['amt_paid']."','".$add['amt_install']."','".$add['company_name']."','".$city."','".$add['all_number']."','".$add['surplus']."','".$add['othersalesman']."','".$add['ctrt_period']."','".$add['salesman']."'
				)";
        $record = Yii::app()->db->createCommand($sql)->execute();
    }

    public function getGroupType($type){
        if($type==0) {
            $model=Yii::t("misc","none");//無
        }elseif($type==1){
            $model=Yii::t("misc","group business");//商業組
        }elseif($type==2) {
            $model=Yii::t("misc","group repast");//餐飲組
        }
        return $model;
    }

    public  function getEmployee($employee,$year,$month){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select e.user_id from  hr$suffix.hr_employee d                  
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where d.code='$employee'
";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $sql1="select visit_dt from sales$suffix.sal_visit   where username='$records'  order by visit_dt 
";
        $record = Yii::app()->db->createCommand($sql1)->queryRow();

        $timestrap=strtotime($record['visit_dt']);
        $years=date('Y',$timestrap);
        $months=date('m',$timestrap);

        if(date('d',$timestrap)=='01'){
            if($years==$year&&$months==$month){
                $a=1;//不加入东成西就
            }else{
                $a=2;
            }
        }else{
            $next=$months+1;
            if($next==13){
                $next=1;
                $years=$years+1;
            }
            if(($years==$year&&$months==$month)||($years==$year&&$next==$month)){
                $a=1;//不加入东成西就
            }else{
                $a=2;
            }
        }
        return $a;
    }

}
