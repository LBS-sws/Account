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
    public $number;
    public $surplus;
    public $employee_name;
    public $new_amount;
    public $edit_amount;
    public $end_amount;
    public $all_amount;
	
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
            'number'=>Yii::t('app','Number'),
            'surplus'=>Yii::t('app','Surplus'),

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
        $this->date="";
        if( date("m")-1==0){
            $this->month=12;
        }else{
            $this->month=date("m")-1;
        }
        if( $this->month==0){
            $this->year=date("Y")-1;
        }else{
            $this->year=date("Y");
        }
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

    public function retrieveData($index){
	    $sql="select a.*,b.*  from acc_service_comm_hdr a
              inner join  acc_service_comm_dtl b on  b.hdr_id=a.id
              where a.id='$index'
";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(!empty($records)){
            $this->city=$records['city'];
            $this->employee_name=$records['employee_code'].$records['employee_name'];
            $this->year=$records['year_no']."/".$records['month_no'];
            $this->new_amount=$records['new_amount'];
            $this->edit_amount=$records['edit_amount'];
            $this->end_amount=$records['end_amount'];
            $this->all_amount=$records['new_amount']+$records['edit_amount']-$records['end_amount'];

        }

//        print_r('<pre>');
//        print_r($records);
        return true;
    }

    public function saveData($add,$index){
	    $city=Yii::app()->user->city();
        $sql = "insert into acc_service_comm_copy(
					hdr_id, first_dt, sign_dt, cust_type, service, paid_type,amt_paid,amt_install,company_name,city,number,surplus
				) values (
					'".$index."','".$add['first_dt']."','".$add['sign_dt']."','".$add['cust_type']."','".$add['service']."','".$add['paid_type']."','".$add['amt_paid']."','".$add['amt_install']."','".$add['company_name']."','".$city."','".$add['number']."','".$add['surplus']."'
				)";
        $record = Yii::app()->db->createCommand($sql)->execute();
    }

}
